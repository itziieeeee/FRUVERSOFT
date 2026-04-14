<?php
namespace App\Controllers;
use App\Models\ExistenciasModel;

class Existencias extends BaseController
{
    public function index()
    {
        try {
            $model = new ExistenciasModel();

            $data = [
                'productos' => $model->getInventario(10), // 10 productos por página
                'pager' => $model->pager,                 // <-- el pager real
            ];

            return view('existencias', $data);

        } catch (\Exception $e) {
            echo "Error BD: " . $e->getMessage();
            die();
        }
    }
}