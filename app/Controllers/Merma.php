<?php

namespace App\Controllers;

class Merma extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $data['productos_merma'] = $db->table('existencias e')
            ->select('p.id as id_p, p.nombre, e.e_total')
            ->join('producto p', 'p.id = e.id_producto')
            ->where('e.e_total >', 0)
            ->get()
            ->getResultArray();

        // Unir merma entrada 
        $data['historial_mermas'] = $db->table('merma m')
            ->select('p.nombre, m.cantidad, m.motivo, m.fecha')
            ->join('entrada e', 'e.id = m.id_entrada')
            ->join('producto p', 'p.id = e.id_producto')
            ->orderBy('m.fecha', 'DESC')
            ->get()
            ->getResultArray();

        return view('pantalla_mermas', $data);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();

        $id_producto = $this->request->getPost('id_producto'); 
        $cantidad_mermar = (float) $this->request->getPost('cantidad');
        $motivo = $this->request->getPost('motivo');

        if (!$id_producto || $cantidad_mermar <= 0) {
            return redirect()->to(base_url('mermas'))->with('error', 'Datos no válidos.');
        }

        $entrada = $db->table('entrada')
                      ->where('id_producto', $id_producto)
                      ->where('cantidad_venta >', 0) 
                      ->orderBy('fecha', 'ASC') 
                      ->get()
                      ->getRowArray();

        if (!$entrada) {
            return redirect()->to(base_url('mermas'))->with('error', 'No hay stock disponible en el lote seleccionado.');
        }

        // Validar que no merme más de lo que hay en ese lote
        if ($entrada['cantidad_venta'] < $cantidad_mermar) {
            return redirect()->to(base_url('mermas'))->with('error', 'La cantidad supera el stock del lote (Disponible: ' . $entrada['cantidad_venta'] . ')');
        }

        // 2. INSERTAR EN MERMA
        $db->table('merma')->insert([
            'cantidad'   => $cantidad_mermar,
            'fecha'      => date('Y-m-d'),
            'motivo'     => $motivo,
            'id_entrada' => $entrada['id'] 
        ]);

        // 3. ACTUALIZAR ENTRADA
        $db->table('entrada')
           ->where('id', $entrada['id'])
           ->update(['cantidad_venta' => $entrada['cantidad_venta'] - $cantidad_mermar]);

        // 4. ACTUALIZAR EXISTENCIAS TOTALES
        $existencia = $db->table('existencias')
                         ->where('id_producto', $id_producto)
                         ->get()
                         ->getRowArray();
                         
        if ($existencia) {
            $db->table('existencias')
               ->where('id_producto', $id_producto)
               ->update([
                   'e_total' => $existencia['e_total'] - $cantidad_mermar,
                   'e_merma' => $existencia['e_merma'] + $cantidad_mermar
               ]);
        }

        return redirect()->to(base_url('mermas'))->with('mensaje', '¡Merma registrada y stock actualizado!');
    }
}
