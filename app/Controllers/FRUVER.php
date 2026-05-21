<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use App\Models\StatusModel;
use App\Models\RepartidorModel;

class FRUVER extends BaseController//controador principal
{
    // ==========================
    // 1. INICIO Y SESIÓN
    // ==========================
    public function index(): string
    {
        return view('Pantalla_inicio');
    }

    public function usuario()
    {
        return view('pantalla_usuario');
    }

    public function validar()
    {
        return redirect()->to(base_url('seleccionar_rol'));
    }

    public function seleccionar_rol()
    {
        return view('seleccionar_rol');
    }

    // ==========================
    // 2. ADMINISTRACIÓN Y USUARIOS
    // ==========================
    public function reuser()
    {
        return view('registrar_usuario');
    }

    public function guardar()
    {
        $model = new UsuarioModel();
        $data = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'nusuario'        => $this->request->getPost('nusuario'),
            'apellido1'       => $this->request->getPost('apellido1'),
            'apellido2'       => $this->request->getPost('apellido2'),
            'contrasena'      => password_hash($this->request->getPost('contrasena'), PASSWORD_DEFAULT),
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('menusolo'));
        }
    }

    public function menusolo()
    {
        return view('menusolo');
    }

    public function pantalla_administrador()
    {
        return view('pantalla_administrador');
    }

    // ==========================
    // 3. CLIENTES
    // ==========================
    public function pantalla_clientes()
    {
        return view('pantalla_clientes');
    }

    public function nuevo_cliente()
    {
        return view('alta_cliente'); 
    }

    public function guardar_cliente()
    {
        $modelo = new ClienteModel();
        $datos = [
            'nombre'           => $this->request->getPost('nombre'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'rfc'              => $this->request->getPost('rfc'),
            'tipo_cliente'     => $this->request->getPost('tipo_cliente')
        ];
        $modelo->insert($datos);
        return redirect()->to(base_url('pantalla_clientes'));
    }

    // 
    // 

// Función interna para procesar mermas por fecha (CORREGIDA)
    private function revisarCaducados()
    {
        $db = \Config\Database::connect();
        $hoy = date('Y-m-d');

        // Buscamor entrada
        $caducados = $db->table('entrada')
                        ->where('fecha_cad <=', $hoy)
                        ->where('cantidad_venta >', 0)
                        ->get()
                        ->getResultArray();

        foreach ($caducados as $fila) {
            $cantidad_mermar = $fila['cantidad_venta'];

            //Registro en tabla merma
            $db->table('merma')->insert([
                'id_entrada' => $fila['id'], 
                'cantidad'   => $cantidad_mermar,
                'motivo'     => 'SISTEMA: CADUCIDAD AUTOMÁTICA (5 DÍAS)',
                'fecha'      => $hoy
            ]);

            //Actualizar existencias
            $existencia = $db->table('existencias')
                             ->where('id_producto', $fila['id_producto'])
                             ->get()
                             ->getRowArray();
                             
            if ($existencia) {
                $db->table('existencias')
                   ->where('id_producto', $fila['id_producto'])
                   ->update([
                       'e_total' => $existencia['e_total'] - $cantidad_mermar,
                       'e_merma' => $existencia['e_merma'] + $cantidad_mermar
                   ]);
            }

            // Vaciamor la cantidad de entrada para que no se procese doble mañana
            $db->table('entrada')
               ->where('id', $fila['id']) // Antes decía 'id'
               ->update(['cantidad_venta' => 0]);         // Antes decía 'cantidad'
        }
    }

    public function inventario()
    {
        // Limpiamo antes de mostrar la tabla
        $this->revisarCaducados(); 

        $db = \Config\Database::connect();

        // Lista general de productos para la modal de Entrada
        $productos = $db->table('producto')->get()->getResultArray();

        // Stock consolidao para mostrar en la tabla principal
        $existencias = $db->table('existencias e')
            ->select('p.nombre, e.e_total, e.e_merma')
            ->join('producto p', 'p.id = e.id_producto')
            ->get()
            ->getResultArray();

        // Productos que tienen stock real para poder hacerles Merma manual
        $productos_merma = $db->table('existencias e')
            ->select('p.id as id_p, p.nombre, e.e_total')
            ->join('producto p', 'p.id = e.id_producto')
            ->where('e.e_total >', 0)
            ->get()
            ->getResultArray();

        $data = [
            'productos'       => $productos,
            'productos_merma' => $productos_merma,
            'existencias'     => $existencias
        ];

        return view('inventario', $data); 
    }

    // vendedor
    public function pantalla_vendedor()
    {
        $data['productos'] = []; 
        $data['termino'] = "";
        return view('vendedor/panel_principal', $data);
    }

    public function buscar_producto()
    {
        $model = new \App\Models\ProductoModel(); 
        $termino = $this->request->getGet('query'); 
        $data['productos'] = $model->like('nombre', $termino)->findAll();
        $data['termino']   = $termino;

        return view('vendedor/panel_p', $data);
    }

    
    public function pantalla_inicio()
    {
        return view('pantalla_inicio');
    }

    public function productos()
    {
        return view('productos');
    }

    public function pantalla_ventas()
    {
        return view('pantalla_ventas');
    }

    public function pantalla_pedidos()
    {
        $model = new StatusModel();
        $data['sp'] = $model->obtenerPedidos();
        return view('pantalla_pedidos', $data);
    }

    public function pantalla_productos()
{
    $model = new \App\Models\ProductoModel();

    $data = [
        'productos' => $model->orderBy('id', 'DESC')->paginate(10),
        'pager'     => $model->pager
    ];

    return view('pantalla_productos', $data);
}
public function mostrar_repartidores() 
{
    $db    = \Config\Database::connect();
    $model = new \App\Models\RepartidorModel(); 

    // Paginación
    $repartidores = $model->paginate(6); 
    $paginaActual = $model->pager->getCurrentPage();
    $totalPaginas = $model->pager->getPageCount();
    $baseUrl      = base_url('pantalla_repartidores') . '?page=';

    // Query de pedidos
    $pedidosRaw = $db->query("
        SELECT 
            p.id_repartidor,
            p.id,
            p.estado_actual,
            p.total,
            p.fecha,
            c.nombre AS cliente_nombre,
            c.apellido_paterno AS cliente_ap,
            d.calle,
            d.numero,
            d.colonia,
            d.municipio,
            d.estado AS cliente_estado
        FROM pedido p
        LEFT JOIN clientes c  ON c.id_cliente = p.id_cliente
        LEFT JOIN direccion d ON d.id_cliente = p.id_cliente
        WHERE p.id_repartidor IS NOT NULL
        ORDER BY p.id_repartidor, p.id DESC
    ")->getResultArray();

    // Separar activos y entregados
    $pedidosPorRepartidor    = [];
    $entregadosPorRepartidor = [];
    $estadosEntregado        = ['Venta confirmada', 'Pedido pagado'];

    foreach ($pedidosRaw as $p) {
        $idRep = $p['id_repartidor'];
        if (in_array($p['estado_actual'], $estadosEntregado)) {
            $entregadosPorRepartidor[$idRep][] = $p;
        } else {
            $pedidosPorRepartidor[$idRep][] = $p;
        }
    }

    return view('pantalla_repartidores', [
        'repartidores'            => $repartidores,
        'pedidosPorRepartidor'    => $pedidosPorRepartidor,
        'entregadosPorRepartidor' => $entregadosPorRepartidor,
        'paginaActual'            => $paginaActual,
        'totalPaginas'            => $totalPaginas,
        'baseUrl'                 => $baseUrl,
    ]);
} 
public function guardarrepartidor()
{
    $model = new \App\Models\RepartidorModel();

    // Manejo de foto
    $foto = $this->request->getFile('foto');
    $nombreFoto = null;

    if ($foto && $foto->isValid() && !$foto->hasMoved()) {
        $nombreFoto = $foto->getRandomName();
        $foto->move(FCPATH . 'uploads/repartidores/', $nombreFoto);
    }

    $data = [
        'nombre'    => $this->request->getPost('nombre'),
        'ap_p'      => $this->request->getPost('ap_p'),
        'ap_m'      => $this->request->getPost('ap_m'),
        'tel'       => $this->request->getPost('tel'),
        'direccion' => $this->request->getPost('direccion'),
        'notas'     => $this->request->getPost('notas'),
    ];

    if ($nombreFoto) {
        $data['foto'] = $nombreFoto;
    }

    if ($model->insert($data)) {
        return $this->response->setJSON(['success' => true]);
    } else {
        return $this->response->setJSON(['success' => false, 'error' => $model->errors()]);
    }
}
public function editarrepartidor($id)
{
    $model = new \App\Models\RepartidorModel();

    $foto = $this->request->getFile('foto');
    $data = [
        'nombre'    => $this->request->getPost('nombre'),
        'ap_p'      => $this->request->getPost('ap_p'),
        'ap_m'      => $this->request->getPost('ap_m'),
        'tel'       => $this->request->getPost('tel'),
        'direccion' => $this->request->getPost('direccion'),
        'notas'     => $this->request->getPost('notas'),
    ];

    if ($foto && $foto->isValid() && !$foto->hasMoved()) {
        $nombreFoto = $foto->getRandomName();
        $foto->move(FCPATH . 'uploads/repartidores/', $nombreFoto);
        $data['foto'] = $nombreFoto;
    }

    if ($model->update($id, $data)) {
        return $this->response->setJSON(['success' => true]);
    } else {
        return $this->response->setJSON(['success' => false, 'error' => $model->errors()]);
    }
}

public function eliminarrepartidor($id)
{
    $model = new \App\Models\RepartidorModel();
    $db    = \Config\Database::connect();

    try {
        $repartidor = $model->find($id);
        if (!$repartidor) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        
        $db->table('pedido')
           ->where('id_repartidor', $id)
           ->update(['id_repartidor' => null]);

        if ($model->delete($id)) {
            return $this->response->setJSON(['success' => true]);
        } else {
            return $this->response->setJSON(['success' => false, 'error' => $model->errors()]);
        }
    } catch (\Exception $e) {
        return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
    }
}
}