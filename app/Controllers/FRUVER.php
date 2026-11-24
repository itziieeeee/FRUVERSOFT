<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use App\Models\StatusModel;
use App\Models\RepartidorModel;
use App\Models\ProductoModel;
use App\Models\ExistenciasModel;
use App\Models\EntradaModel;

class FRUVER extends BaseController
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
        // Resumen del día para el inicio del administrador (también se actualiza solo por dashboard/datos)
        return view('menusolo', ['resumen' => (new \App\Services\DashboardService())->resumen()]);
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

    // ==========================
    // 4. INVENTARIO
    // ==========================
    public function inventario()
    {
        $existenciasModel = new ExistenciasModel();
        $productoModel    = new ProductoModel();
        $entradaModel     = new EntradaModel();

        // Limpiamos caducados antes de mostrar la tabla
        $existenciasModel->procesarCaducados();

        $data = [
            'productos'       => $productoModel->findAll(),
            'catalogo'        => $entradaModel->getCatalogoEntrada(),
            'productos_merma' => $existenciasModel->getProductosConStock(),
            'existencias'     => $existenciasModel->getStockConsolidado(),
        ];

        return view('inventario', $data); 
    }

    // ==========================
    // 5. VENDEDOR
    // ==========================
    

public function pantalla_vendedor()
{
    $existenciasModel = new \App\Models\ExistenciasModel();

    $productos = $existenciasModel->getInventario(100);

    foreach ($productos as &$producto) {
        $producto['stock_disponible'] =
            (float) $producto['existencias_totales']
            - (float) $producto['existencias_bloqueadas'];
    }

    unset($producto);

    return view('vendedor/panel_principal', [
        'productos' => $productos
    ]);
}

    public function buscar_producto()
    {
        $model = new ProductoModel(); 
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
        $model = new ProductoModel();

        $data = [
            'productos' => $model->orderBy('id', 'DESC')->paginate(10),
            'pager'     => $model->pager
        ];

        return view('pantalla_productos', $data);
    }

    // ==========================
    // 6. REPARTIDORES
    // ==========================
    public function mostrar_repartidores() 
    {
        $model = new RepartidorModel(); 

        // Paginación
        $repartidores = $model->paginate(6); 
        $paginaActual = $model->pager->getCurrentPage();
        $totalPaginas = $model->pager->getPageCount();
        $baseUrl      = base_url('pantalla_repartidores') . '?page=';

        $pedidosRaw = $model->getPedidosPorRepartidor();

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
            'zonas'                   => $model->getZonasDisponibles(),
        ]);
    } 

public function mostrar_vendedores()
{
    $db = \Config\Database::connect();

    $vendedores = $db->table('usuarios')
        ->select('id, nombre, usuario, correo, activo, debe_cambiar_password, created_at')
        ->where('rol', 'vendedor')
        ->orderBy('id', 'DESC')
        ->get()
        ->getResultArray();

    return view('pantalla_vendedores', [
        'vendedores' => $vendedores
    ]);
}

public function guardarvendedor()
{
    $db = \Config\Database::connect();

    $nombre = trim((string) $this->request->getPost('nombre'));
    $correo = strtolower(trim((string) $this->request->getPost('correo')));

    if ($nombre === '') {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'El nombre es obligatorio.'
        ]);
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Escribe un correo válido.'
        ]);
    }

    if ($db->table('usuarios')->where('correo', $correo)->countAllResults() > 0) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Ese correo ya está registrado.'
        ]);
    }

    $ultimo = $db->table('usuarios')
        ->selectMax('id')
        ->get()
        ->getRowArray();

    $siguiente = ((int) ($ultimo['id'] ?? 0)) + 1;
    $usuario = 'ven_' . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);

    while ($db->table('usuarios')->where('usuario', $usuario)->countAllResults() > 0) {
        $siguiente++;
        $usuario = 'ven_' . str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    $passwordTemporal = 'Ve-' . strtoupper(bin2hex(random_bytes(4)));

    $insertado = $db->table('usuarios')->insert([
        'nombre' => $nombre,
        'usuario' => $usuario,
        'correo' => $correo,
        'password' => password_hash($passwordTemporal, PASSWORD_DEFAULT),
        'rol' => 'vendedor',
        'debe_cambiar_password' => 1,
        'activo' => 1,
        'id_repartidor' => null
    ]);

    if (!$insertado) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'No se pudo crear la cuenta del vendedor.'
        ]);
    }

    \App\Services\AuditoriaService::registrar(
        'vendedor.crear', 'usuario', $usuario,
        'Vendedor nuevo: ' . $nombre . ' (' . $usuario . ')'
    );

    return $this->response->setJSON([
        'success' => true,
        'credenciales' => [
            'usuario' => $usuario,
            'correo' => $correo,
            'password' => $passwordTemporal
        ]
    ]);
}


    public function guardarrepartidor()
    {
        $model = new RepartidorModel();
        $db = \Config\Database::connect();

        // Manejo de foto
        $foto = $this->request->getFile('foto');
        $nombreFoto = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $nombreFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/repartidores/', $nombreFoto);
        }

        $direccion = $this->formarDireccionRepartidor();
        $data = [
            'nombre'       => trim((string) $this->request->getPost('nombre')),
            'ap_p'         => trim((string) $this->request->getPost('ap_p')),
            'ap_m'         => trim((string) $this->request->getPost('ap_m')),
            'tel'          => trim((string) $this->request->getPost('tel')),
            'direccion'    => $direccion,
            'notas'        => trim((string) $this->request->getPost('notas')),
            'zona_base_id' => $model->zonaValidaOrNull($this->request->getPost('zona_base_id')),
        ];

        if ($nombreFoto) {
            $data['foto'] = $nombreFoto;
        }

        if (!$model->insert($data)) {
            return $this->response->setJSON(['success' => false, 'error' => $model->errors()]);
        }

        $idRepartidor = (int) $model->getInsertID();
        $credenciales = null;

        if ($this->request->getPost('crear_acceso') === '1') {
            $correo = strtolower(trim((string) $this->request->getPost('correo')));
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $model->delete($idRepartidor);
                return $this->response->setJSON(['success' => false, 'error' => 'Escribe un correo válido para crear el acceso.']);
            }

            if ($db->table('usuarios')->where('correo', $correo)->countAllResults() > 0) {
                $model->delete($idRepartidor);
                return $this->response->setJSON(['success' => false, 'error' => 'Ese correo ya está registrado.']);
            }

            $usuario = 'rep_' . str_pad((string) $idRepartidor, 4, '0', STR_PAD_LEFT);
            $passwordTemporal = 'Rp-' . strtoupper(bin2hex(random_bytes(4)));
            $insertado = $db->table('usuarios')->insert([
                'nombre'               => trim($data['nombre'] . ' ' . $data['ap_p']),
                'usuario'              => $usuario,
                'correo'               => $correo,
                'password'             => password_hash($passwordTemporal, PASSWORD_DEFAULT),
                'rol'                  => 'repartidor',
                'id_repartidor'        => $idRepartidor,
                'debe_cambiar_password'=> 1,
                'activo'               => 1,
            ]);

            if (!$insertado) {
                $model->delete($idRepartidor);
                return $this->response->setJSON(['success' => false, 'error' => 'No se pudo crear la cuenta del repartidor.']);
            }

            $credenciales = [
                'usuario'  => $usuario,
                'correo'   => $correo,
                'password' => $passwordTemporal,
            ];
        }

        \App\Services\AuditoriaService::registrar(
            'repartidor.crear', 'repartidor', $idRepartidor,
            'Repartidor nuevo: ' . trim($data['nombre'] . ' ' . $data['ap_p']) . ($credenciales ? ' (con acceso a la app)' : ''),
            ['tel' => $data['tel'], 'con_acceso' => (bool) $credenciales]
        );

        return $this->response->setJSON([
            'success'      => true,
            'credenciales' => $credenciales,
        ]);
    }

    private function formarDireccionRepartidor(): ?string
    {
        $partes = [
            trim((string) $this->request->getPost('calle')),
            trim((string) $this->request->getPost('numero')) !== '' ? 'No. ' . trim((string) $this->request->getPost('numero')) : '',
            trim((string) $this->request->getPost('colonia')),
            trim((string) $this->request->getPost('municipio')),
            trim((string) $this->request->getPost('estado')),
        ];
        $partes = array_values(array_filter($partes, static fn (string $parte): bool => $parte !== ''));
        if ($partes) {
            return implode(', ', $partes);
        }

        $texto = trim((string) $this->request->getPost('direccion'));
        return $texto !== '' ? $texto : null;
    }

    public function editarrepartidor($id)
    {
        $model = new RepartidorModel();

        $foto = $this->request->getFile('foto');
        $data = [
            'nombre'    => $this->request->getPost('nombre'),
            'ap_p'      => $this->request->getPost('ap_p'),
            'ap_m'      => $this->request->getPost('ap_m'),
            'tel'          => trim((string) $this->request->getPost('tel')),
            'direccion'    => $this->formarDireccionRepartidor(),
            'notas'        => trim((string) $this->request->getPost('notas')),
            'zona_base_id' => $model->zonaValidaOrNull($this->request->getPost('zona_base_id')),
        ];

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $nombreFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/repartidores/', $nombreFoto);
            $data['foto'] = $nombreFoto;
        }

        $antes = $model->find($id);
        if ($model->update($id, $data)) {
            \App\Services\AuditoriaService::registrar(
                'repartidor.editar', 'repartidor', $id,
                'Repartidor editado: ' . trim(($data['nombre'] ?? '') . ' ' . ($data['ap_p'] ?? '')),
                ['antes' => $antes ? array_intersect_key($antes, array_flip(['nombre', 'ap_p', 'ap_m', 'tel', 'direccion', 'zona_base_id'])) : null]
            );
            return $this->response->setJSON(['success' => true]);
        } else {
            return $this->response->setJSON(['success' => false, 'error' => $model->errors()]);
        }
    }

    public function eliminarrepartidor($id)
    {
        $model = new RepartidorModel();

        try {
            $repartidor = $model->find($id);
            if (!$repartidor) {
                return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
            }

            $model->desasignarPedidos($id);

            if ($model->delete($id)) {
                \App\Services\AuditoriaService::registrar(
                    'repartidor.eliminar', 'repartidor', $id,
                    'Repartidor eliminado: ' . trim(($repartidor['nombre'] ?? '') . ' ' . ($repartidor['ap_p'] ?? ''))
                );
                return $this->response->setJSON(['success' => true]);
            } else {
                return $this->response->setJSON(['success' => false, 'error' => $model->errors()]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}