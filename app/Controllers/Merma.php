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

    $data['historial_mermas'] = $db->table('merma m')
        ->select('p.nombre, m.cantidad, m.motivo, m.fecha')
        ->join('entrada e', 'e.id = m.id_entrada')
        ->join('producto p', 'p.id = e.id_producto')
        ->orderBy('m.fecha', 'DESC')
        ->get()
        ->getResultArray();

    $grafica = $db->query("
    SELECT 
        COALESCE(SUM(e.cantidad_compra), 0) AS total_entradas,
        COALESCE(SUM(m.cantidad), 0) AS total_merma
    FROM entrada e
    LEFT JOIN merma m ON m.id_entrada = e.id
")->getRowArray();

$data['grafica_labels']   = ['Total General'];
$data['grafica_entradas'] = [(int) $grafica['total_entradas']];
$data['grafica_mermas']   = [(float) $grafica['total_merma']];

    return view('pantalla_mermas', $data);
}


public function guardar()
{
    date_default_timezone_set('America/Mexico_City');
    $db = \Config\Database::connect();

    // 1. Capturar datos del formulario
    $id_producto = $this->request->getPost('id_producto'); 
    $cantidad_mermar = (float) $this->request->getPost('cantidad');
    $motivo = $this->request->getPost('motivo');

    // Validación de seguridad
    if (!$id_producto || $cantidad_mermar <= 0) {
        // AJUSTE: Cambia 'mermas' por el nombre real de tu ruta de la vista
        return redirect()->back()->with('error', 'Datos no válidos.');
    }

    // 2. BUSCAR LA ENTRADA (Lote) DE DONDE SACAR LA MERMA
    $entrada = $db->table('entrada')
                  ->where('id_producto', $id_producto)
                  ->where('cantidad_venta >', 0) 
                  ->orderBy('fecha', 'ASC') 
                  ->get()
                  ->getRowArray();

    if (!$entrada) {
        return redirect()->back()->with('error', 'No hay stock disponible para este producto en las entradas.');
    }

    // 3. INSERTAR EN TABLA MERMA
    // Tu tabla pide: id, cantidad, fecha, motivo, id_entrada, aplicada
    $db->table('merma')->insert([
        'cantidad'   => $cantidad_mermar,
        'fecha'      => date('Y-m-d H:i:s'), 
        'motivo'     => $motivo,
        'id_entrada' => $entrada['id'],
        'aplicada'   => 1 // Marcamos como aplicada
    ]);

    // 4. ACTUALIZAR TABLA ENTRADA (Descontar del lote)
    $db->table('entrada')
       ->where('id', $entrada['id'])
       ->update(['cantidad_venta' => $entrada['cantidad_venta'] - $cantidad_mermar]);

    // 5. ACTUALIZAR EXISTENCIAS TOTALES
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

    // AJUSTE: Asegúrate que esta ruta 'inventario/mermas' o similar exista
    return redirect()->back()->with('mensaje', '¡Merma registrada exitosamente!');
}


}

