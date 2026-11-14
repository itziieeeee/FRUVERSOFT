<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CrearAdmin extends BaseCommand
{
    protected $group       = 'Usuarios';
    protected $name        = 'admin:crear';
    protected $description = 'Crea una cuenta administrativa inicial de forma controlada.';
    protected $usage       = 'admin:crear <correo> <nombre> <password>';
    protected $arguments   = [
        'correo'   => 'Correo del administrador.',
        'nombre'   => 'Nombre que aparecerá en la sesión.',
        'password' => 'Contraseña inicial de mínimo 8 caracteres.',
    ];

    public function run(array $params)
    {
        if (count($params) < 3) {
            CLI::error('Uso: php spark admin:crear correo@ejemplo.com "Nombre Admin" "ContraseñaSegura"');
            return EXIT_ERROR;
        }

        [$correo, $nombre, $password] = array_map('trim', array_slice($params, 0, 3));
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            CLI::error('El correo no es válido.');
            return EXIT_ERROR;
        }
        if ($nombre === '' || strlen($password) < 8) {
            CLI::error('El nombre es obligatorio y la contraseña debe tener al menos 8 caracteres.');
            return EXIT_ERROR;
        }

        $db = \Config\Database::connect();
        if ($db->table('usuarios')->where('correo', $correo)->countAllResults() > 0) {
            CLI::error('Ese correo ya está registrado.');
            return EXIT_ERROR;
        }

        $ok = $db->table('usuarios')->insert([
            'nombre'                 => $nombre,
            'usuario'                => null,
            'correo'                 => $correo,
            'password'               => password_hash($password, PASSWORD_DEFAULT),
            'rol'                    => 'admin',
            'id_repartidor'         => null,
            'debe_cambiar_password' => 0,
            'activo'                => 1,
        ]);

        if (!$ok) {
            CLI::error('No se pudo crear el administrador.');
            return EXIT_ERROR;
        }

        CLI::write('Administrador creado correctamente.', 'green');
        CLI::write('Correo: ' . $correo);
        CLI::write('No se muestra ni se guarda la contraseña en texto plano.', 'yellow');
        return EXIT_SUCCESS;
    }
}
