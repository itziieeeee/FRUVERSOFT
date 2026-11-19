<?php
/**
 * app/Views/etiquetas_productos.php
 * Etiquetas imprimibles con el identificador propio de cada producto (PRD-XXXXXX) en Code128.
 * Sirven para costales, cajas y producto a granel que no traen código de barras de fábrica.
 * Recibe: $productos (id, codigo, codigo_barras, nombre)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Etiquetas de productos</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; color: #1e293b; padding: 16px; }
    .barra { max-width: 1100px; margin: 0 auto 16px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; background: #fff; padding: 12px 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    .barra h1 { font-size: 1.1rem; color: #1d4a27; flex: 1 1 220px; }
    .barra label { font-size: .85rem; display: flex; gap: 6px; align-items: center; }
    .barra input[type=number] { width: 64px; padding: 6px; border: 1px solid #cbd5e1; border-radius: 8px; }
    .btn { border: 0; border-radius: 10px; padding: 9px 16px; font-weight: 600; cursor: pointer; background: #1d4a27; color: #fff; text-decoration: none; font-size: .9rem; }
    .btn.sec { background: #e2e8f0; color: #334155; }
    .nota { max-width: 1100px; margin: 0 auto 12px; font-size: .82rem; color: #64748b; }
    .hoja { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fill, 6.4cm); gap: 6px; justify-content: center; }
    .etiqueta { width: 6.4cm; height: 3.4cm; background: #fff; border: 1px dashed #94a3b8; border-radius: 4px; padding: 4px 6px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden; page-break-inside: avoid; break-inside: avoid; position: relative; }
    .etiqueta .marca { font-size: 8px; letter-spacing: 1px; color: #1d4a27; font-weight: 700; }
    .etiqueta .nombre { font-size: 12px; font-weight: 700; line-height: 1.1; max-height: 28px; overflow: hidden; }
    .etiqueta svg { width: 100%; height: 1.8cm; background: #fff; }
    .etiqueta .elegir { position: absolute; top: 3px; left: 4px; }
    .oculta { display: none !important; }
    @media print {
        body { background: #fff; padding: 0; }
        .barra, .nota, .elegir { display: none !important; }
        .hoja { gap: 0; max-width: none; justify-content: start; }
        .etiqueta { border: 1px dotted #999; border-radius: 0; }
        @page { margin: 8mm; }
    }
</style>
</head>
<body>

<div class="barra">
    <h1>Etiquetas de productos</h1>
    <label><input type="checkbox" id="chkTodas" checked> Seleccionar todas</label>
    <label>Copias de cada una <input type="number" id="copias" value="1" min="1" max="20"></label>
    <button class="btn" onclick="imprimir()">Imprimir</button>
    <a class="btn sec" href="<?= base_url('pantalla_productos') ?>">Regresar</a>
</div>
<p class="nota">
    Pega la etiqueta en el costal, la caja o la charola. Al escanearla con el lector o la cámara, el sistema reconoce el producto.
    Los productos empacados que ya traen código de barras no necesitan etiqueta.
</p>

<div class="hoja" id="hoja">
<?php foreach ($productos as $p): ?>
    <div class="etiqueta" data-codigo="<?= esc($p['codigo']) ?>">
        <input type="checkbox" class="elegir" checked aria-label="Imprimir <?= esc($p['nombre']) ?>">
        <div class="marca">FRUVER</div>
        <div class="nombre"><?= esc($p['nombre']) ?></div>
        <svg class="codigo" data-valor="<?= esc($p['codigo']) ?>"></svg>
    </div>
<?php endforeach; ?>
</div>

<script src="<?= base_url('js/vendor/JsBarcode.all.min.js') ?>"></script>
<script>
document.querySelectorAll('svg.codigo').forEach(svg => {
    JsBarcode(svg, svg.dataset.valor, {
        // marginLeft/Right = 10 módulos: el código de barras necesita ese espacio en blanco para poder leerse
        format: 'CODE128', displayValue: true, fontSize: 14, height: 48, width: 2,
        marginTop: 4, marginBottom: 2, marginLeft: 20, marginRight: 20, background: '#ffffff'
    });
});

document.getElementById('chkTodas').addEventListener('change', function () {
    document.querySelectorAll('.etiqueta .elegir').forEach(c => { c.checked = this.checked; });
});

function imprimir() {
    const copias = Math.max(1, Math.min(20, parseInt(document.getElementById('copias').value, 10) || 1));
    const hoja = document.getElementById('hoja');
    const agregadas = [];

    // Las copias extra se agregan solo para imprimir y se quitan después
    document.querySelectorAll('.etiqueta').forEach(et => {
        const marcada = et.querySelector('.elegir').checked;
        et.classList.toggle('oculta', !marcada);
        if (marcada) {
            for (let i = 1; i < copias; i++) {
                const copia = et.cloneNode(true);
                hoja.appendChild(copia);
                agregadas.push(copia);
            }
        }
    });

    window.print();

    agregadas.forEach(c => c.remove());
    document.querySelectorAll('.etiqueta').forEach(et => et.classList.remove('oculta'));
}
</script>
</body>
</html>
