<?php /* app/Views/Repartidor/partials/tema.php
   Aplica el tema guardado ANTES de renderizar para evitar flash blanco.
   Define variables CSS compartidas para todo el flujo del repartidor. */ ?>
<script>
(function () {
    try {
        var guardado = localStorage.getItem('fruver-tema');
        if (!guardado) {
            guardado = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro';
        }
        document.documentElement.setAttribute('data-tema', guardado);
    } catch (e) {
        document.documentElement.setAttribute('data-tema', 'claro');
    }
})();
</script>
<style>
    /* ============ VARIABLES GLOBALES FRUVER ============ */
    :root {
        --verde: #1d4a27;
        --verde-osc: #153a1e;
        --naranja: #f16b1a;
        --rojo: #dc2626;

        --bg-page: #f5f7fa;
        --bg-card: #ffffff;
        --bg-elev: #ffffff;
        --bg-suave: #f8f9fa;
        --bg-topbar: #1d4a27;
        --bg-nav: #ffffff;

        --texto: #1e293b;
        --texto-suave: #64748b;
        --texto-tenue: #94a3b8;

        --borde: #e0e0e0;
        --borde-suave: #eef2f6;

        --sombra: 0 4px 12px rgba(0,0,0,0.08);
        --sombra-hover: 0 12px 28px rgba(0,0,0,0.14);
        --badge-bg: rgba(255,255,255,0.98);

        /* Semáforo stock */
        --ok-bg: #d1fae5;    --ok-tx: #059669;
        --poco-bg: #fef3c7;  --poco-tx: #d97706;
        --agot-bg: #fee2e2;  --agot-tx: #dc2626;

        color-scheme: light;
    }

    html[data-tema="oscuro"] {
        --bg-page: #0f172a;
        --bg-card: #1e293b;
        --bg-elev: #1e293b;
        --bg-suave: #273449;
        --bg-topbar: #0f3d24;
        --bg-nav: #1e293b;

        --texto: #f1f5f9;
        --texto-suave: #94a3b8;
        --texto-tenue: #64748b;

        --borde: #334155;
        --borde-suave: #273449;

        --sombra: 0 4px 12px rgba(0,0,0,0.5);
        --sombra-hover: 0 12px 28px rgba(0,0,0,0.7);
        --badge-bg: #0f172a;

        --ok-bg: rgba(74,222,128,0.15);   --ok-tx: #4ade80;
        --poco-bg: rgba(251,191,36,0.15); --poco-tx: #fbbf24;
        --agot-bg: rgba(248,113,113,0.15);--agot-tx: #f87171;

        color-scheme: dark;
    }

    /* Transiciones suaves al cambiar de tema */
    html, body { transition: background-color 0.3s ease, color 0.3s ease; }
</style>