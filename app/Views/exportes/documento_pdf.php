<?php
/**
 * app/Views/exportes/documento_pdf.php
 * Documento PDF genérico a partir de un reporte estructurado (ver App\Libraries\Exportador).
 * Recibe: $reporte, $logo, $generado
 */
use App\Libraries\Exportador;

$formato = static fn ($v, string $f): string => Exportador::formatear($v, $f);
$derecha = static fn (string $f): bool => in_array($f, ['dinero', 'entero', 'numero', 'porcentaje'], true);
$vertical = ($reporte['orientacion'] ?? 'portrait') !== 'landscape';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= esc($reporte['titulo'] ?? 'Reporte') ?></title>
<style>
    @page { margin: 28px 30px 40px 30px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: <?= $vertical ? '10' : '9' ?>px; color: #1e293b; }
    .encabezado { width: 100%; border-bottom: 3px solid #1d4a27; margin-bottom: 14px; }
    .encabezado td { vertical-align: middle; padding-bottom: 8px; }
    .logo { height: 48px; }
    h1 { font-size: 17px; color: #1d4a27; margin: 0 0 2px 0; }
    .sub { font-size: 10px; color: #475569; }
    .gen { font-size: 8px; color: #94a3b8; text-align: right; }

    h2 { font-size: 12px; color: #1d4a27; margin: 16px 0 6px 0; }

    table.datos { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.datos td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
    table.datos td.etq { width: 55%; color: #475569; }
    table.datos td.val { text-align: right; font-weight: bold; }

    table.tabla { width: 100%; border-collapse: collapse; }
    table.tabla th { background: #1d4a27; color: #ffffff; font-size: <?= $vertical ? '8' : '8' ?>px; text-align: left; padding: 6px 6px; }
    table.tabla th.r, table.tabla td.r { text-align: right; }
    table.tabla td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; }
    table.tabla tr.par td { background: #f8fafc; }
    table.tabla tr.total td { font-weight: bold; border-top: 2px solid #94a3b8; background: #eef2f6; }
    .vacio { color: #94a3b8; text-align: center; padding: 14px 0; }
    .nota { font-size: 9px; color: #64748b; margin-top: 10px; }
</style>
</head>
<body>

<table class="encabezado">
    <tr>
        <?php if ($logo): ?><td style="width:64px;"><img class="logo" src="<?= $logo ?>" alt="FRUVER"></td><?php endif; ?>
        <td>
            <h1><?= esc($reporte['titulo'] ?? 'Reporte') ?></h1>
            <?php if (!empty($reporte['subtitulo'])): ?><div class="sub"><?= esc($reporte['subtitulo']) ?></div><?php endif; ?>
        </td>
        <td class="gen">FRUVER<br>Generado: <?= esc($generado) ?></td>
    </tr>
</table>

<?php if (!empty($reporte['resumen'])): ?>
    <h2>Resumen</h2>
    <table class="datos">
        <?php foreach ($reporte['resumen'] as $dato): ?>
            <tr>
                <td class="etq"><?= esc($dato[0]) ?></td>
                <td class="val"><?= esc($formato($dato[1], $dato[2] ?? 'texto')) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php foreach ($reporte['tablas'] ?? [] as $tabla): ?>
    <?php $columnas = $tabla['columnas'] ?? []; ?>
    <?php if (!empty($tabla['titulo'])): ?><h2><?= esc($tabla['titulo']) ?></h2><?php endif; ?>
    <table class="tabla">
        <thead>
            <tr>
                <?php foreach ($columnas as $c): ?>
                    <th class="<?= $derecha($c[1] ?? 'texto') ? 'r' : '' ?>"><?= esc($c[0]) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tabla['filas'])): ?>
                <tr><td class="vacio" colspan="<?= max(1, count($columnas)) ?>"><?= esc($tabla['vacio'] ?? 'Sin datos.') ?></td></tr>
            <?php endif; ?>
            <?php foreach ($tabla['filas'] ?? [] as $i => $fila): ?>
                <tr class="<?= $i % 2 ? 'par' : '' ?>">
                    <?php foreach ($columnas as $k => $c): ?>
                        <td class="<?= $derecha($c[1] ?? 'texto') ? 'r' : '' ?>"><?= esc($formato($fila[$k] ?? null, $c[1] ?? 'texto')) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (!empty($tabla['total'])): ?>
                <tr class="total">
                    <?php foreach ($columnas as $k => $c): ?>
                        <td class="<?= $derecha($c[1] ?? 'texto') ? 'r' : '' ?>"><?= esc($formato($tabla['total'][$k] ?? null, $c[1] ?? 'texto')) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endforeach; ?>

<?php foreach ($reporte['notas'] ?? [] as $nota): ?>
    <div class="nota"><?= esc($nota) ?></div>
<?php endforeach; ?>

</body>
</html>
