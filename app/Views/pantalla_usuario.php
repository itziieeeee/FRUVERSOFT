<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?=('usuario_estilo.css')?>">
    <title>FRUVER - Iniciar Sesión</title>
    
</head>
<body>
    <div class="contenedor">
        <div class="form">
            <div class="logo">
                <img src="<?= base_url('img/LOGO1.png') ?>">
            </div>

            <h1>FRUVER</h1>
            
            
           <form action="<?= base_url('/validar') ?>" method="POST">
                <div class="labels">
                    <label>
                        <i class="fas fa-user-circle"></i> Usuario
                    </label>

                    <div class="inputs">
                        <i class="fas fa-user"></i>
                        <input type="text" id="usuario" name="nusuario" placeholder="Ingresa tu usuario" required>
                    </div>
                </div>
                
                <div class="labels">
                    <label>
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="inputs">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                    </div>
                </div>
                <!--
                <div class="opciones">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Recordar acceso
                    </label>
                    <a href="forgot-password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>-->
                
                <button type="submit" class="botoniniciar">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
           
        </div>
    </div>

    
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePassword(){
            const passwordInput = document.getElementById('contrasena');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>