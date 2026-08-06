<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use App\Models\StatusModel;
use App\Models\RepartidorModel;
use App\Models\ProductoModel;
use App\Models\ExistenciasModel;

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

    // ==========================
    // 4. INVENTARIO
    // ==========================
    public function inventario()
    {
        $existenciasModel = new ExistenciasModel();
        $productoModel    = new ProductoModel();

        // Limpiamos caducados antes de mostrar la tabla
        $existenciasModel->procesarCaducados();

        $data = [
            'productos'       => $productoModel->findAll(),
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
        $data['productos'] = []; 
        $data['termino'] = "";
        return view('vendedor/panel_principal', $data);
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
        ]);
    } 

    public function guardarrepartidor()
    {
        $model = new RepartidorModel();

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
        $model = new RepartidorModel();

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
        $model = new RepartidorModel();

        try {
            $repartidor = $model->find($id);
            if (!$repartidor) {
                return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
            }

            $model->desasignarPedidos($id);

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