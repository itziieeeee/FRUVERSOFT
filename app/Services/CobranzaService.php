<?php
namespace App\Services;

/**
 * COBRANZA DE CRÉDITOS
 * ------------------------------------------------------------
 *  Crédito por cobrar : pedido con tipo_pago = 'credito', no cancelado y con saldo (total - monto_pagado) mayor a 0.
 *  Vencimiento        : pedido.fecha_vencimiento; si está vacía, fecha del pedido + plazo general (alertas_config).
 *  Estado             : vencido (ya pasó), hoy, por vencer (faltan <= días de aviso) o al corriente.
 *
 * El recordatorio es un enlace wa.me con el mensaje ya escrito: el envío lo hace el administrador con un clic.
 */
class CobranzaService
{
    private const ZONA           = 'America/Mexico_City';
    public  const PLAZO_DEFECTO  = 15;
    public  const AVISO_DEFECTO  = 3;

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /* ============================ configuración ============================ */

    public function config(): array
    {
        $plazo = self::PLAZO_DEFECTO;
        $aviso = self::AVISO_DEFECTO;

        if ($this->db->tableExists('alertas_config')) {
            $filas = $this->db->table('alertas_config')
                ->whereIn('clave', ['credito_plazo_dias', 'credito_aviso_dias'])
                ->get()->getResultArray();
            foreach ($filas as $f) {
                if ($f['clave'] === 'credito_plazo_dias' && (int) $f['valor'] > 0) { $plazo = (int) $f['valor']; }
                if ($f['clave'] === 'credito_aviso_dias' && (int) $f['valor'] >= 0) { $aviso = (int) $f['valor']; }
            }
        }
        return ['plazo' => $plazo, 'aviso' => $aviso];
    }

    public function guardarConfig(int $plazo, int $aviso): bool
    {
        if ($plazo < 1 || $plazo > 365 || $aviso < 0 || $aviso > 60) {
            return false;
        }
        $antes = $this->config();
        if ($antes['plazo'] !== $plazo || $antes['aviso'] !== $aviso) {
            AuditoriaService::registrar(
                'config.cobranza', 'config', 'cobranza',
                'Crédito: plazo ' . $antes['plazo'] . ' → ' . $plazo . ' días, aviso ' . $antes['aviso'] . ' → ' . $aviso . ' días antes',
                ['antes' => $antes, 'despues' => ['plazo' => $plazo, 'aviso' => $aviso]]
            );
        }
        foreach (['credito_plazo_dias' => $plazo, 'credito_aviso_dias' => $aviso] as $clave => $valor) {
            $this->db->query(
                'INSERT INTO alertas_config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)',
                [$clave, (string) $valor]
            );
        }
        return true;
    }

    /* ============================ lectura ============================ */

    private function hoy(): \DateTime
    {
        return (new \DateTime('now', new \DateTimeZone(self::ZONA)))->setTime(0, 0, 0);
    }

    /** Las fechas se guardan con la hora del servidor (UTC): se pasan a hora de México para decidir el día */
    private function aLocal(string $fecha): \DateTime
    {
        return (new \DateTime($fecha, new \DateTimeZone(date_default_timezone_get())))
            ->setTimezone(new \DateTimeZone(self::ZONA));
    }

    private function dinero(float $n): string
    {
        return '$' . number_format($n, 2);
    }

    private function textoHace(?string $fecha): ?string
    {
        if (!$fecha) { return null; }
        $segundos = time() - strtotime($fecha . ' ' . date_default_timezone_get());
        if ($segundos < 3600)   { return 'hace menos de 1 hora'; }
        if ($segundos < 86400)  { return 'hace ' . (int) floor($segundos / 3600) . ' h'; }
        $dias = (int) floor($segundos / 86400);
        return 'hace ' . $dias . ($dias === 1 ? ' día' : ' días');
    }

    /** Créditos por cobrar agrupados por cliente, los más urgentes primero */
    public function listar(): array
    {
        helper('whatsapp');

        $cfg  = $this->config();
        $hoy  = $this->hoy();

        $filas = $this->db->query(
            "SELECT p.id, p.fecha, p.total, p.monto_pagado, p.estado_actual, p.fecha_vencimiento, p.recordatorio_credito_at,
                    c.id_cliente, c.nombre, c.apellido_paterno, c.tel
               FROM pedido p
               JOIN clientes c ON c.id_cliente = p.id_cliente
              WHERE p.tipo_pago = 'credito'
                AND p.estado_actual <> 'Pedido cancelado'
                AND (p.total - p.monto_pagado) > 0.009
              ORDER BY p.id"
        )->getResultArray();

        $clientes = [];
        foreach ($filas as $f) {
            $saldo = round((float) $f['total'] - (float) $f['monto_pagado'], 2);

            $vence = !empty($f['fecha_vencimiento'])
                ? new \DateTime($f['fecha_vencimiento'], new \DateTimeZone(self::ZONA))
                : $this->aLocal($f['fecha'])->setTime(0, 0, 0)->modify('+' . $cfg['plazo'] . ' days');
            $dias = (int) $hoy->diff($vence)->format('%r%a');   // negativo = ya venció

            if ($dias < 0)                   { $estado = 'vencido'; }
            elseif ($dias === 0)             { $estado = 'hoy'; }
            elseif ($dias <= $cfg['aviso'])  { $estado = 'por_vencer'; }
            else                             { $estado = 'al_corriente'; }

            $id = (int) $f['id_cliente'];
            $clientes[$id] ??= [
                'id_cliente' => $id,
                'cliente'    => trim($f['nombre'] . ' ' . ($f['apellido_paterno'] ?? '')),
                'nombre'     => trim((string) $f['nombre']),
                'tel'        => $f['tel'],
                'pedidos'    => [],
                'saldo'      => 0.0,
                'dias_min'   => PHP_INT_MAX,
            ];

            $clientes[$id]['pedidos'][] = [
                'id'               => (int) $f['id'],
                'folio'            => 'PED-' . str_pad((string) $f['id'], 5, '0', STR_PAD_LEFT),
                'fecha'            => $this->aLocal($f['fecha'])->format('d/m/Y'),
                'total'            => (float) $f['total'],
                'pagado'           => (float) $f['monto_pagado'],
                'saldo'            => $saldo,
                'vence'            => $vence->format('Y-m-d'),
                'vence_texto'      => $vence->format('d/m/Y'),
                'personalizado'    => !empty($f['fecha_vencimiento']),
                'dias'             => $dias,
                'estado'           => $estado,
                'recordado_texto'  => $this->textoHace($f['recordatorio_credito_at']),
            ];
            $clientes[$id]['saldo']    += $saldo;
            $clientes[$id]['dias_min']  = min($clientes[$id]['dias_min'], $dias);
        }

        // Cierre por cliente: estado más urgente, mensaje de WhatsApp y recordatorio más reciente
        $resumen = ['por_cobrar' => 0.0, 'vencido' => 0.0, 'clientes' => 0, 'vencidos' => 0, 'por_vencer' => 0];
        foreach ($clientes as &$c) {
            $c['saldo'] = round($c['saldo'], 2);
            $c['estado'] = $c['dias_min'] < 0 ? 'vencido'
                         : ($c['dias_min'] === 0 ? 'hoy'
                         : ($c['dias_min'] <= $cfg['aviso'] ? 'por_vencer' : 'al_corriente'));

            $c['ids']          = array_column($c['pedidos'], 'id');
            $c['whatsapp_url'] = $this->enlaceWhatsApp($c);

            $resumen['por_cobrar'] += $c['saldo'];
            $resumen['clientes']++;
            if ($c['estado'] === 'vencido') { $resumen['vencidos']++; }
            if ($c['estado'] === 'hoy' || $c['estado'] === 'por_vencer') { $resumen['por_vencer']++; }
            foreach ($c['pedidos'] as $p) {
                if ($p['estado'] === 'vencido') { $resumen['vencido'] += $p['saldo']; }
            }
        }
        unset($c);

        usort($clientes, static fn ($a, $b) => [$a['dias_min'], -$a['saldo']] <=> [$b['dias_min'], -$b['saldo']]);

        $resumen['por_cobrar'] = round($resumen['por_cobrar'], 2);
        $resumen['vencido']    = round($resumen['vencido'], 2);

        return ['clientes' => array_values($clientes), 'resumen' => $resumen, 'config' => $cfg];
    }

    /* ============================ mensaje de WhatsApp ============================ */

    private function enlaceWhatsApp(array $c): ?string
    {
        $telefono = formato_telefono_wa($c['tel'] ?? '');
        if (strlen($telefono) < 10) {
            return null;
        }
        return 'https://wa.me/' . $telefono . '?text=' . rawurlencode($this->mensaje($c));
    }

    private function mensaje(array $c): string
    {
        $saludo = $c['nombre'] !== '' ? 'Hola ' . $c['nombre'] . ',' : 'Hola,';
        $lineas = [];
        foreach ($c['pedidos'] as $p) {
            $cuando = $p['dias'] < 0 ? 'venció el ' . $p['vence_texto']
                    : ($p['dias'] === 0 ? 'vence hoy' : 'vence el ' . $p['vence_texto']);
            $lineas[] = '- ' . $p['folio'] . ': ' . $this->dinero($p['saldo']) . ' (' . $cuando . ')';
        }

        $hayVencidos = $c['dias_min'] < 0;

        return $saludo . ' te escribimos de FRUVER.' . "\n\n"
            . ($hayVencidos ? 'Te recordamos que tienes un saldo vencido.' : 'Te recordamos que tienes un saldo por pagar.')
            . ' En total debes *' . $this->dinero($c['saldo']) . '*:' . "\n"
            . implode("\n", $lineas) . "\n\n"
            . 'Puedes pasar a liquidarlo o responder este mensaje para ponernos de acuerdo. Gracias por tu preferencia.';
    }

    /* ============================ acciones ============================ */

    /** Cambia la fecha límite de un pedido a crédito (null = vuelve a usar el plazo general) */
    public function guardarVencimiento(int $idPedido, ?string $fecha): bool
    {
        if ($fecha !== null) {
            $d = \DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$d || $d->format('Y-m-d') !== $fecha) {
                return false;
            }
        }

        $antes = $this->db->table('pedido')->select('fecha_vencimiento')->where('id', $idPedido)->get()->getRowArray();

        $this->db->table('pedido')
            ->where('id', $idPedido)
            ->where('tipo_pago', 'credito')
            ->update(['fecha_vencimiento' => $fecha]);

        AuditoriaService::registrar(
            'pedido.vencimiento', 'pedido', $idPedido,
            AuditoriaService::folio($idPedido) . ': fecha límite ' . ($antes['fecha_vencimiento'] ?: 'por plazo general') . ' → ' . ($fecha ?: 'por plazo general'),
            ['antes' => $antes['fecha_vencimiento'] ?? null, 'despues' => $fecha]
        );

        return $this->db->affectedRows() >= 0;
    }

    /** Registra que se abrió el recordatorio de estos pedidos */
    public function marcarRecordado(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $i): bool => $i > 0));
        if (!$ids) {
            return 0;
        }

        $this->db->table('pedido')
            ->whereIn('id', $ids)
            ->where('tipo_pago', 'credito')
            ->update(['recordatorio_credito_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows();
    }
}
