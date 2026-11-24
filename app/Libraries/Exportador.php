<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

/**
 * Convierte un reporte estructurado en PDF o en Excel (.xlsx).
 *
 * Estructura del reporte:
 * [
 *   'titulo'      => 'Corte de caja',
 *   'subtitulo'   => '24 de noviembre de 2026',
 *   'archivo'     => 'Corte-de-caja-2026-11-24',      // nombre del archivo sin extensión
 *   'orientacion' => 'portrait' | 'landscape',         // solo para el PDF
 *   'resumen'     => [ ['Ventas', 1234.5, 'dinero'], ... ],            // datos clave: [etiqueta, valor, formato]
 *   'tablas'      => [[
 *       'titulo'   => 'Movimientos',
 *       'hoja'     => 'Resumen',                       // opcional: hoja de Excel (si se repite, las tablas se apilan)
 *       'columnas' => [ ['Hora', 'texto'], ['Monto', 'dinero'] ],      // [encabezado, formato]
 *       'filas'    => [ ['10:30', 120.0], ... ],
 *       'total'    => ['Total', 120.0],                // opcional, mismas columnas que las filas
 *       'vacio'    => 'No hubo movimientos',           // opcional, texto si no hay filas
 *   ]],
 *   'notas'       => ['Texto libre al final', ...],
 * ]
 * Formatos de columna: texto, dinero, entero, numero, porcentaje.
 */
class Exportador
{
    public const FORMATOS = ['texto', 'dinero', 'entero', 'numero', 'porcentaje'];

    /* ============================ respuestas HTTP ============================ */

    public static function pdf(ResponseInterface $respuesta, array $reporte): ResponseInterface
    {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            throw new RuntimeException('Falta instalar Dompdf: abre la terminal en el proyecto y corre "composer install".');
        }

        $opciones = new \Dompdf\Options();
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('isRemoteEnabled', false);

        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->loadHtml(self::html($reporte), 'UTF-8');
        $dompdf->setPaper('A4', ($reporte['orientacion'] ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait');
        $dompdf->render();

        return $respuesta
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . self::nombreArchivo($reporte) . '.pdf"')
            ->setBody($dompdf->output());
    }

    public static function xlsx(ResponseInterface $respuesta, array $reporte): ResponseInterface
    {
        return $respuesta
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . self::nombreArchivo($reporte) . '.xlsx"')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(self::libro($reporte)->contenido());
    }

    /* ============================ PDF ============================ */

    public static function html(array $reporte): string
    {
        return view('exportes/documento_pdf', [
            'reporte'  => $reporte,
            'logo'     => self::logo(),
            'generado' => (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('d/m/Y h:i A'),
        ]);
    }

    /** Texto con el formato de la columna, para el PDF */
    public static function formatear($valor, string $formato): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }
        if (!is_numeric($valor) || $formato === 'texto') {
            return (string) $valor;
        }

        $n = (float) $valor;
        switch ($formato) {
            case 'dinero':     return ($n < 0 ? '-' : '') . '$' . number_format(abs($n), 2);
            case 'entero':     return number_format($n, 0);
            case 'numero':     return number_format($n, 2);
            case 'porcentaje': return number_format($n, 1) . '%';
        }
        return (string) $valor;
    }

    /* ============================ Excel ============================ */

    public static function libro(array $reporte): XlsxWriter
    {
        $libro = new XlsxWriter();

        // Se agrupan las tablas por hoja; las que no piden hoja usan su título
        $hojas = [];
        foreach ($reporte['tablas'] ?? [] as $tabla) {
            $nombre = $tabla['hoja'] ?? ($tabla['titulo'] ?? 'Datos');
            $hojas[$nombre][] = $tabla;
        }

        // Hoja 1: título, datos clave y las tablas que comparten la hoja "Resumen"
        $filas = [
            [[$reporte['titulo'] ?? 'Reporte', 'titulo']],
            [[($reporte['subtitulo'] ?? ''), 'subtitulo']],
            [['Generado: ' . (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('d/m/Y h:i A'), 'subtitulo']],
            [],
        ];
        foreach ($reporte['resumen'] ?? [] as $dato) {
            $filas[] = [[$dato[0], 'negrita'], [$dato[1], self::estiloCelda($dato[2] ?? 'texto')]];
        }
        $anchos = [self::anchoTexto(array_column($reporte['resumen'] ?? [], 0), 34, 22), 20];

        if (isset($hojas['Resumen'])) {
            foreach ($hojas['Resumen'] as $tabla) {
                $filas[] = [];
                [$bloque, $anchosTabla] = self::filasTabla($tabla, true);
                array_push($filas, ...$bloque);
                foreach ($anchosTabla as $i => $a) { $anchos[$i] = max($anchos[$i] ?? 0, $a); }
            }
            unset($hojas['Resumen']);
        }
        foreach ($reporte['notas'] ?? [] as $nota) {
            $filas[] = [];
            $filas[] = [[$nota, 'subtitulo']];
        }
        $libro->agregarHoja('Resumen', $filas, $anchos);

        // Resto de hojas: cada una empieza con el encabezado para poder inmovilizarlo
        foreach ($hojas as $nombre => $tablas) {
            $filas = [];
            $anchos = [];
            $congelar = 0;
            foreach ($tablas as $i => $tabla) {
                if ($i > 0) { $filas[] = []; }
                [$bloque, $anchosTabla] = self::filasTabla($tabla, $i > 0);
                array_push($filas, ...$bloque);
                foreach ($anchosTabla as $k => $a) { $anchos[$k] = max($anchos[$k] ?? 0, $a); }
                if ($i === 0) { $congelar = 1; }
            }
            $libro->agregarHoja($nombre, $filas, $anchos, $congelar);
        }

        return $libro;
    }

    /**
     * Filas de una tabla para Excel y el ancho sugerido de cada columna.
     * @return array{0: array, 1: array}
     */
    private static function filasTabla(array $tabla, bool $conTitulo): array
    {
        $columnas = $tabla['columnas'] ?? [];
        $filas = [];
        if ($conTitulo && !empty($tabla['titulo'])) {
            $filas[] = [[$tabla['titulo'], 'negrita']];
        }

        $filas[] = array_map(static fn (array $c): array => [$c[0], 'encabezado'], $columnas);

        $anchos = array_map(static fn (array $c): int => mb_strlen((string) $c[0]) + 4, $columnas);

        foreach ($tabla['filas'] ?? [] as $fila) {
            $celdas = [];
            foreach ($columnas as $i => $c) {
                $v = $fila[$i] ?? null;
                $celdas[] = [self::valorCelda($v, $c[1] ?? 'texto'), self::estiloCelda($c[1] ?? 'texto')];
                $anchos[$i] = max($anchos[$i], min(45, mb_strlen(self::formatear($v, $c[1] ?? 'texto')) + 2));
            }
            $filas[] = $celdas;
        }

        if (empty($tabla['filas']) && !empty($tabla['vacio'])) {
            $filas[] = [[$tabla['vacio'], 'subtitulo']];
        }

        if (!empty($tabla['total'])) {
            $celdas = [];
            foreach ($columnas as $i => $c) {
                $v = $tabla['total'][$i] ?? null;
                $f = $c[1] ?? 'texto';
                $celdas[] = [self::valorCelda($v, $f), $v === null ? 'texto_total' : self::estiloTotal($f)];
            }
            $filas[] = $celdas;
        }

        return [$filas, array_map(static fn (int $a): int => max(10, $a), $anchos)];
    }

    /** Los números viajan como número (para poder sumarlos en Excel); lo demás, como texto */
    private static function valorCelda($valor, string $formato)
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if ($formato !== 'texto' && is_numeric($valor)) {
            return $formato === 'entero' ? (int) round((float) $valor) : (float) $valor;
        }
        return (string) $valor;
    }

    private static function estiloCelda(string $formato): string
    {
        return in_array($formato, ['dinero', 'entero', 'numero', 'porcentaje'], true) ? $formato : 'texto';
    }

    private static function estiloTotal(string $formato): string
    {
        return ['dinero' => 'dinero_total', 'entero' => 'entero_total', 'numero' => 'numero_total', 'porcentaje' => 'numero_total'][$formato] ?? 'texto_total';
    }

    private static function anchoTexto(array $textos, int $maximo, int $minimo): int
    {
        $ancho = $minimo;
        foreach ($textos as $t) { $ancho = max($ancho, mb_strlen((string) $t) + 2); }
        return min($maximo, $ancho);
    }

    /* ============================ utilidades ============================ */

    /** Nombre de archivo seguro: sin acentos, espacios ni símbolos */
    public static function nombreArchivo(array $reporte): string
    {
        $nombre = (string) ($reporte['archivo'] ?? 'reporte');
        $nombre = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre) ?: $nombre;
        $nombre = trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $nombre) ?? '', '-');
        return $nombre !== '' ? $nombre : 'reporte';
    }

    public static function logo(): string
    {
        foreach (['img/LOGO1.png', 'IMG/LOGO1.png'] as $ruta) {
            if (is_file(FCPATH . $ruta)) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . $ruta));
            }
        }
        return '';
    }

    /** "24 de noviembre de 2026" a partir de Y-m-d */
    public static function fechaLarga(string $fecha): string
    {
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $t = strtotime($fecha);
        return $t ? (int) date('j', $t) . ' de ' . $meses[(int) date('n', $t)] . ' de ' . date('Y', $t) : $fecha;
    }
}
