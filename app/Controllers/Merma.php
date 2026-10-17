<?php

namespace App\Controllers;

use App\Models\MermaModel;

class Merma extends BaseController
{
    public function index()
    {
        $mermaModel = new MermaModel();

        $data['productos_merma']  = $mermaModel->getProductosConExistencia();
        $data['historial_mermas'] = $mermaModel->getHistorialMermas();

        $grafica = $mermaModel->getDatosGrafica();
        $data['grafica_labels']   = ['Total General'];
        $data['grafica_entradas'] = [(int) $grafica['total_entradas']];
        $data['grafica_mermas']   = [(float) $grafica['total_merma']];

        return view('pantalla_mermas', $data);
    }

    public function guardar()
    {
        date_default_timezone_set('America/Mexico_City');

        $id_producto     = $this->request->getPost('id_producto');
        $cantidad_mermar = (float) $this->request->getPost('cantidad');
        $motivo          = $this->request->getPost('motivo');

        if (!$id_producto || $cantidad_mermar <= 0) {
            return redirect()->back()->with('error', 'Datos no válidos.');
        }

        $mermaModel = new MermaModel();
        $resultado  = $mermaModel->registrarMerma($id_producto, $cantidad_mermar, $motivo);

        if (!$resultado['ok']) {
            return redirect()->back()->with('error', $resultado['error']);
        }

        return redirect()->back()->with('mensaje', '¡Merma registrada exitosamente!');
    }
}