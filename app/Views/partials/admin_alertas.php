<?php
// Campanita de inventario para el banner administrativo.
// El resumen se consulta por AJAX y no bloquea la carga del menú.
?>
<style>
    .logo-area { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .alertas-admin-widget { position: relative; display: inline-flex; align-items: center; }
    .alertas-admin-btn {
        position: relative; display: inline-flex; align-items: center; gap: 8px;
        border: 1px solid rgba(255,255,255,.28); background: rgba(255,255,255,.12);
        color: #fff; border-radius: 60px; padding: 9px 15px; cursor: pointer;
        font: inherit; font-size: .85rem; font-weight: 600; transition: .2s;
    }
    .alertas-admin-btn:hover, .alertas-admin-btn[aria-expanded="true"] { background: #f16b1a; transform: translateY(-2px); }
    .alertas-admin-btn i { font-size: 1rem; }
    .alertas-admin-badge {
        display: none; min-width: 20px; height: 20px; padding: 1px 6px; border-radius: 50px;
        color: #fff; font-size: .7rem; line-height: 18px; text-align: center; font-weight: 800;
        background: #dc2626; box-shadow: 0 0 0 2px rgba(255,255,255,.25);
    }
    .alertas-admin-badge.atencion { background: #f59e0b; }
    .alertas-admin-popover {
        position: absolute; z-index: 2000; top: calc(100% + 12px); right: 0; width: min(360px, 90vw);
        background: #fff; color: #1e293b; border-radius: 16px; padding: 14px;
        box-shadow: 0 14px 35px rgba(0,0,0,.22); border: 1px solid #e2e8f0;
    }
    .alertas-admin-popover::before {
        content: ''; position: absolute; top: -7px; right: 28px; width: 13px; height: 13px;
        background: #fff; border-left: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; transform: rotate(45deg);
    }
    .alertas-admin-popover[hidden] { display: none; }
    .alertas-admin-titulo { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 5px; }
    .alertas-admin-titulo strong { color: #1e5631; font-size: .96rem; }
    .alertas-admin-cerrar { border: 0; background: transparent; color: #64748b; cursor: pointer; font-size: 1rem; }
    .alertas-admin-resumen { color: #64748b; font-size: .78rem; margin-bottom: 10px; }
    .alertas-admin-item {
        display: block; border-left: 4px solid #dc2626; background: #fff7f7; border-radius: 8px;
        padding: 8px 9px; margin: 6px 0; font-size: .8rem;
    }
    .alertas-admin-item strong { display: block; color: #334155; margin-bottom: 2px; }
    .alertas-admin-item small { color: #64748b; }
    .alertas-admin-vacio { padding: 12px 8px; border-radius: 8px; background: #f0fdf4; color: #166534; font-size: .82rem; }
    .alertas-admin-pie { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
    .alertas-admin-pie a { color: #1e5631; font-size: .8rem; font-weight: 700; text-decoration: none; }
    .alertas-admin-pie a:hover { text-decoration: underline; }
    .alertas-admin-actualizar { border: 0; background: #eef6ef; color: #1e5631; border-radius: 7px; padding: 5px 8px; cursor: pointer; font-size: .75rem; }
    @media (max-width: 580px) {
        .alertas-admin-popover { position: fixed; top: 90px; right: 5vw; width: 90vw; }
        .alertas-admin-popover::before { display: none; }
    }
</style>

<div class="alertas-admin-widget" id="alertasAdminWidget">
    <button type="button" class="alertas-admin-btn" id="btnAlertasAdmin" aria-expanded="false" aria-controls="popoverAlertasAdmin" title="Revisar estado del inventario">
        <i class="fas fa-bell"></i>
        <span>Stock</span>
        <span class="alertas-admin-badge" id="badgeAlertasAdmin" aria-label="Productos que requieren atención"></span>
    </button>

    <div class="alertas-admin-popover" id="popoverAlertasAdmin" role="dialog" aria-labelledby="tituloAlertasAdmin" hidden>
        <div class="alertas-admin-titulo">
            <strong id="tituloAlertasAdmin"><i class="fas fa-boxes-stacked"></i> Estado del inventario</strong>
            <button type="button" class="alertas-admin-cerrar" id="cerrarAlertasAdmin" aria-label="Cerrar alertas">&times;</button>
        </div>
        <div class="alertas-admin-resumen" id="resumenAlertasAdmin">Consultando productos...</div>
        <div id="listaAlertasAdmin"></div>
        <div class="alertas-admin-pie">
            <a href="<?= base_url('alertas') ?>">Ver análisis completo</a>
            <button type="button" class="alertas-admin-actualizar" id="actualizarAlertasAdmin"><i class="fas fa-sync-alt"></i> Actualizar</button>
        </div>
    </div>
</div>

<script>
(function () {
    const widget = document.getElementById('alertasAdminWidget');
    if (!widget || widget.dataset.iniciado === '1') return;
    widget.dataset.iniciado = '1';

    const boton = document.getElementById('btnAlertasAdmin');
    const popover = document.getElementById('popoverAlertasAdmin');
    const cerrar = document.getElementById('cerrarAlertasAdmin');
    const actualizar = document.getElementById('actualizarAlertasAdmin');
    const badge = document.getElementById('badgeAlertasAdmin');
    const resumen = document.getElementById('resumenAlertasAdmin');
    const lista = document.getElementById('listaAlertasAdmin');
    const url = '<?= base_url('alertas/resumen') ?>';
    let consultaEnCurso = false;

    function escapar(valor) {
        return String(valor ?? '').replace(/[&<>'"]/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
        })[c]);
    }

    function formatoNumero(valor) {
        return Number(valor || 0).toLocaleString('es-MX', { maximumFractionDigits: 1 });
    }

    function pintar(data) {
        const criticos = Number(data.criticos || 0);
        const atencion = Number(data.atencion || 0);
        const total = criticos + atencion;

        badge.textContent = total > 99 ? '99+' : String(total);
        badge.style.display = total > 0 ? 'inline-block' : 'none';
        badge.classList.toggle('atencion', criticos === 0 && atencion > 0);

        if (criticos > 0) {
            resumen.textContent = criticos + (criticos === 1 ? ' producto necesita' : ' productos necesitan') + ' reposición urgente.' +
                (atencion > 0 ? ' Además, ' + atencion + ' requieren revisión.' : '');
        } else if (atencion > 0) {
            resumen.textContent = atencion + (atencion === 1 ? ' producto necesita' : ' productos necesitan') + ' revisión pronto.';
        } else {
            resumen.textContent = 'No hay productos en riesgo por ahora.';
        }

        const urgentes = Array.isArray(data.urgentes) ? data.urgentes : [];
        if (!urgentes.length) {
            lista.innerHTML = '<div class="alertas-admin-vacio"><i class="fas fa-circle-check"></i> Inventario estable por ahora.</div>';
            return;
        }

        lista.innerHTML = urgentes.map(p => {
            const cobertura = p.cobertura === null || p.cobertura === undefined
                ? 'sin consumo reciente'
                : 'alcanza para ' + formatoNumero(p.cobertura) + ' día(s)';
            return '<div class="alertas-admin-item">' +
                '<strong><i class="fas fa-triangle-exclamation"></i> ' + escapar(p.nombre) + '</strong>' +
                '<small>Quedan ' + formatoNumero(p.stock) + ' ' + escapar(p.unidad) + '; ' + cobertura + '.</small>' +
                '</div>';
        }).join('');
    }

    async function cargar() {
        if (consultaEnCurso) return;
        consultaEnCurso = true;
        try {
            const respuesta = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!respuesta.ok) throw new Error('HTTP ' + respuesta.status);
            pintar(await respuesta.json());
        } catch (error) {
            badge.style.display = 'none';
            resumen.textContent = 'No se pudo consultar el inventario.';
            lista.innerHTML = '<div class="alertas-admin-vacio" style="background:#fff7ed;color:#9a3412;"><i class="fas fa-circle-exclamation"></i> Intenta actualizar en unos segundos.</div>';
        } finally {
            consultaEnCurso = false;
        }
    }

    function abrir() {
        popover.hidden = false;
        boton.setAttribute('aria-expanded', 'true');
        cargar();
    }
    function cerrarPopover() {
        popover.hidden = true;
        boton.setAttribute('aria-expanded', 'false');
    }

    boton.addEventListener('click', () => popover.hidden ? abrir() : cerrarPopover());
    cerrar.addEventListener('click', cerrarPopover);
    actualizar.addEventListener('click', cargar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarPopover(); });
    document.addEventListener('click', e => { if (!widget.contains(e.target)) cerrarPopover(); });

    cargar();
    setInterval(cargar, 60000);
})();
</script>
