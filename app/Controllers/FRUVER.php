<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;

class FRUVER extends BaseController
{
    // 1. Pantalla principal (Bienvenido)
    public function index(): string
    {
        return view('Pantalla_inicio');
    }

    // 2. Sección Usuarios (Registro de Admin)
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
            'contrasena'      => password_hash($this->request->getPost('contrasena'), PASSWORD_DEFAULT),
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('pantalla_administrador'));
        }
    }

    // 3. Sección Login
    public function usuario()
    {
        return view('pantalla_usuario');
    }

    public function validar()
    {
        // Nota: Aquí después agregaremos la lógica real de checar usuario/password
        return view('pantalla_administrador');
    }

    // 4. Sección Clientes (Unificada)
    public function pantalla_clientes()
    {
        $modelo = new ClienteModel();
        // Obtener todos los clientes de la base de datos
        $data['clientes'] = $modelo->findAll();
        
        // Esta es la vista general de la tabla de clientes, pasamos los datos
        return view('pantalla_clientes', $data);
    }

    public function nuevo_cliente()
    {
        // Esta es la vista del formulario (alta_cliente)
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

    public function get_cliente_detalle()
    {
        $id_cliente = $this->request->getPost('id_cliente');
        
        $modeloCliente = new ClienteModel();
        
        // 1. Obtener datos basicos del cliente
        $cliente = $modeloCliente->find($id_cliente);
        
        if (!$cliente) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Cliente no encontrado']);
        }

        // 2. Obtener historial (Ejemplo base para la tabla "pedido" y "detalle_pedido")
        // Como no tenemos el App\Models\PedidoModel creado, podemos usar el Query Builder nativo:
        $db = \Config\Database::connect();
        
        $pedidos = $db->table('pedido')
                      ->select('id_pedido, fecha_apertura as fecha, total, estatus')
                      ->where('id_cliente', $id_cliente)
                      ->orderBy('fecha_apertura', 'DESC')
                      ->limit(10) // Mostrar últimos 10 pedidos
                      ->get()->getResultArray();
                      
        // Enviar respuesta en formato JSON para que JavaScript la use
        return $this->response->setJSON([
            'status' => 'success',
            'cliente' => $cliente,
            'pedidos' => $pedidos
        ]);
    }

    // 5. Cerrar Sesión
    public function salir()
    {
        return redirect()->to(base_url('/'));
    }
}