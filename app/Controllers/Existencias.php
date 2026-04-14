<?php
namespace App\Controllers;
use App\Models\ExistenciasModel;

class Existencias extends BaseController
{
    public function index()
{
    try {
        $model = new ExistenciasModel();

        $productos = $model->getInventario(6);

        $data = [
            'productos' => $productos,
            'pager'     => $model->pager,  // viene del model
        ];

        return view('existencias', $data);

    } catch (\Exception $e) {
        echo "Error BD: " . $e->getMessage();
        die();
    }
}
}