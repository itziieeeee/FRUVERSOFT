/**
 * FRUVER · Modo oscuro
 *
 * Lo agrega el filtro App\Filters\ModoOscuro a todas las pantallas. La preferencia se guarda en
 * localStorage ("fruver-tema") y es la misma que usan las pantallas del repartidor.
 *
 * Cómo oscurece sin cambiar la marca:
 *  - Lee las hojas de estilo de la pantalla (las propias y las de Bootstrap, Leaflet, etc.) y arma una hoja
 *    "oscura" que solo vale con <html data-tema="oscuro">.
 *  - Los fondos claros y neutros (blanco, gris claro) pasan a gris azulado oscuro.
 *  - Los textos oscuros neutros pasan a claros. Los textos de color (verde de marca) se aclaran lo justo para leerse.
 *  - Los colores de marca (verde oscuro de los encabezados, naranja de los botones, rojos y azules sólidos) NO cambian.
 *  - Los fondos de color muy claros (etiquetas verdes, amarillas, rojas pálidas) pasan a una versión oscura del mismo tono.
 */
(function () {
    'use strict';

    var CLAVE = 'fruver-tema';
    var html = document.documentElement;
    var construido = false;
    var construyendo = null;

    /* ===================================================================== */
    /*  Colores                                                               */
    /* ===================================================================== */

    var NOMBRES = {
        white: '#ffffff', snow: '#fffafa', ghostwhite: '#f8f8ff', whitesmoke: '#f5f5f5', gainsboro: '#dcdcdc',
        lightgray: '#d3d3d3', lightgrey: '#d3d3d3', silver: '#c0c0c0', darkgray: '#a9a9a9', darkgrey: '#a9a9a9',
        gray: '#808080', grey: '#808080', dimgray: '#696969', dimgrey: '#696969', black: '#000000',
        ivory: '#fffff0', beige: '#f5f5dc', linen: '#faf0e6', aliceblue: '#f0f8ff', honeydew: '#f0fff0',
        mintcream: '#f5fffa', azure: '#f0ffff', lavender: '#e6e6fa', lightyellow: '#ffffe0', seashell: '#fff5ee'
    };

    var RE_COLOR = /#[0-9a-fA-F]{8}\b|#[0-9a-fA-F]{6}\b|#[0-9a-fA-F]{3,4}\b|rgba?\([^)]*\)|hsla?\([^)]*\)|\b(?:white|snow|ghostwhite|whitesmoke|gainsboro|lightgray|lightgrey|silver|darkgray|darkgrey|gray|grey|dimgray|dimgrey|black|ivory|beige|linen|aliceblue|honeydew|mintcream|azure|lavender|lightyellow|seashell)\b/gi;

    function hex(h) {
        h = h.replace('#', '');
        if (h.length === 3 || h.length === 4) h = h.split('').map(function (c) { return c + c; }).join('');
        var n = parseInt(h.substr(0, 6), 16);
        var a = h.length === 8 ? parseInt(h.substr(6, 2), 16) / 255 : 1;
        return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255, a: a };
    }

    function aRgb(h, s, l) {
        var c = (1 - Math.abs(2 * l - 1)) * s, x = c * (1 - Math.abs((h / 60) % 2 - 1)), m = l - c / 2, r = 0, g = 0, b = 0;
        if (h < 60) { r = c; g = x; } else if (h < 120) { r = x; g = c; } else if (h < 180) { g = c; b = x; }
        else if (h < 240) { g = x; b = c; } else if (h < 300) { r = x; b = c; } else { r = c; b = x; }
        return { r: Math.round((r + m) * 255), g: Math.round((g + m) * 255), b: Math.round((b + m) * 255) };
    }

    function aHsl(c) {
        var r = c.r / 255, g = c.g / 255, b = c.b / 255;
        var max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min, l = (max + min) / 2, h = 0, s = 0;
        if (d > 0) {
            s = d / (1 - Math.abs(2 * l - 1));
            if (max === r) h = ((g - b) / d) % 6; else if (max === g) h = (b - r) / d + 2; else h = (r - g) / d + 4;
            h = (h * 60 + 360) % 360;
        }
        return { h: h, s: s, l: l };
    }

    function leer(token) {
        token = token.trim().toLowerCase();
        if (token.charAt(0) === '#') return hex(token);
        if (NOMBRES[token]) return hex(NOMBRES[token]);

        var m = token.match(/^(rgba?|hsla?)\(([^)]*)\)$/);
        if (!m) return null;
        var p = m[2].replace(/\//g, ' ').replace(/,/g, ' ').trim().split(/\s+/);
        if (p.length < 3) return null;
        var a = p.length > 3 ? (p[3].indexOf('%') > -1 ? parseFloat(p[3]) / 100 : parseFloat(p[3])) : 1;
        if (isNaN(a)) return null;

        if (m[1].indexOf('rgb') === 0) {
            var v = p.slice(0, 3).map(function (x) { return x.indexOf('%') > -1 ? parseFloat(x) * 2.55 : parseFloat(x); });
            if (v.some(isNaN)) return null;
            return { r: Math.round(v[0]), g: Math.round(v[1]), b: Math.round(v[2]), a: a };
        }
        var h = parseFloat(p[0]), s = parseFloat(p[1]) / 100, l = parseFloat(p[2]) / 100;
        if ([h, s, l].some(isNaN)) return null;
        var rgb = aRgb(((h % 360) + 360) % 360, s, l);
        return { r: rgb.r, g: rgb.g, b: rgb.b, a: a };
    }

    function escribir(c, h, s, l) {
        var rgb = aRgb(h, Math.max(0, Math.min(1, s)), Math.max(0, Math.min(1, l)));
        return c.a < 1
            ? 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + (Math.round(c.a * 1000) / 1000) + ')'
            : 'rgb(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ')';
    }

    // Brillo percibido (0 a 1): distingue el amarillo (claro) del naranja o el verde (de marca)
    function brillo(c) { return (0.299 * c.r + 0.587 * c.g + 0.114 * c.b) / 255; }

    function neutro(hsl) { return hsl.s < 0.16 || hsl.l < 0.06 || hsl.l > 0.97; }

    /** Fondos */
    function mapFondo(c) {
        if (c.a === 0) return null;
        var hsl = aHsl(c), y = brillo(c);

        if (neutro(hsl)) {
            // Capas casi transparentes (rayas de tabla, hover): oscuras en modo claro, claras en modo oscuro
            if (hsl.l < 0.2 && c.a < 0.2) return 'rgba(255,255,255,' + Math.round(c.a * 0.8 * 1000) / 1000 + ')';
            if (hsl.l <= 0.5) return null;                       // ya es oscuro: se queda
            var l2 = 0.06 + 0.11 * Math.pow((hsl.l - 0.5) / 0.5, 12);
            return escribir(c, 218, 0.30, l2);
        }

        if (y > 0.62) {
            // Color muy claro (etiqueta pálida, aviso amarillo): misma tonalidad, versión oscura
            var l3 = Math.max(0.13, Math.min(0.28, 0.15 + (0.97 - y) * 0.45));
            return escribir(c, hsl.h, Math.min(hsl.s * 0.7, 0.45), l3);
        }
        return null;                                             // color de marca: no se toca
    }

    /** Textos */
    function mapTexto(c) {
        if (c.a === 0) return null;
        var hsl = aHsl(c);

        if (neutro(hsl)) {
            if (hsl.l > 0.85) return null;                       // blanco sobre botones de color: se queda
            return escribir(c, 215, 0.16, 0.95 - 0.5 * hsl.l);
        }
        if (hsl.l < 0.55) {
            // Verde oscuro, azul oscuro, etc.: se aclara lo justo para leerse sobre fondo oscuro
            return escribir(c, hsl.h, hsl.s * 0.9, 0.55 + (0.55 - hsl.l) * 0.25);
        }
        return null;
    }

    /** Bordes */
    function mapBorde(c) {
        if (c.a === 0) return null;
        var hsl = aHsl(c), y = brillo(c);

        if (neutro(hsl)) {
            if (hsl.l > 0.5) return escribir(c, 218, 0.22, 0.22 + (1 - hsl.l) * 0.15);
            if (hsl.l < 0.28) return escribir(c, 218, 0.22, 0.28);
            return null;
        }
        if (y > 0.62) return escribir(c, hsl.h, Math.min(hsl.s * 0.5, 0.3), 0.26);
        return null;
    }

    var MAPAS = { fondo: mapFondo, texto: mapTexto, borde: mapBorde };

    /** Cambia los colores de un valor CSS completo (también dentro de degradados) */
    function mapValor(valor, tipo) {
        var f = MAPAS[tipo], cambio = false;

        // Las direcciones url(...) no se tocan
        var partes = valor.split(/(url\([^)]*\))/i);
        for (var i = 0; i < partes.length; i += 2) {
            partes[i] = partes[i].replace(RE_COLOR, function (t) {
                var c = leer(t);
                if (!c) return t;
                var nuevo = f(c);
                if (nuevo) { cambio = true; return nuevo; }
                return t;
            });
        }
        return cambio ? partes.join('') : null;
    }

    /* ===================================================================== */
    /*  Variables CSS (var(--x)) definidas en :root                            */
    /* ===================================================================== */

    var estiloRaiz = null;
    function resolverVars(valor) {
        if (valor.indexOf('var(') === -1) return valor;
        estiloRaiz = estiloRaiz || getComputedStyle(html);
        for (var vuelta = 0; vuelta < 6 && valor.indexOf('var(') > -1; vuelta++) {
            valor = valor.replace(/var\(\s*(--[\w-]+)\s*(?:,\s*([^()]*(?:\([^()]*\)[^()]*)*))?\)/g, function (todo, nombre, respaldo) {
                var v = estiloRaiz.getPropertyValue(nombre).trim();
                return v !== '' ? v : (respaldo !== undefined ? respaldo : todo);
            });
        }
        return valor;
    }

    /* ===================================================================== */
    /*  Reglas CSS -> reglas oscuras                                           */
    /* ===================================================================== */

    var PROP_BORDE = /^(border(-(top|right|bottom|left|block|inline)(-(start|end))?)?(-color)?|outline(-color)?|column-rule(-color)?)$/;

    // Las variables de Bootstrap y de las pantallas (--bs-body-bg, --bg-card, --text-dark...) se convierten según su nombre
    function tipoVariable(nombre) {
        if (/(^|-)(bg|background)(-|$)/.test(nombre)) return 'fondo';
        if (/(^|-)(border|divider|outline)(-|$)/.test(nombre)) return 'borde';
        if (/(^|-)(color|text|fg|foreground)(-|$)/.test(nombre)) return 'texto';
        return null;
    }

    /**
     * Convierte las declaraciones de color de una regla.
     * Los colores que no cambian también se escriben igual: así se conserva el orden de prioridad que la pantalla
     * ya tenía entre sus propias reglas (por ejemplo, un botón con texto blanco sigue ganándole al color de los enlaces).
     */
    function declaraciones(estilo, textoCss) {
        var salida = [];
        var vistos = {};

        // Con var() dentro de un atajo, el navegador devuelve vacío cada parte y el atajo: el valor original solo está en el texto de la regla
        function leerValor(nombre) {
            var v = estilo.getPropertyValue(nombre);
            if (!v && textoCss && /^(background|border(-(top|right|bottom|left))?|outline)$/.test(nombre)) {
                var m = textoCss.match(new RegExp("(?:^|[;{\\s])" + nombre + "\\s*:\\s*([^;}]+)"));
                if (m) v = m[1].replace(/\s*!important\s*$/i, "").trim();
            }
            return v;
        }
        var recorteTexto = /text/.test((estilo.getPropertyValue('background-clip') || estilo.getPropertyValue('-webkit-background-clip') || ''));

        var nombres = [];
        for (var k = 0; k < estilo.length; k++) nombres.push(estilo[k]);

        // Cuando un atajo usa var() ("background: var(--verde)") el navegador no lista sus partes: se revisa el atajo completo
        [['background', 'background-color'], ['border', 'border-top-color'], ['border-top', 'border-top-color'],
         ['border-right', 'border-right-color'], ['border-bottom', 'border-bottom-color'], ['border-left', 'border-left-color'],
         ['outline', 'outline-color']].forEach(function (par) {
            if (nombres.indexOf(par[0]) === -1 && leerValor(par[0]) && !estilo.getPropertyValue(par[1])) {
                nombres.push(par[0]);
            }
        });

        // Título con degradado (background-clip:text) cuyo degradado usa var(): el navegador no deja leerlo.
        // Se le pone un degradado verde claro, para que el título se lea sobre el fondo oscuro.
        if (recorteTexto && nombres.indexOf('background-image') > -1 && !estilo.getPropertyValue('background-image') && !leerValor('background')) {
            salida.push('background-image:linear-gradient(120deg,#57b36a,#8fd19e) !important');
        }

        for (var i = 0; i < nombres.length; i++) {
            var nombre = nombres[i];
            if (vistos[nombre]) continue;
            vistos[nombre] = true;

            var valor = leerValor(nombre);
            if (!valor) continue;

            var tipo = null, prop = nombre, esVariable = nombre.indexOf('--') === 0;
            if (esVariable) {
                tipo = tipoVariable(nombre);
                valor = valor.trim();
            } else {
                valor = resolverVars(valor);
                if (nombre === 'color') tipo = 'texto';
                else if (nombre === 'background-color') tipo = 'fondo';
                // Texto con degradado (background-clip:text): el degradado es el color del texto
                else if (nombre === 'background-image') tipo = recorteTexto ? 'texto' : 'fondo';
                else if (nombre === 'background') { tipo = recorteTexto ? 'texto' : 'fondo'; prop = null; }
                else if (PROP_BORDE.test(nombre)) tipo = 'borde';
            }
            if (!tipo) continue;

            var nuevo = mapValor(valor, tipo);
            if (!nuevo) {
                var esColor = nombre === 'color' || nombre === 'background-color' || (tipo === 'borde' && /-color$/.test(nombre)) ||
                    // "background: var(--verde)" y similares: si es un color liso también se conserva su prioridad
                    (nombre === 'background' && !/gradient\(/i.test(valor) && valor.search(RE_COLOR) > -1);
                if (!esColor) continue;
                nuevo = valor;                                   // igual que el original, solo para conservar la prioridad
            }
            if (esVariable) { salida.push(prop + ':' + nuevo + ' !important'); continue; }

            if (prop) {
                // Atajos como "border-bottom: 1px solid #eee": se cambia solo el color
                if (tipo === 'borde' && !/-color$/.test(prop)) {
                    var color = nuevo.match(RE_COLOR);
                    if (!color) continue;
                    salida.push(prop + '-color:' + color[0] + ' !important');
                } else {
                    salida.push(prop + ':' + nuevo + ' !important');
                }
            } else {
                // "background:" completo: si trae degradado se reemplaza la imagen, si no solo el color
                if (/gradient\(/i.test(nuevo)) {
                    salida.push('background-image:' + nuevo.replace(/^.*?((?:repeating-)?(?:linear|radial|conic)-gradient\(.*\)).*$/i, '$1') + ' !important');
                } else {
                    var c = nuevo.match(RE_COLOR);
                    if (c) salida.push('background-color:' + c[c.length - 1] + ' !important');
                }
            }
        }
        return salida;
    }

    // Separa "a, b:is(c, d), e" sin cortar dentro de paréntesis, corchetes o comillas
    function separarSelectores(texto) {
        var res = [], nivel = 0, actual = '', comilla = '';
        for (var i = 0; i < texto.length; i++) {
            var ch = texto.charAt(i);
            if (comilla) { if (ch === comilla) comilla = ''; }
            else if (ch === '"' || ch === "'") comilla = ch;
            else if (ch === '(' || ch === '[') nivel++;
            else if (ch === ')' || ch === ']') nivel--;
            else if (ch === ',' && nivel === 0) { res.push(actual.trim()); actual = ''; continue; }
            actual += ch;
        }
        if (actual.trim()) res.push(actual.trim());
        return res;
    }

    var PREFIJO = 'html[data-tema="oscuro"]';

    function prefijar(selector) {
        if (/^:root\b/.test(selector))  return PREFIJO + selector.replace(/^:root/, '');
        if (/^html\b/.test(selector))   return PREFIJO + selector.replace(/^html/, '');
        return PREFIJO + ' ' + selector;
    }

    function reglas(lista, salida) {
        for (var i = 0; i < lista.length; i++) {
            var r = lista[i];
            try {
                if (r.type === 1 && r.style) {                       // regla normal
                    var d = declaraciones(r.style, r.cssText);
                    if (d.length) {
                        var sels = separarSelectores(r.selectorText).map(prefijar).join(',');
                        salida.push(sels + '{' + d.join(';') + '}');
                    }
                } else if (r.type === 4 && r.cssRules) {             // @media
                    var tmp = [];
                    reglas(r.cssRules, tmp);
                    if (tmp.length) salida.push('@media ' + r.media.mediaText + '{' + tmp.join('') + '}');
                } else if (r.type === 12 && r.cssRules) {            // @supports
                    var tmp2 = [];
                    reglas(r.cssRules, tmp2);
                    if (tmp2.length) salida.push('@supports ' + r.conditionText + '{' + tmp2.join('') + '}');
                } else if (r.type === 3 && r.styleSheet) {           // @import
                    reglas(r.styleSheet.cssRules, salida);
                }
            } catch (e) { /* una regla rara no debe detener a las demás */ }
        }
    }

    // Hojas de otros dominios (Bootstrap, Leaflet...) no se pueden leer directo: se descargan y se leen aparte
    function leerExterna(url) {
        return fetch(url, { mode: 'cors', credentials: 'omit' })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(function (texto) {
                var doc = document.implementation.createHTMLDocument('');
                var st = doc.createElement('style');
                st.textContent = texto;
                doc.head.appendChild(st);
                return st.sheet ? st.sheet.cssRules : [];
            });
    }

    function omitir(hoja) {
        var nodo = hoja.ownerNode;
        if (hoja.disabled) return true;
        if (nodo && (nodo.id === 'fe-oscuro-css' || nodo.id === 'fe-oscuro-inline' || nodo.id === 'fe-estilos')) return true;
        if (hoja.href && hoja.href.indexOf('modo-oscuro') > -1) return true;
        var medios = hoja.media && hoja.media.mediaText;
        if (medios && /^print$/i.test(medios.trim())) return true;
        return false;
    }

    function construir() {
        if (construido) return Promise.resolve();
        if (construyendo) return construyendo;

        var trabajos = [];
        var piezas = [];
        var hojas = Array.prototype.slice.call(document.styleSheets);

        hojas.forEach(function (hoja, indice) {
            if (omitir(hoja)) return;
            var lista = null;
            try { lista = hoja.cssRules; } catch (e) { lista = null; }

            if (lista) {
                var tmp = [];
                reglas(lista, tmp);
                piezas[indice] = tmp.join('');
            } else if (hoja.href) {
                trabajos.push(leerExterna(hoja.href).then(function (cr) {
                    var tmp = [];
                    reglas(cr, tmp);
                    piezas[indice] = tmp.join('');
                }).catch(function () { /* sin permiso para leerla: queda con sus colores */ }));
            }
        });

        construyendo = Promise.all(trabajos).then(function () {
            var css = piezas.filter(Boolean).join('\n');
            var st = document.getElementById('fe-oscuro-css');
            if (!st) {
                st = document.createElement('style');
                st.id = 'fe-oscuro-css';
                document.head.appendChild(st);
            }
            // Solo en pantalla: al imprimir se usan los colores normales
            st.textContent = '@media screen{' + css + '}';
            construido = true;
            inline.iniciar();
        });
        return construyendo;
    }

    /* ===================================================================== */
    /*  Estilos escritos directamente en las etiquetas (style="...")           */
    /* ===================================================================== */

    var inline = (function () {
        var contador = 0, reglasPorId = {}, pendiente = false, observador = null;

        function volcar() {
            pendiente = false;
            var st = document.getElementById('fe-oscuro-inline');
            if (!st) {
                st = document.createElement('style');
                st.id = 'fe-oscuro-inline';
                document.head.appendChild(st);
            }
            var texto = Object.keys(reglasPorId).map(function (k) { return reglasPorId[k]; }).join('');
            st.textContent = '@media screen{' + texto + '}';
        }

        function programar() {
            if (pendiente) return;
            pendiente = true;
            (window.requestAnimationFrame || setTimeout)(volcar);
        }

        function procesar(el) {
            if (!el || el.nodeType !== 1 || !el.style || !el.hasAttribute('style')) return;
            var d = declaraciones(el.style, el.getAttribute("style"));
            var id = el.getAttribute('data-fe-i');

            if (!d.length) {
                if (id && reglasPorId[id]) { delete reglasPorId[id]; programar(); }
                return;
            }
            if (!id) { id = String(++contador); el.setAttribute('data-fe-i', id); }
            reglasPorId[id] = PREFIJO + ' [data-fe-i="' + id + '"]{' + d.join(';') + '}';
            programar();
        }

        function arbol(raiz) {
            if (!raiz || raiz.nodeType !== 1) return;
            procesar(raiz);
            var hijos = raiz.querySelectorAll('[style]');
            for (var i = 0; i < hijos.length; i++) procesar(hijos[i]);
        }

        function iniciar() {
            arbol(document.body);
            if (observador || !window.MutationObserver) return;
            observador = new MutationObserver(function (cambios) {
                cambios.forEach(function (c) {
                    if (c.type === 'attributes') procesar(c.target);
                    else c.addedNodes.forEach(arbol);
                });
            });
            observador.observe(document.body, { subtree: true, childList: true, attributes: true, attributeFilter: ['style'] });
        }

        return { iniciar: iniciar };
    })();

    /* ===================================================================== */
    /*  Tema y botón                                                           */
    /* ===================================================================== */

    function temaActual() { return html.getAttribute('data-tema') === 'oscuro' ? 'oscuro' : 'claro'; }

    function guardar(tema) {
        try { localStorage.setItem(CLAVE, tema); } catch (e) { /* modo privado: solo dura esta pantalla */ }
    }

    function mostrarPagina() { html.removeAttribute('data-fe-carga'); }

    function pintarBoton(btn) {
        var oscuro = temaActual() === 'oscuro';
        btn.classList.toggle('activo', oscuro);
        btn.setAttribute('aria-pressed', oscuro ? 'true' : 'false');
        btn.setAttribute('aria-label', oscuro ? 'Desactivar modo oscuro' : 'Activar modo oscuro');
        btn.title = oscuro ? 'Modo claro' : 'Modo oscuro';
    }

    function aplicar(tema, btn) {
        if (tema === 'oscuro') {
            html.setAttribute('data-fe-carga', '1');
            html.setAttribute('data-tema', 'oscuro');
            construir().catch(function () { /* se queda con lo que alcanzó a convertir */ }).then(mostrarPagina);
        } else {
            html.setAttribute('data-tema', 'claro');
            mostrarPagina();
        }
        if (btn) pintarBoton(btn);
    }

    function asegurarIconos() {
        if (document.querySelector('link[href*="font-awesome"], link[href*="fontawesome"]')) return;
        var l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
        document.head.appendChild(l);
    }

    // El botón va junto al buscador del encabezado; si la pantalla no tiene buscador, junto a los botones del encabezado
    function ubicar(btn) {
        var buscador = document.querySelector('.buscador, .buscador-header-wrap, .search-box, .search-bar, form[role="search"]');
        if (!buscador) {
            var campo = document.querySelector('header input[type="text"], header input[type="search"], nav input[type="search"], input[type="search"]');
            if (campo) buscador = campo.closest('form') || campo.parentElement;
        }
        if (buscador && buscador.parentElement) {
            buscador.insertAdjacentElement('afterend', btn);
            btn.classList.add('fe-tema-enlinea');
            return;
        }

        var acciones = document.querySelector('.user-actions, .user-actions-wrap, .header-actions');
        if (acciones) {
            acciones.insertBefore(btn, acciones.firstChild);
            btn.classList.add('fe-tema-enlinea');
            return;
        }

        var cab = document.querySelector('header, .barra-superior, .top-bar, nav.navbar');
        if (cab) {
            cab.appendChild(btn);
            btn.classList.add('fe-tema-enlinea');
            return;
        }

        btn.classList.add('fe-tema-flotante');
        document.body.appendChild(btn);
    }

    function crearBoton() {
        if (document.getElementById('fe-tema-btn')) return;
        asegurarIconos();

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'fe-tema-btn';
        btn.innerHTML = '<i class="fas fa-moon" aria-hidden="true"></i>';
        pintarBoton(btn);
        ubicar(btn);

        btn.addEventListener('click', function () {
            var nuevo = temaActual() === 'oscuro' ? 'claro' : 'oscuro';
            guardar(nuevo);
            aplicar(nuevo, btn);
        });

        // Si el tema cambia en otra pestaña, esta se actualiza sola
        window.addEventListener('storage', function (e) {
            if (e.key === CLAVE) aplicar(e.newValue === 'oscuro' ? 'oscuro' : 'claro', btn);
        });

        // Si la pantalla se abrió en modo oscuro, se construye la hoja oscura
        if (temaActual() === 'oscuro') aplicar('oscuro', btn);
        else mostrarPagina();
    }

    if (document.body) crearBoton();
    else document.addEventListener('DOMContentLoaded', crearBoton);
})();
