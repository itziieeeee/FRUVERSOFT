<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>FRUVER INICIO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('CSS/inicio.css') ?>">
</head>
<body>

    <!-- Navbar blanca con logo a la izquierda y botones a la derecha -->
    <nav class="navbar" id="navbar">
        <div class="logo">
            <img src="<?= base_url('IMG/LOGO1.png') ?>" alt="FRUVER">
        </div>
        <div class="nav-buttons">
            <a href="<?= base_url('registro') ?>" class="btn-registro"><i class="fas fa-leaf"></i> Registrarse</a>
            <a href="<?= base_url('admin') ?>" class="btn-admin"><i class="fas fa-user-shield"></i> Admin</a>
        </div>
    </nav>

    <!-- Carrusel fullscreen -->
    <div class="carrusel">
        <div class="carruselcontenedor">
            <!-- Slide 1  Principal FRUVER -->
            <div class="carousel-slide active" data-index="0">
                <div class="slide-bg" style="background-image: url('https://images.pexels.com/photos/616401/pexels-photo-616401.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <h1>FRUVER</h1>
                    <p>Del huerto a tu mesa</p>
                    <a href="<?= base_url('menusolo') ?>" class="btn-ingresar">
                        <i class="fas fa-sign-in-alt"></i> INGRESAR
                    </a>
                </div>
            </div>

     <!-- Slide 2 - Envíos -->
<div class="carousel-slide" data-index="1">
<div class="slide-bg" style="background-image: url('https://images.pexels.com/photos/1132047/pexels-photo-1132047.jpeg?auto=compress&cs=tinysrgb&w=1600');"></div>
    <div class="slide-overlay"></div>
    <div class="slide-content">
        <h1>Garantía de frescura y calidad en cada envío</h1>
        <a href="<?= base_url('') ?>" class="btn-ingresar">
            <i class="fa-solid fa-store"></i> SUCURSALES
        </a>
    </div>
</div>

        <!-- Botones de navegación -->
        <div class="carousel-btn btn-prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="carousel-btn btn-next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </div>

        <!-- Indicadores -->
        <div class="carousel-dots" id="carouselDots"></div>

        <!-- Badge de frescura flotante -->
        <div class="frescura-badge">
            <span><i class="fas fa-apple-alt"></i> 100% Natural</span>
            <span><i class="fas fa-truck-fast"></i> Entrega directa</span>
        </div>

        <div class="wave-bottom">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path fill="#0a2f1f" fill-opacity="0.4" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,53.3C672,53,768,75,864,85.3C960,96,1056,96,1152,85.3C1248,75,1344,53,1392,42.7L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </div>

    <script>
        // === Carrusel Automático ===
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;
        let autoPlayInterval;
        let isPlaying = true;

        // Crear dots dinámicamente
        function createDots() {
            const dotsContainer = document.getElementById('carouselDots');
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (i === currentSlide) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(i));
                dotsContainer.appendChild(dot);
            }
        }

        function updateDots() {
            const dots = document.querySelectorAll('.dot');
            dots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function goToSlide(index) {
            slides[currentSlide].classList.remove('active');
            currentSlide = (index + totalSlides) % totalSlides;
            slides[currentSlide].classList.add('active');
            updateDots();
            resetAutoPlay();
        }

        function changeSlide(direction) {
            goToSlide(currentSlide + direction);
        }

        function nextSlide() {
            if (isPlaying) goToSlide(currentSlide + 1);
        }

        function resetAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
            }
            autoPlayInterval = setInterval(nextSlide, 5000);
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                isPlaying = false;
            }
        }

        function startAutoPlay() {
            if (!isPlaying) {
                isPlaying = true;
                resetAutoPlay();
            }
        }

        // Inicializar carrusel
        function initCarousel() {
            createDots();
            resetAutoPlay();
            
            const carousel = document.querySelector('.carrusel');
            if (carousel) {
                carousel.addEventListener('mouseenter', stopAutoPlay);
                carousel.addEventListener('mouseleave', startAutoPlay);
            }
        }

        // Exponer funciones globalmente
        window.changeSlide = changeSlide;

        // === Navbar Scroll Effect ===
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Inicializar todo cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            initCarousel();
        });
    </script>
</body>
</html>