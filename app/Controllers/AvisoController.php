<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AvisoModel;

class AvisoController extends BaseController
{
    public function index()
    {
        $avisoModel = new AvisoModel();
        $data['avisos'] = $avisoModel->orderBy('id', 'DESC')->findAll();

        return view('Admin/vista_avisos', $data);
    }

    public function guardar()
    {
        $avisoModel = new AvisoModel();

        $titulo = $this->request->getPost('titulo');
        $mensaje = $this->request->getPost('mensaje');
        $rolDestino = $this->request->getPost('rol_destino') ?? 'cliente';
        $diasDuracion = (int) $this->request->getPost('dias_duracion');

        $fechaExpiracion = null;
        if ($diasDuracion > 0) {
            $fechaExpiracion = date('Y-m-d', strtotime("+$diasDuracion days"));
        }

        $avisoModel->insert([
            'titulo'           => $titulo,
            'mensaje'          => $mensaje,
            'rol_destino'      => $rolDestino,
            'fecha_expiracion' => $fechaExpiracion,
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(base_url('admin/avisos'))->with('mensaje', '¡Aviso publicado con éxito!');
    }

    public function eliminar($id)
    {
        $avisoModel = new AvisoModel();
        $avisoModel->delete($id);

        return redirect()->to(base_url('admin/avisos'))->with('mensaje', 'Aviso eliminado correctamente.');
    }
}