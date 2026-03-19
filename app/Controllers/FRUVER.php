<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;

class FRUVER extends BaseController
{
    // 1. Pantalla principal (Bienvenida)
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
            'apellido1'       => $this->request->getPost('apellido1'),
            'apellido2'       => $this->request->getPost('apellido2'),
            'contrasena'      => password_hash($this->request->getPost('contrasena'), PASSWORD_DEFAULT),
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('menusolo'));
        }
    }

    // 3. Sección Login
    public function usuario()
    {
        return view('pantalla_usuario');
    }

    public function validar()
    {
        // En lugar de cargar la vista directo, redireccionamos a la selección de rol
        return redirect()->to(base_url('seleccionar_rol'));
    
    }

    public function menusolo()
    {
        return view('menusolo');
    }

    // 4. Sección Clientes (Unificada)
    public function pantalla_clientes()
    {
        return view('pantalla_clientes');
    }

    public function nuevo_cliente()
    {
        // Esta función ahora está una sola vez y cargará la vista correctamente
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

    // 5. Cerrar Sesión
    public function pantalla_inicio()
    {
        return view('pantalla_inicio');
    }

    // Sección Inventario
public function inventario()
{
    // Carga la vista inventario.php que ya tienes en tu carpeta Views
    return view('inventario');

}
// --- SECCIÓN VENDEDOR ---

//  función abre la pantalla limpia del buscador
public function pantalla_vendedor()
{
   
    $data['productos'] = []; 
    $data['termino'] = "";
    return view('vendedor/panel_principal', $data);
}

//  función es la que activa la lupa
public function buscar_producto()
{
    
    $model = new \App\Models\ProductoModel(); 

  
    $termino = $this->request->getGet('query'); 

   
    $data['productos'] = $model->like('nombre', $termino)->findAll();
    $data['termino']   = $termino;

   
    return view('vendedor/panel_p', $data);
}
//seleccionamos rol
public function seleccionar_rol()
 {
    return view('seleccionar_rol');
}

public function pantalla_administrador()
{
    return view('pantalla_administrador');
}
public function productos()
{
    return view('productos');
}
}