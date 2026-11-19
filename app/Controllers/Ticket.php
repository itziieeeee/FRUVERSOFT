<?php

namespace App\Controllers;

class Ticket extends BaseController
{
    /**
     * Obtiene pedido, cliente, repartidor y productos.
     */
    private function datos(int $id): ?array
    {
        $db = \Config\Database::connect();

        $pedido = $db->table('pedido p')
            ->select('
                p.*,
                c.nombre,
                c.apellido_paterno,
                c.tel,
                CONCAT(r.nombre, " ", r.ap_p) AS repartidor
            ')
            ->join('clientes c', 'c.id_cliente = p.id_cliente', 'left')
            ->join('repartidor r', 'r.id = p.id_repartidor', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();

        if (!$pedido) {
            return null;
        }

        $items = $db->table('producto_pedido pp')
            ->select('
                pp.cantidad,
                pp.unidad_venta AS unidad,
                pp.precio_venta AS precio_unitario,
                COALESCE(
                    pp.subtotal,
                    pp.total,
                    pp.cantidad * pp.precio_venta
                ) AS subtotal,
                p.nombre AS nombre_producto
            ')
            ->join('producto p', 'p.id = pp.id_producto')
            ->where('pp.id_pedido', $id)
            ->get()
            ->getResultArray();

        return [
            'pedido' => $pedido,
            'items'  => $items,
            'logo'   => $this->logo()
        ];
    }

    /**
     * Obtiene el logo en Base64.
     */
    private function logo(): string
    {
        $rutas = [
            FCPATH . 'img/LOGO1.png',
            FCPATH . 'IMG/LOGO1.png'
        ];

        foreach ($rutas as $archivo) {
            if (is_file($archivo)) {
                $contenido = file_get_contents($archivo);

                if ($contenido !== false) {
                    return 'data:image/png;base64,' . base64_encode($contenido);
                }
            }
        }

        return '';
    }

    /**
     * Muestra el ticket y prepara el enlace de WhatsApp.
     */
    public function ver($id)
    {
        $id = (int) $id;

        $datos = $this->datos($id);

        if (!$datos) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('Pedido no encontrado');
        }

        helper('whatsapp');

        // Teléfono del cliente
        $telefono = preg_replace(
            '/\D+/',
            '',
            (string) ($datos['pedido']['tel'] ?? '')
        );

        // Preparar enlace para compartir el PDF por WhatsApp
        $urlWhatsApp = generar_link_pdf_wa($telefono, $id);

        return view('ticket', $datos + [
            'esPdf'       => false,
            'urlPdf'      => base_url('ticket/pdf/' . $id),
            'urlWhatsApp' => $urlWhatsApp
        ]);
    }

    /**
     * Genera el PDF del ticket en tamaño carta.
     */
    public function pdf($id)
    {
        $id = (int) $id;

        $datos = $this->datos($id);

        if (!$datos) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('Pedido no encontrado');
        }

        // Verificar que Dompdf esté instalado
        if (!class_exists(\Dompdf\Dompdf::class)) {
            return redirect()->to(base_url('ticket/ver/' . $id));
        }

        $html = view('ticket', $datos + [
            'esPdf' => true
        ]);

        $opciones = new \Dompdf\Options();
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($opciones);

        $dompdf->loadHtml($html, 'UTF-8');

        // Hoja tamaño carta
        $dompdf->setPaper('letter', 'portrait');

        $dompdf->render();

        $folio = 'PED-' . str_pad($id, 5, '0', STR_PAD_LEFT);

        $pdf = $dompdf->output();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader(
                'Content-Disposition',
                'inline; filename="Ticket-' . $folio . '.pdf"'
            )
            ->setBody($pdf);
    }

    /**
     * PDF del último pedido no cancelado de un cliente.
     */
    public function ultimo($idCliente)
    {
        $fila = \Config\Database::connect()
            ->table('pedido')
            ->select('id')
            ->where('id_cliente', (int) $idCliente)
            ->where('estado_actual !=', 'Pedido cancelado')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if (!$fila) {
            return redirect()
                ->back()
                ->with('flash_error', 'El cliente todavía no tiene pedidos');
        }

        return redirect()->to(
            base_url('ticket/pdf/' . $fila['id'])
        );
    }
}
