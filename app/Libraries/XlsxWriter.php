<?php

namespace App\Libraries;

use RuntimeException;
use ZipArchive;

/**
 * Generador mínimo de archivos .xlsx (Excel) sin librerías externas.
 * Un .xlsx es un zip con archivos XML; solo hace falta la extensión zip de PHP.
 *
 * Cada hoja recibe filas. Cada fila es una lista de celdas y cada celda es:
 *   - un valor suelto (texto, entero o decimal), o
 *   - [valor, estilo] con alguno de los estilos de ESTILOS.
 * Una fila vacía ([]) deja un renglón en blanco.
 */
class XlsxWriter
{
    /** nombre del estilo => posición dentro de cellXfs (ver styles()) */
    public const ESTILOS = [
        'texto'        => 0,
        'titulo'       => 1,
        'encabezado'   => 2,
        'dinero'       => 3,
        'entero'       => 4,
        'numero'       => 5,
        'negrita'      => 6,
        'dinero_total' => 7,
        'porcentaje'   => 8,
        'subtitulo'    => 9,
        'texto_total'  => 10,
        'entero_total' => 11,
        'numero_total' => 12,
    ];

    /** @var array<int, array{nombre: string, filas: array, anchos: array, congelar: int}> */
    private array $hojas = [];

    /**
     * @param array $filas    filas de la hoja (ver arriba)
     * @param array $anchos   ancho de cada columna en caracteres (1.ª columna = índice 0)
     * @param int   $congelar cuántas filas de arriba se quedan fijas al desplazarse (0 = ninguna)
     */
    public function agregarHoja(string $nombre, array $filas, array $anchos = [], int $congelar = 0): self
    {
        $this->hojas[] = [
            'nombre'   => $this->nombreHoja($nombre),
            'filas'    => $filas,
            'anchos'   => $anchos,
            'congelar' => max(0, $congelar),
        ];
        return $this;
    }

    /** Devuelve el contenido binario del archivo .xlsx */
    public function contenido(): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Falta la extensión zip de PHP para generar el archivo de Excel.');
        }
        if (!$this->hojas) {
            throw new RuntimeException('El libro de Excel no tiene hojas.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'fvx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo preparar el archivo de Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->relsRaiz());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->relsLibro());
        $zip->addFromString('xl/styles.xml', $this->styles());
        foreach ($this->hojas as $i => $hoja) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->hojaXml($hoja));
        }
        $zip->close();

        $datos = file_get_contents($tmp);
        @unlink($tmp);

        if ($datos === false || $datos === '') {
            throw new RuntimeException('No se pudo leer el archivo de Excel generado.');
        }
        return $datos;
    }

    /* ============================ piezas del archivo ============================ */

    private const XML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";

    private function contentTypes(): string
    {
        $hojas = '';
        foreach ($this->hojas as $i => $_) {
            $hojas .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return self::XML
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . $hojas
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function relsRaiz(): string
    {
        return self::XML
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(): string
    {
        $hojas = '';
        foreach ($this->hojas as $i => $hoja) {
            $hojas .= '<sheet name="' . $this->xml($hoja['nombre']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return self::XML
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $hojas . '</sheets></workbook>';
    }

    private function relsLibro(): string
    {
        $rels = '';
        foreach ($this->hojas as $i => $_) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . (count($this->hojas) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return self::XML
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    private function styles(): string
    {
        $formatos = '<numFmts count="2">'
            . '<numFmt numFmtId="164" formatCode="&quot;$&quot;#,##0.00;[Red]\-&quot;$&quot;#,##0.00"/>'
            . '<numFmt numFmtId="165" formatCode="0.0&quot;%&quot;"/>'
            . '</numFmts>';

        $fuentes = '<fonts count="5">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'                                                       // 0 normal
            . '<font><b/><sz val="16"/><color rgb="FF1D4A27"/><name val="Calibri"/></font>'                            // 1 título
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'                            // 2 encabezado
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'                                                   // 3 negrita
            . '<font><i/><sz val="10"/><color rgb="FF64748B"/><name val="Calibri"/></font>'                            // 4 subtítulo
            . '</fonts>';

        $rellenos = '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1D4A27"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>';

        $bordes = '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left/><right/><top style="thin"><color rgb="FF94A3B8"/></top><bottom/><diagonal/></border>'
            . '</borders>';

        $xf = static fn (int $fmt, int $fuente, int $borde, string $extra = ''): string =>
            '<xf numFmtId="' . $fmt . '" fontId="' . $fuente . '" fillId="0" borderId="' . $borde . '" xfId="0"'
            . ($fmt ? ' applyNumberFormat="1"' : '') . ($fuente ? ' applyFont="1"' : '') . ($borde ? ' applyBorder="1"' : '') . $extra . '/>';

        $estilos = '<cellXfs count="13">'
            . $xf(0, 0, 0)                                                                                              // 0 texto
            . $xf(0, 1, 0)                                                                                              // 1 titulo
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
              . '<alignment horizontal="center" vertical="center" wrapText="1"/></xf>'                                  // 2 encabezado
            . $xf(164, 0, 0)                                                                                            // 3 dinero
            . $xf(3, 0, 0)                                                                                              // 4 entero
            . $xf(4, 0, 0)                                                                                              // 5 numero
            . $xf(0, 3, 0)                                                                                              // 6 negrita
            . $xf(164, 3, 1)                                                                                            // 7 dinero_total
            . $xf(165, 0, 0)                                                                                            // 8 porcentaje
            . $xf(0, 4, 0)                                                                                              // 9 subtitulo
            . $xf(0, 3, 1)                                                                                              // 10 texto_total
            . $xf(3, 3, 1)                                                                                              // 11 entero_total
            . $xf(4, 3, 1)                                                                                              // 12 numero_total
            . '</cellXfs>';

        return self::XML
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $formatos . $fuentes . $rellenos . $bordes
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . $estilos
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function hojaXml(array $hoja): string
    {
        $vista = '<sheetView workbookViewId="0" showGridLines="0">';
        if ($hoja['congelar'] > 0) {
            $vista .= '<pane ySplit="' . $hoja['congelar'] . '" topLeftCell="A' . ($hoja['congelar'] + 1) . '" activePane="bottomLeft" state="frozen"/>'
                    . '<selection pane="bottomLeft" activeCell="A' . ($hoja['congelar'] + 1) . '" sqref="A' . ($hoja['congelar'] + 1) . '"/>';
        }
        $vista .= '</sheetView>';

        $columnas = '';
        foreach ($hoja['anchos'] as $i => $ancho) {
            $columnas .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . round((float) $ancho, 1) . '" customWidth="1"/>';
        }

        $filas = '';
        foreach ($hoja['filas'] as $r => $celdas) {
            $numFila = $r + 1;
            $xml = '';
            foreach (array_values((array) $celdas) as $c => $celda) {
                $xml .= $this->celda($c, $numFila, $celda);
            }
            // Una fila vacía igual debe existir para conservar la numeración
            $filas .= '<row r="' . $numFila . '">' . $xml . '</row>';
        }

        return self::XML
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews>' . $vista . '</sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . ($columnas !== '' ? '<cols>' . $columnas . '</cols>' : '')
            . '<sheetData>' . $filas . '</sheetData>'
            . '</worksheet>';
    }

    /** @param mixed $celda valor suelto o [valor, estilo] */
    private function celda(int $columna, int $fila, $celda): string
    {
        $estilo = 0;
        $valor  = $celda;
        if (is_array($celda)) {
            $valor  = $celda[0] ?? null;
            $estilo = self::ESTILOS[$celda[1] ?? 'texto'] ?? 0;
        }

        if ($valor === null || $valor === '') {
            return '';
        }

        $ref = $this->letra($columna) . $fila;

        if (is_int($valor) || is_float($valor)) {
            if (is_float($valor) && (is_nan($valor) || is_infinite($valor))) {
                return '';
            }
            return '<c r="' . $ref . '"' . ($estilo ? ' s="' . $estilo . '"' : '') . '><v>' . $valor . '</v></c>';
        }

        return '<c r="' . $ref . '" t="inlineStr"' . ($estilo ? ' s="' . $estilo . '"' : '') . '>'
             . '<is><t xml:space="preserve">' . $this->xml((string) $valor) . '</t></is></c>';
    }

    /** 0 => A, 25 => Z, 26 => AA */
    private function letra(int $i): string
    {
        $s = '';
        for ($n = $i + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $s = chr(65 + (($n - 1) % 26)) . $s;
        }
        return $s;
    }

    /** Escapa el texto y quita los caracteres de control que Excel no acepta */
    private function xml(string $texto): string
    {
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texto) ?? '';
        return htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** Excel limita el nombre de la hoja a 31 caracteres y prohíbe  : \ / ? * [ ] */
    private function nombreHoja(string $nombre): string
    {
        $nombre = trim(preg_replace('/[:\\\\\/?*\[\]]/u', ' ', $nombre) ?? '');
        $nombre = mb_substr($nombre !== '' ? $nombre : 'Hoja', 0, 31);

        // No puede haber dos hojas con el mismo nombre
        $base = $nombre;
        $n = 2;
        while (in_array($nombre, array_column($this->hojas, 'nombre'), true)) {
            $sufijo = ' (' . $n++ . ')';
            $nombre = mb_substr($base, 0, 31 - mb_strlen($sufijo)) . $sufijo;
        }
        return $nombre;
    }
}
