<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="inicio_estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

</head>
<h1>HOLAAAAAA AMISTADDD</h1>
<body>
<style>
     .cont{
            height: 100vh;
            width: 100%;
            background-image: linear-gradient(145deg, rgba(0,30,10,0.5) 0%, rgba(0,0,0,0.4) 100%), url("<?= base_url('IMG/inicio.png') ?>");
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Efecto parallax suave */
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

</style>
    <!-- Barra de navegacion -->
    <nav class="navbar" id="navbar">
        <div class="logo">
            <img src="<?= base_url('img/LOGO1.png') ?>"  width="120">
        </div>
        <ul>
            <li><a href="<?= base_url('registro') ?>"><i class="fas fa-leaf" style="margin-right: 6px;"></i>Registrarse</a></li>
        </ul>
    </nav>

    <!-- Contenedor o seccion donde esta la imagen -->
    <section class="cont">
        <div class="titulo">
        <h1>Bienvenid(a)</h1>
        <p class="subtitulo">Del huerto a tu mesa</p>
            
        <!-- Boton que nos lleva la seccion de acceder-->
        <a href="<?= base_url('menusolo') ?>" class="btn-ingresar">
        <i class="fas fa-sign-in-alt"></i> INGRESAR</a>
            
        <p style="margin-top: 30px; font-size: 0.9rem; opacity: 0.7;">
        <i class="fas fa-seedling"></i> Productos frescos y naturales</p>
        </div>
    </section>

    <!-- Java ) -->
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>