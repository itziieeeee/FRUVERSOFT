<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use App\Models\StatusModel;

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
    // 4. INVENTARIO Y CADUCIDAD
    // ==========================

    // Función interna para procesar mermas por fecha
    private function revisarCaducados()
    {
        $db = \Config\Database::connect();
        $hoy = date('Y-m-d');

        // Buscar productos en 'entrada' que caducaron hoy o antes
        $caducados = $db->table('entrada')
                        ->where('fecha_cad <=', $hoy)
                        ->where('cantidad >', 0)
                        ->get()
                        ->getResultArray();

        foreach ($caducados as $fila) {
            $cantidad_mermar = $fila['cantidad'];

            // A. Registrar en tabla merma
            $db->table('merma')->insert([
                'id_entrada' => $fila['id'],
                'cantidad'   => $cantidad_mermar,
                'motivo'     => 'SISTEMA: CADUCIDAD AUTOMÁTICA (5 DÍAS)',
                'fecha'      => $hoy
            ]);

            // B. Actualizar existencias
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

            // C. Vaciar cantidad de esa entrada para no repetir el proceso
            $db->table('entrada')->where('id', $fila['id'])->update(['cantidad' => 0]);
        }
    }

    public function inventario()
    {
        // Limpiamos antes de mostrar la tabla
        $this->revisarCaducados(); 

        $db = \Config\Database::connect();

        // 1. Lista general de productos
        $productos = $db->table('producto')->get()->getResultArray();

        // 2. Stock consolidado para la tabla principal
        $existencias = $db->table('existencias e')
            ->select('p.nombre, e.e_total, e.e_merma')
            ->join('producto p', 'p.id = e.id_producto')
            ->get()
            ->getResultArray();

        // 3. NUEVA CONSULTA: Productos disponibles para MERMA
        $productos_merma = $db->table('existencias e')
            ->select('p.id as id_p, p.nombre, e.e_total')
            ->join('producto p', 'p.id = e.id_producto')
            ->where('e.e_total >', 0)
            ->get()
            ->getResultArray();

        $data = [
            'productos'       => $productos,       // Se usa en Entrada
            'productos_merma' => $productos_merma, // Se usa en Merma
            'existencias'     => $existencias      // Se usa en la Tabla
        ];

        return view('inventario', $data);
    }



    // ==========================
    // 5. VENDEDOR Y BUSCADOR
    // ==========================
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

    // ==========================
    // 6. OTRAS PANTALLAS
    // ==========================
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

    public function pantalla_repartidores()
    {
        return view('pantalla_repartidores');
    }

    public function pantalla_pedidos()
    {
        $model = new StatusModel();
        $data['sp'] = $model->obtenerPedidos();
        return view('pantalla_pedidos', $data);
    }

    public function pantalla_productos()
    {
        return view('pantalla_productos');
    }
}