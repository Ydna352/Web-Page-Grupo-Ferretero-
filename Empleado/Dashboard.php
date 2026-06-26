<?php require_once '../PHP/auth.php'; auth_require_rol(['admin', 'empleado']); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Empleado</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../Administrador/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../Administrador/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../Administrador/favicon-16x16.png">
    <link rel="manifest" href="../Administrador/site.webmanifest">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        *::before,
        *::after {
            box-sizing: border-box;
        }

        :root {
            --naranja: #f97316;
            --azul: #3e8cdd;
            --verde: #74cb3c;
            --muted: #6b7280;
            --fondo: #fafaf8;
            --sombra: 0 4px 24px rgba(0, 0, 0, 0.07);
            --sombra-hover: 0 12px 36px rgba(0, 0, 0, 0.13);
            --radius: 0.625rem;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 0.9rem;
            background: var(--fondo);
            color: #1a1a2e;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 36px;
        }

        .page-title {
            font-size: 2.2rem;
            color: var(--naranja);
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .page-subtitle {
            color: var(--muted);
            margin-top: 6px;
            font-size: 1rem;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--sombra);
            border: 2px solid transparent;
            padding: 24px;
            transition: box-shadow 0.25s, transform 0.25s;
            animation: fadeUp 0.5s both;
        }

        .card:hover {
            box-shadow: var(--sombra-hover);
            transform: translateY(-4px);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .card-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--muted);
        }

        .card-icon svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card-value {
            font-size: 1.7rem;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 0.82rem;
            color: var(--muted);
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 560px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- sidebar.js envuelve todo esto automáticamente -->
    <div class="page-header">
        <h1 class="page-title" id="page-title">Bienvenido, Empleado</h1>
        <p class="page-subtitle">Panel principal del empleado</p>
    </div>
    <div class="cards-grid" id="cards-grid"></div>

    <script>
        // ── Cargar datos reales desde la sesión PHP ───────────────────────────
        var SALARIOS = {
            'Administrador': 90000,
            'Supervisor': 12000,
            'Contador': 8800,
            'Repartidor': 6800,
            'Almacenista': 6400,
            'Cajero': 7000,
            'Vendedor': 6000
        };

        function fmtMXN(n) {
            return '$' + parseFloat(n).toLocaleString('es-MX', {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            }) + ' MXN';
        }

        function renderDashboard(user) {
            document.getElementById('page-title').textContent = 'Bienvenido, ' + user.nombre;

            var salario = SALARIOS[user.puesto] || 0;

            var tarjetas = [
                {
                    label: 'Salario Mensual',
                    color: '#f97316',
                    icono: '<svg viewBox="0 0 24 24" stroke="#f97316"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                    contenido: '<div class="card-value" style="color:#f97316">' + fmtMXN(salario) + '</div>'
                },
                {
                    label: 'Puesto',
                    color: '#3e8cdd',
                    icono: '<svg viewBox="0 0 24 24" stroke="#3e8cdd"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
                    contenido: '<div class="card-value" style="color:#3e8cdd">' + (user.puesto || 'Empleado') + '</div>'
                },
                {
                    label: 'Información Personal',
                    color: '#74cb3c',
                    icono: '<svg viewBox="0 0 24 24" stroke="#74cb3c"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                    contenido:
                        '<div class="info-row"><span class="info-label">Nombre:</span><span class="info-value">' + user.nombre + '</span></div>' +
                        '<div class="info-row"><span class="info-label">Correo:</span><span class="info-value">' + user.email + '</span></div>' +
                        (user.telefono ? '<div class="info-row"><span class="info-label">Teléfono:</span><span class="info-value">' + user.telefono + '</span></div>' : '')
                },

                {
                    label: 'Registrar Venta',
                    color: '#3e8cdd',
                    icono: '<svg viewBox="0 0 24 24" stroke="#3e8cdd" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
                    contenido: '<div style="margin-top:8px"><a href="../Empleado/RegisterSale.php" style="display:inline-block;padding:10px 20px;background:#3e8cdd;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">Ir a Registrar Venta →</a></div>'
                },
                {
                    label: 'Registrar Cliente',
                    color: '#74cb3c',
                    icono: '<svg viewBox="0 0 24 24" stroke="#74cb3c" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
                    contenido: '<div style="margin-top:8px"><a href="../Empleado/RegisterClient.php" style="display:inline-block;padding:10px 20px;background:#74cb3c;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">Ir a Registrar Cliente →</a></div>'
                }
            ];

            var grid = document.getElementById('cards-grid');
            grid.innerHTML = '';
            for (var i = 0; i < tarjetas.length; i++) {
                var t = tarjetas[i];
                var card = document.createElement('div');
                card.className = 'card';
                card.style.borderColor = t.color;
                card.style.animationDelay = (i * 100) + 'ms';
                card.innerHTML =
                    '<div class="card-header">' +
                    '<span class="card-label">' + t.label + '</span>' +
                    '<div class="card-icon">' + t.icono + '</div>' +
                    '</div>' +
                    '<div class="card-content">' + t.contenido + '</div>';
                grid.appendChild(card);
            }
        }

        // Obtener sesión desde PHP
        fetch('../PHP/sesion.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.logueado && (data.rol === 'empleado' || data.rol === 'admin')) {
                    renderDashboard(data);
                } else if (!data.logueado) {
                    // Sin sesión: redirigir al login
                    window.location.href = '../Home/UnifiedLogin.php?error=' +
                        encodeURIComponent('Debes iniciar sesión para continuar.');
                } else {
                    // Es cliente u otro rol — mostrar datos genéricos
                    renderDashboard({ nombre: 'Empleado', puesto: 'Empleado', email: '', telefono: '' });
                }
            })
            .catch(function () {
                // Error de red — mostrar datos de respaldo
                renderDashboard({ nombre: 'Empleado', puesto: 'Empleado', email: '', telefono: '' });
            });
    </script>

    <!-- ✅ Sidebar compartido — debe ir al final del body -->
    <script src="sidebar.js"></script>
</body>

</html>