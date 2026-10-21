<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EntradaModel;
use App\Models\ProductoModel;

class GestionEntradas extends BaseController
{
    private const UNIDADES_COMPRA = ['caja', 'kilo', 'domos', 'mazo', 'arpilla', 'ramos'];
    private const UNIDADES_VENTA  = ['caja', 'kilo', 'domos', 'mazo', 'arpilla', 'ramos', 'pieza'];
    private const CATEGORIAS      = ['Frutas', 'Verduras', 'Hierbas'];

    public function guardar()
    {
        $entradaModel     = new EntradaModel();
        $productoModel    = new ProductoModel();

        $ids_productos     = (array) $this->request->getPost('id_producto');
        $precios_compra    = (array) $this->request->getPost('precio_compra');
        $cantidades_compra = (array) $this->request->getPost('cantidad_compra');
        $unidades_compra   = (array) $this->request->getPost('unidad_compra');
        $precios_sugeridos = (array) $this->request->getPost('precio_sugerido');
        $unidades_venta    = (array) $this->request->getPost('unidad_venta');
        $conversiones      = (array) $this->request->getPost('valor_conversion');
        $cantidades_venta  = (array) $this->request->getPost('cantidad_venta');
        $categorias        = (array) $this->request->getPost('categoria');

        if (empty($ids_productos)) {
            return redirect()->back()->with('error', 'La lista de productos está vacía.');
        }

        // ---------- 1. VALIDAR TODO ANTES DE GUARDAR ----------
        $existentes = $productoModel->getIdsExistentes($ids_productos);

        $filas = [];
        foreach ($ids_productos as $i => $id) {
            $n = $i + 1;

            $id     = (int) $id;
            $pc     = (float) ($precios_compra[$i]    ?? 0);
            $cant   = (float) ($cantidades_compra[$i] ?? 0);
            $ps     = (float) ($precios_sugeridos[$i] ?? 0);
            $uc     = (string) ($unidades_compra[$i]  ?? '');
            $uv     = (string) ($unidades_venta[$i]   ?? '');
            $cat    = (string) ($categorias[$i]       ?? '');
            $conv   = (float) ($conversiones[$i]      ?? 0);
            $cv     = (float) ($cantidades_venta[$i]  ?? 0);

            if (!in_array($id, $existentes, true)) {
                return redirect()->back()->with('error', "Fila $n: el producto no existe.");
            }
            if ($pc <= 0 || $cant <= 0 || $ps <= 0) {
                return redirect()->back()->with('error', "Fila $n: el precio de compra, la cantidad y el precio sugerido deben ser mayores a cero.");
            }
            if (!in_array($uc, self::UNIDADES_COMPRA, true) || !in_array($uv, self::UNIDADES_VENTA, true)) {
                return redirect()->back()->with('error', "Fila $n: la unidad de compra o de venta no es válida.");
            }
            if (!in_array($cat, self::CATEGORIAS, true)) {
                return redirect()->back()->with('error', "Fila $n: la categoría no es válida.");
            }

            // Mismo cálculo de siempre: cantidad de compra × conversión.
            // Si por alguna razón no llega la conversión, se usa la cantidad de venta enviada.
            if ($conv > 0) {
                $cv = round($cant * $conv, 2);
            } elseif ($cv > 0) {
                $conv = round($cv / $cant, 4);
            } else {
                return redirect()->back()->with('error', "Fila $n: falta la conversión entre unidad de compra y de venta.");
            }

            $filas[] = [
                'id_producto'     => $id,
                'precio_compra'   => $pc,
                'cantidad_compra' => $cant,
                'unidad_compra'   => $uc,
                'precio_sugerido' => $ps,
                'unidad_venta'    => $uv,
                'cantidad_venta'  => $cv,
                'categoria'       => $cat,
                '_conv'           => $conv,
            ];
        }

        // ---------- 2. GUARDAR TODO O NADA (la transacción vive en el modelo) ----------
        if (!$entradaModel->registrarEntradas($filas)) {
            return redirect()->to(base_url('inventario'))
                ->with('error', 'No se guardó ninguna entrada porque ocurrió un error. Intenta de nuevo.');
        }

        return redirect()->to(base_url('inventario'))
            ->with('mensaje', '¡' . count($filas) . ' entrada(s) registrada(s) correctamente!');
    }
}