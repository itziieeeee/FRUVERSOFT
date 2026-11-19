<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Agrega el botón de modo oscuro a TODAS las pantallas HTML (administrador, vendedor, repartidor y cliente)
 * sin tener que editar cada vista: antes de </head> pone la hoja de estilos y el script que aplica el tema
 * guardado (así no hay parpadeo blanco) y antes de </body> el script que dibuja el botón.
 *
 * No toca:
 *  - respuestas que no son HTML (JSON, PDF...)
 *  - las pantallas del repartidor que ya traen su propio botón y tema (id="btnTema")
 *  - el ticket y las etiquetas, que son documentos para imprimir
 */
class ModoOscuro implements FilterInterface
{
    private const SIN_BOTON = ['ticket/', 'productos/etiquetas', 'costos/pdf'];

    public function before(RequestInterface $request, $arguments = null)
    {
        // Nada que hacer antes de la petición
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $tipo = $response->getHeaderLine('Content-Type');
        if ($tipo !== '' && stripos($tipo, 'text/html') === false) {
            return;
        }

        $cuerpo = $response->getBody();
        if (!is_string($cuerpo) || $cuerpo === '') {
            return;
        }

        $ruta = trim($request->getUri()->getPath(), '/');
        foreach (self::SIN_BOTON as $fragmento) {
            if (str_contains($ruta, $fragmento)) {
                return;
            }
        }

        $posHead = stripos($cuerpo, '</head>');
        $posBody = strripos($cuerpo, '</body>');
        if ($posHead === false || $posBody === false || $posHead > $posBody) {
            return;
        }

        // Pantallas que ya tienen su propio botón de tema
        if (str_contains($cuerpo, 'id="btnTema"') || str_contains($cuerpo, 'id="fe-tema-btn"')) {
            return;
        }

        $css = base_url('css/modo-oscuro.css') . '?v=3';
        $js  = base_url('js/modo-oscuro.js') . '?v=3';

        // Aplica el tema guardado antes de pintar. Si es oscuro, la pantalla espera (máx. 2.5 s) a que se arme la hoja oscura.
        $head = '<script>(function(){try{var d=document.documentElement,t=localStorage.getItem("fruver-tema");'
              . 'if(t==="oscuro"){d.setAttribute("data-tema","oscuro");d.setAttribute("data-fe-carga","1");'
              . 'setTimeout(function(){d.removeAttribute("data-fe-carga");},2500);}'
              . 'else{d.setAttribute("data-tema","claro");}}catch(e){}})();</script>'
              . '<link rel="stylesheet" href="' . $css . '">';
        $pie  = '<script src="' . $js . '"></script>';

        // Se inserta primero el pie (posición mayor) para no mover la posición de </head>
        $cuerpo = substr_replace($cuerpo, $pie, $posBody, 0);
        $cuerpo = substr_replace($cuerpo, $head, $posHead, 0);

        $response->setBody($cuerpo);
    }
}
