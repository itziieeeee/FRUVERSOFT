<?php
namespace App\Controllers;

use App\Services\ReporteCostosService;

class Costos extends BaseController
{
    /** Periodo pedido en la URL: ?periodo=hoy|ayer|semana|mes|mes_pasado|rango&desde=&hasta= */
    private function periodoSolicitado(ReporteCostosService $servicio): array
    {
        return $servicio->periodo(
            (string) ($this->request->getGet('periodo') ?: 'hoy'),
            $this->request->getGet('desde'),
            $this->request->getGet('hasta')
        );
    }

    /** Pantalla: ganancias del periodo, costos y variación de costos */
    public function index()
    {
        $servicio   = new ReporteCostosService();
        $periodo    = $this->periodoSolicitado($servicio);
        $idProducto = (int) $this->request->getGet('producto');

        return view('pantalla_costos', [
            'periodo'         => $periodo,
            'reporte'         => $servicio->reporte($periodo['desde'], $periodo['hasta']),
            'variaciones'     => $servicio->variaciones($periodo['desde'], $periodo['hasta']),
            'productos'       => $servicio->listaProductos(),
            'idProducto'      => $idProducto,
            'historial'       => $idProducto ? $servicio->historialProducto($idProducto) : [],
            'tieneConversion' => $servicio->tieneConversion(),
        ]);
    }

    /** PDF del reporte (se genera aquí mismo; no depende de ningún servicio externo) */
    public function pdf()
    {
        if (!class_exists(\Dompdf\Dompdf::class)) {
            return redirect()->to(base_url('costos'))
                ->with('error', 'Falta instalar Dompdf: abre la terminal en el proyecto y corre "composer install".');
        }

        $servicio = new ReporteCostosService();
        $periodo  = $this->periodoSolicitado($servicio);

        $html = view('costos_pdf', [
            'periodo'     => $periodo,
            'reporte'     => $servicio->reporte($periodo['desde'], $periodo['hasta']),
            'variaciones' => $servicio->variaciones($periodo['desde'], $periodo['hasta']),
            'logo'        => $this->logo(),
            'generado'    => (new \DateTime('now', new \DateTimeZone('America/Mexico_City')))->format('d/m/Y h:i A'),
        ]);

        $opciones = new \Dompdf\Options();
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('isRemoteEnabled', false);

        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombre = 'Reporte-costos-' . $periodo['desde']
                . ($periodo['desde'] !== $periodo['hasta'] ? '_a_' . $periodo['hasta'] : '') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombre . '"')
            ->setBody($dompdf->output());
    }

    /** Corrige cuántas unidades de venta trae una unidad de compra en una entrada */
    public function conversion()
    {
        $ok = (new ReporteCostosService())->guardarConversion(
            (int) $this->request->getPost('id_entrada'),
            (float) $this->request->getPost('valor')
        );

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Conversión actualizada.' : 'Escribe un número mayor a 0 (y corre la migración si aún no lo has hecho).',
        ]);
    }

    private function logo(): string
    {
        foreach (['img/LOGO1.png', 'IMG/LOGO1.png'] as $ruta) {
            if (is_file(FCPATH . $ruta)) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . $ruta));
            }
        }
        return '';
    }
}