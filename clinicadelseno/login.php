<?php
session_start();
require 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = connection();
    $numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
    $rol_form = mysqli_real_escape_string($con, $_POST['rol']);

    // Buscar usuario por número de cédula
    $sql = "SELECT * FROM users WHERE numero_cedula = '$numero_cedula'";
    $query = mysqli_query($con, $sql);

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        if ($user['estado'] !== 'Activo') {
            $error = "Usuario inactivo, contacte al administrador.";
        } elseif ($user['role'] !== $rol_form) {
            $error = "El rol seleccionado no corresponde al usuario.";
        } else {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombres_completos'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['numero_cedula'] = $user['numero_cedula'];

            // Redirección según rol
            if ($user['role'] == 'Administrador') {
                header("Location: index.php");
            } elseif ($user['role'] == 'Jefe inmediato') {
                header("Location: ver_solicitudes_jefe.php");
            } else {
                header("Location: solicitud.php");
            }
            exit();
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Solicitudes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background circles */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        body::before {
            width: 400px;
            height: 400px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        body::after {
            width: 300px;
            height: 300px;
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(50px, 50px) scale(1.1);
            }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ec407a 0%, #e91e63 100%);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(233, 30, 99, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin-bottom: 8px;
        }

        .login-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            color: #2d3748;
            background: white;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #ec407a;
            box-shadow: 0 0 0 4px rgba(236, 64, 122, 0.1);
        }

        .form-input:focus + .input-icon,
        .form-select:focus ~ .input-icon {
            color: #ec407a;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.4);
            margin-top: 8px;
            position: relative;
        }

        .btn-login:hover:not(.loading) {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(220, 38, 38, 0.5);
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #ec407a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #d81b60;
            text-decoration: underline;
        }

        .divider {
            position: relative;
            text-align: center;
            margin: 32px 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e2e8f0;
        }

        .divider-text {
            position: relative;
            display: inline-block;
            padding: 0 16px;
            background: rgba(255, 255, 255, 0.95);
            color: #94a3b8;
            font-size: 13px;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            color: #64748b;
            font-size: 13px;
        }

        .footer-text a {
            color: #ec407a;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #fee2e2;
            border: 2px solid #fca5a5;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            animation: slideIn 0.4s ease-out;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }

            .login-title {
                font-size: 24px;
            }

            .logo {
                width: 70px;
                height: 70px;
                font-size: 32px;
            }
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <div class="logo">📋</div>
                <h1 class="login-title">Iniciar Sesión</h1>
                <p class="login-subtitle">Bienvenido al Sistema de Solicitudes</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Número de cédula</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input 
                            type="text" 
                            class="form-input" 
                            placeholder="Ingrese su número de cédula"
                            name="numero_cedula"
                            id="cedula"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Rol de usuario</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🏢</span>
                        <select class="form-select" name="rol" id="rol" required>
                            <option value="">Seleccione su rol</option>
                            <option value="Empleado">Empleado</option>
                            <option value="Jefe inmediato">Jefe inmediato</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    Ingresar
                </button>

                <div class="forgot-password">
                    <a href="#">¿Olvidó su información de acceso?</a>
                </div>

                <div class="divider">
                    <span class="divider-text">O continúe con</span>
                </div>

                <div class="footer-text">
                    ¿Necesita ayuda? <a href="#">Contactar soporte</a>
                </div>
            </form>

            <!-- Footer con información de contacto -->
            <div style="
                text-align: center;
                padding: 20px 0;
                margin-top: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            ">
                <h4 style="margin: 0 0 10px 0; font-size: 16px; color: #ff69b4;"><p style="margin: 0; font-size: 13px; color: #666;">
                    Contacta al administrador:<br>
                    <strong>📧 admin@clinicadelseno.com</strong><br>
                    <strong>📞 +57 123 456 7890</strong>
                </p>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const originalButtonText = 'Ingresar';

        loginForm.addEventListener('submit', function(e) {
            const cedula = document.getElementById('cedula').value;
            const rol = document.getElementById('rol').value;

            if (cedula && rol) {
                // Add loading state
                loginBtn.classList.add('loading');
                loginBtn.textContent = '';
            }
        });

        // Input validation - only numbers for cedula
        document.getElementById('cedula').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Reset button state if needed
        window.addEventListener('load', function() {
            loginBtn.classList.remove('loading');
            loginBtn.textContent = originalButtonText;
        });
    </script>

</body>
</html>