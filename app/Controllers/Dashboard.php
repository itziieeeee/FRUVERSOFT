<?php

namespace App\Controllers;

use App\Services\DashboardService;

/** Resumen del inicio del administrador, en JSON, para que se actualice solo sin recargar la página */
class Dashboard extends BaseController
{
    public function datos()
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-store')
            ->setJSON(['success' => true, 'data' => (new DashboardService())->resumen()]);
    }
}
