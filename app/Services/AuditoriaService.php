<?php
namespace App\Services;

/**
 * HISTORIAL DE CAMBIOS
 * ------------------------------------------------------------
 * Guarda quién hizo cada cambio importante (precios, estados de pedidos, stock, cobros, devoluciones...).
 * Registrar un cambio NUNCA debe romper la operación que lo origina: cualquier error aquí se ignora.
 *
 * Uso:
 *   AuditoriaService::registrar('pedido.estado', 'pedido', 5, 'PED-00005: Pedido -> Pedido cancelado', ['antes' => ..., 'despues' => ...]);
 */
class AuditoriaService
{
    private const ZONA = 'America/Mexico_City';

    /** Nombres legibles de cada acción, para los filtros de la pantalla */
    public const ACCIONES = [
        'pedido.crear'        => 'Pedido creado',
        'pedido.estado'       => 'Cambio de estado de pedido',
        'pedido.precios'      => 'Precios de pedido editados',
        'pedido.pago'         => 'Pago o abono registrado',
        'pedido.eliminar'     => 'Pedido eliminado',
        'pedido.vencimiento'  => 'Fecha límite de crédito',
        'devolucion.crear'    => 'Devolución de cliente',
        'entrega.registrar'   => 'Entrega con evidencia',
        'stock.ajuste'        => 'Ajuste de existencias',
        'stock.eliminar'      => 'Producto eliminado del inventario',
        'stock.caducidad'     => 'Merma por caducidad automática',
        'entrada.crear'       => 'Entrada de inventario',
        'merma.crear'         => 'Merma registrada',
        'producto.crear'      => 'Producto creado',
        'producto.editar'     => 'Producto editado',
        'producto.eliminar'   => 'Producto eliminado',
        'cliente.crear'       => 'Cliente creado',
        'cliente.editar'      => 'Cliente editado',
        'cliente.eliminar'    => 'Cliente eliminado',
        'repartidor.crear'    => 'Repartidor creado',
        'repartidor.editar'   => 'Repartidor editado',
        'repartidor.eliminar' => 'Repartidor eliminado',
        'vendedor.crear'      => 'Vendedor creado',
        'corte.guardar'       => 'Corte de caja guardado',
        'config.cobranza'     => 'Configuración de cobranza',
    ];

    /**
     * @param array|null $detalle datos extra (antes / después); se guardan como JSON
     */
    public static function registrar(string $accion, string $entidad, $idEntidad, string $resumen, ?array $detalle = null): void
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('auditoria')) {
                return;
            }

            $idUsuario = null;
            $usuario   = 'Sistema';
            $rol       = null;
            $ip        = null;

            if (!is_cli()) {
                $sesion    = session();
                $idUsuario = $sesion->get('id_usuario') !== null ? (int) $sesion->get('id_usuario') : null;
                $usuario   = (string) ($sesion->get('nombre') ?: 'Sistema');
                $rol       = $sesion->get('rol');
                $ip        = \Config\Services::request()->getIPAddress();
            }

            $db->table('auditoria')->insert([
                'fecha'      => date('Y-m-d H:i:s'),
                'id_usuario' => $idUsuario,
                'usuario'    => mb_substr($usuario, 0, 120),
                'rol'        => $rol ? mb_substr((string) $rol, 0, 20) : null,
                'accion'     => mb_substr($accion, 0, 60),
                'entidad'    => mb_substr($entidad, 0, 40),
                'id_entidad' => $idEntidad !== null ? mb_substr((string) $idEntidad, 0, 40) : null,
                'resumen'    => mb_substr($resumen, 0, 400),
                'detalle'    => $detalle ? json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'ip'         => $ip,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Auditoría: ' . $e->getMessage());
        }
    }

    /** Folio legible de un pedido */
    public static function folio($idPedido): string
    {
        return 'PED-' . str_pad((string) $idPedido, 5, '0', STR_PAD_LEFT);
    }

    /* ============================ consulta ============================ */

    /**
     * @param array $f filtros: desde, hasta (Y-m-d, hora de México), accion, usuario, q
     * @return array{filas: array, total: int, paginas: int, pagina: int}
     */
    public function listar(array $f, int $pagina = 1, int $porPagina = 40): array
    {
        $db = \Config\Database::connect();
        $tz = new \DateTimeZone(self::ZONA);
        $utc = new \DateTimeZone('UTC');

        $b = $db->table('auditoria');

        if (!empty($f['desde'])) {
            $d = \DateTime::createFromFormat('Y-m-d H:i:s', $f['desde'] . ' 00:00:00', $tz);
            if ($d) { $b->where('fecha >=', $d->setTimezone($utc)->format('Y-m-d H:i:s')); }
        }
        if (!empty($f['hasta'])) {
            $h = \DateTime::createFromFormat('Y-m-d H:i:s', $f['hasta'] . ' 23:59:59', $tz);
            if ($h) { $b->where('fecha <=', $h->setTimezone($utc)->format('Y-m-d H:i:s')); }
        }
        if (!empty($f['accion'])) {
            $b->where('accion', $f['accion']);
        }
        if (!empty($f['usuario'])) {
            $b->where('usuario', $f['usuario']);
        }
        if (!empty($f['q'])) {
            $b->groupStart()->like('resumen', $f['q'])->orLike('id_entidad', $f['q'])->groupEnd();
        }

        $total   = (int) $b->countAllResults(false);
        $paginas = max(1, (int) ceil($total / $porPagina));
        $pagina  = max(1, min($pagina, $paginas));

        $filas = $b->orderBy('id', 'DESC')->limit($porPagina, ($pagina - 1) * $porPagina)->get()->getResultArray();
        foreach ($filas as &$fila) {
            $local = (new \DateTime($fila['fecha'], $utc))->setTimezone($tz);
            $fila['fecha_local'] = $local->format('d/m/Y H:i');
            $fila['accion_texto'] = self::ACCIONES[$fila['accion']] ?? $fila['accion'];
            $fila['detalle_arr'] = $fila['detalle'] ? json_decode($fila['detalle'], true) : null;
        }
        unset($fila);

        return ['filas' => $filas, 'total' => $total, 'paginas' => $paginas, 'pagina' => $pagina];
    }

    /** Usuarios que han hecho cambios (para el filtro) */
    public function usuarios(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('auditoria')) {
            return [];
        }
        return array_column(
            $db->query('SELECT DISTINCT usuario FROM auditoria WHERE usuario IS NOT NULL ORDER BY usuario')->getResultArray(),
            'usuario'
        );
    }
}
