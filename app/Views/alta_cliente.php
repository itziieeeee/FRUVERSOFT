<!DOCTYPE html>
<html lang="es">
<head>
    <!--ESTE ES EL FORMULARIO PARA DAR DE ALTA AL CLIENTE-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Registro de Cliente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Roboto, system-ui, sans-serif;
        }

        body {
            background: #eef3e9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* contenedor elegante con card */
        .registro-wrapper {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
        }

        .registro-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 40px -8px rgba(30, 60, 30, 0.3);
            overflow: hidden;
            transition: all 0.2s;
            border: 1px solid rgba(100, 140, 100, 0.2);
        }

        /* header con gradiente sutil */
        .card-header {
            background: linear-gradient(135deg, #1d4a27 0%, #2a6230 100%);
            color: white;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }
        .card-header::after {
            content: "🍍🍊";
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 2.2rem;
            opacity: 0.12;
            transform: rotate(-5deg);
            pointer-events: none;
        }
        .card-header i {
            font-size: 2rem;
            background: rgba(255,255,255,0.2);
            padding: 12px;
            border-radius: 24px;
            backdrop-filter: blur(4px);
        }
        .card-header h2 {
            font-weight: 600;
            font-size: 1.6rem;
            letter-spacing: -0.3px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* cuerpo del formulario */
        .form-body {
            padding: 36px 38px;
        }

        /* grid para campos */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 30px;
        }

        .campo {
            margin-bottom: 8px;
        }

        .campo.full-width {
            grid-column: span 2;
        }

        .campo label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #2a5e2f;
            margin-bottom: 8px;
        }
        .campo label i {
            color: #f16b1a;
            font-size: 1rem;
            width: 20px;
        }

        .campo input, .campo select {
            width: 100%;
            padding: 14px 18px;
            background: #f9fdf8;
            border: 2px solid #d5e8d1;
            border-radius: 20px;
            font-size: 0.95rem;
            transition: 0.2s ease;
            outline: none;
            color: #1d3a1d;
            font-weight: 500;
        }

        .campo input:focus, .campo select:focus {
            border-color: #f16b1a;
            background: white;
            box-shadow: 0 0 0 4px rgba(241, 107, 26, 0.15);
        }

        .campo input::placeholder {
            color: #b0c6aa;
            font-weight: 400;
            opacity: 0.8;
        }

        /* campo de RFC con icono de ayuda */
        .rfc-wrapper {
            position: relative;
        }
        .rfc-wrapper input {
            padding-right: 45px;
            text-transform: uppercase;
        }
        .rfc-wrapper i {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #f16b1a;
            font-size: 1.2rem;
            cursor: help;
            transition: 0.2s;
        }
        .rfc-wrapper i:hover {
            color: #1d4a27;
        }

        /* tip selector bonito (en lugar de <select> podemos usar chips, pero mantenemos select mejorado) */
        .select-mejorado {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%231d4a27" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>');
            background-repeat: no-repeat;
            background-position: right 18px center;
            background-size: 16px;
        }

        /* opción de tipo cliente con tarjetas en lugar de select (para más estilo) */
        .tipo-cliente-opciones {
            display: flex;
            gap: 16px;
            margin-top: 6px;
        }
        .tipo-opcion {
            flex: 1;
            background: #f0f7ee;
            border: 2px solid #cde0ca;
            border-radius: 40px;
            padding: 16px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-weight: 600;
            color: #1d4a27;
            cursor: pointer;
            transition: 0.15s;
        }
        .tipo-opcion i {
            font-size: 1.3rem;
            color: #f16b1a;
        }
        .tipo-opcion.seleccionado {
            background: #1d4a27;
            border-color: #1d4a27;
            color: white;
        }
        .tipo-opcion.seleccionado i {
            color: white;
        }
        /* radios ocultos */
        .radio-oculto {
            display: none;
        }

        /* badge de crédito informativo (opcional) */
        .info-credito {
            background: #fef6e8;
            border-radius: 24px;
            padding: 12px 18px;
            margin: 20px 0 0;
            border: 1px solid #fadcb9;
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 0.9rem;
            color: #61421c;
        }
        .info-credito i {
            font-size: 1.6rem;
            color: #f16b1a;
        }

        /* acciones botones */
        .acciones {
            margin-top: 36px;
            display: flex;
            gap: 18px;
            justify-content: flex-end;
            align-items: center;
            border-top: 2px dashed #d0e2cb;
            padding-top: 28px;
        }
        .btn {
            padding: 14px 34px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
        }
        .btn-cancelar {
            background: white;
            border: 2px solid #c5dbc0;
            color: #2e5a33;
        }
        .btn-cancelar:hover {
            background: #ebf5e8;
            border-color: #9fc097;
        }
        .btn-registrar {
            background: #f16b1a;
            color: white;
            box-shadow: 0 8px 18px rgba(241, 107, 26, 0.35);
        }
        .btn-registrar:hover {
            background: #d95c0d;
            transform: scale(1.02);
            box-shadow: 0 12px 24px rgba(241, 107, 26, 0.45);
        }

        /* pequeño detalle de ejemplo RFC */
        .rfc-hint {
            font-size: 0.75rem;
            color: #658b6b;
            margin-top: 6px;
            margin-left: 12px;
        }

        /* responsive */
        @media (max-width: 600px) {
            .form-body { padding: 24px 20px; }
            .grid-2 { grid-template-columns: 1fr; }
            .campo.full-width { grid-column: span 1; }
            .tipo-cliente-opciones { flex-direction: column; }
            .acciones { flex-direction: column-reverse; }
            .btn { width: 100%; }
        }

        /* animación leve */
        .registro-card {
            animation: fadeUp 0.3s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0.7; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="registro-wrapper">
        <div class="registro-card">
            <!-- header más fresco y acorde a FRUVER -->
            <div class="card-header">
                <i class="fas fa-apple-alt"></i>
                <h2>Registro de Nuevo Cliente</h2>
            </div>

            <!-- formulario con method POST (action dinámico) -->
            <form action="<?= base_url('alta_clientes/guardar') ?>" method="post" class="form-body">
                <!-- grid 2 columnas para aprovechar espacio -->
                <div class="grid-2">
                    <!-- Nombre(s) ocupa las dos columnas (diseño limpio) -->
                    <div class="campo full-width">
                        <label><i class="fas fa-user"></i> Nombre(s) *</label>
                        <input type="text" name="nombre" placeholder="Ej. Juan Carlos" required>
                    </div>

                    <!-- Apellido Paterno -->
                    <div class="campo">
                        <label><i class="fas fa-user-tag"></i> Apellido Paterno *</label>
                        <input type="text" name="apellido_paterno" placeholder="Hernández" required>
                    </div>

                    <!-- Apellido Materno -->
                    <div class="campo">
                        <label><i class="fas fa-user-tag"></i> Apellido Materno *</label>
                        <input type="text" name="apellido_materno" placeholder="García" >
                    </div>

                    <!-- RFC con ícono de ayuda (max 13, mayúsculas) -->
                    <div class="campo">
                        <label><i class="fas fa-qrcode"></i> RFC</label>
                        <div class="rfc-wrapper">
                            <input type="text" name="rfc" maxlength="13" placeholder="ABCD123456XYZ" style="text-transform:uppercase;" value="HEGL960101">
                            <i class="fas fa-circle-question" title="RFC con homoclave (13 caracteres)"></i>
                        </div>
                        <div class="rfc-hint">
                            <i class="fas fa-lightbulb" style="margin-right: 4px;"></i> Ej: AEXD890123HDF
                        </div>
                    </div>

                    <!-- Tipo de Cliente con selector visual (dos tarjetas) -->
                    <div class="campo">
                        <label><i class="fas fa-store"></i> Tipo de Cliente *</label>
                        <!-- radion invisibles -->
                        <input type="radio" name="tipo_cliente" id="tipoMayoreo" class="radio-oculto" value="Mayoreo" checked>
                        <input type="radio" name="tipo_cliente" id="tipoMenudeo" class="radio-oculto" value="Menudeo">
                        
                        <div class="tipo-cliente-opciones">
                            <label for="tipoMayoreo" class="tipo-opcion seleccionado" id="labelMayoreo">
                                <i class="fas fa-credit-card"></i> Mayoreo (Crédito)
                            </label>
                            <label for="tipoMenudeo" class="tipo-opcion" id="labelMenudeo">
                                <i class="fas fa-cash-register"></i> Menudeo (Contado)
                            </label>
                        </div>
                    </div>

                    <!-- adicional: campo para teléfono / email (no estaba en tu base, lo añadimos sutil) -->
                    <div class="campo">
                        <label><i class="fas fa-phone-alt"></i> Teléfono</label>
                        <input type="tel" name="telefono" placeholder="55 1234 5678">
                    </div>
                    <div class="campo">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="cliente@ejemplo.com">
                    </div>

                    <!-- mensaje opcional de crédito (si es mayoreo, feedback) -->
                    
                </div> <!-- fin grid -->

                <!-- Info contextual con estilo (fresco) 
                <div class="info-credito" id="creditoHint">
                    <i class="fas fa-chart-line"></i>
                    <span>📌 <strong>Cliente Mayoreo</strong> — podrá acceder a línea de crédito sujeta a aprobación. Límite sugerido: $50,000 MXN</span>
                </div>
-->
                <!-- Botones Cancelar / Registrar -->
                <div class="acciones">
                    <a href="<?= base_url('pantalla_clientes') ?>" class="btn btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
                    <button type="submit" class="btn btn-registrar"><i class="fas fa-check-circle"></i> Registrar Cliente</button>
                </div>

                <!-- campo oculto para seguridad (opcional, podrías agregar token) -->
                <!-- <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>"> -->
            </form>
        </div>
    </div>

    <!-- pequeño script para mantener la sincronización de los chips de tipo cliente y actualizar el mensaje -->
    <script>
        (function() {
            const radioMayoreo = document.getElementById('tipoMayoreo');
            const radioMenudeo = document.getElementById('tipoMenudeo');
            const labelMayoreo = document.getElementById('labelMayoreo');
            const labelMenudeo = document.getElementById('labelMenudeo');
            const creditoHint = document.getElementById('creditoHint');

            function actualizarSeleccion() {
                if (radioMayoreo.checked) {
                    labelMayoreo.classList.add('seleccionado');
                    labelMenudeo.classList.remove('seleccionado');
                    creditoHint.innerHTML = '<i class="fas fa-chart-line"></i> <span>📌 <strong>Cliente Mayoreo</strong> — podrá acceder a línea de crédito sujeta a aprobación. Límite sugerido: $50,000 MXN</span>';
                } else {
                    labelMenudeo.classList.add('seleccionado');
                    labelMayoreo.classList.remove('seleccionado');
                    creditoHint.innerHTML = '<i class="fas fa-shopping-basket"></i> <span>🛒 <strong>Cliente Menudeo</strong> — pago de contado. Ideal para ventas directas.</span>';
                }
            }

            // Eventos
            radioMayoreo.addEventListener('change', actualizarSeleccion);
            radioMenudeo.addEventListener('change', actualizarSeleccion);

            // también permitir clic en labels (por si acaso el for ya funciona, pero aseguramos)
            labelMayoreo.addEventListener('click', function(e) {
                radioMayoreo.checked = true;
                actualizarSeleccion();
            });
            labelMenudeo.addEventListener('click', function(e) {
                radioMenudeo.checked = true;
                actualizarSeleccion();
            });

            // inicializar mensaje
            actualizarSeleccion();

            // Para el RFC: forzar mayúsculas mientras escriben
            const rfcInput = document.querySelector('input[name="rfc"]');
            if (rfcInput) {
                rfcInput.addEventListener('input', function(e) {
                    this.value = this.value.toUpperCase();
                });
            }
        })();
    </script>

    <!-- Si se quiere un efecto de validación simple en envío (opcional) -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            // solo un ejemplo didáctico, no interfiere con envío real
            const nombre = document.querySelector('input[name="nombre"]').value.trim();
            if (nombre === '') {
                e.preventDefault();
                alert('👤 Por favor ingresa el nombre del cliente');
                return;
            }
            // podrías agregar más validaciones, pero no bloqueamos
        });
    </script>
</body>
</html>