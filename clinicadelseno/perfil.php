<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header('Location: login.php');
    exit();
}
include 'connection.php';

$con = connection();
$user_id = $_SESSION['user_id'];

// Obtener datos del usuario
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$query = mysqli_query($con, $sql);
$user = mysqli_fetch_assoc($query);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
        $nombres_completos = mysqli_real_escape_string($con, $_POST['nombres_completos']);
        $telefono_personal = mysqli_real_escape_string($con, $_POST['telefono_personal']);
        $correo_electronico = mysqli_real_escape_string($con, $_POST['correo_electronico']);

        // Verificar si la cédula ya existe en otro usuario
        $check_sql = "SELECT id FROM users WHERE numero_cedula = '$numero_cedula' AND id != '$user_id'";
        $check_query = mysqli_query($con, $check_sql);
        if (mysqli_num_rows($check_query) > 0) {
            $message = "El número de cédula ya está en uso.";
            $message_type = 'error';
        } else {
            $update_sql = "UPDATE users SET numero_cedula = '$numero_cedula', nombres_completos = '$nombres_completos', telefono_personal = '$telefono_personal', correo_electronico = '$correo_electronico' WHERE id = '$user_id'";
            if (mysqli_query($con, $update_sql)) {
                $message = "Perfil actualizado exitosamente.";
                $message_type = 'success';
                // Actualizar sesión
                $_SESSION['user_name'] = $nombres_completos;
                $_SESSION['numero_cedula'] = $numero_cedula;
                // Recargar datos del usuario
                $query = mysqli_query($con, $sql);
                $user = mysqli_fetch_assoc($query);
            } else {
                $message = "Error al actualizar el perfil.";
                $message_type = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .user-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .role-badge {
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .page-title {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .page-subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #34495e;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input:hover {
            border-color: #b0b0b0;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 150px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.3);
        }

        .icon {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .container {
                padding: 25px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="img/logo.jpg" alt="Logo" style="height:70px; width:140px; object-fit:contain; border-radius: 8px;">
            <h1>Sistema de Gestión</h1>
        </div>
        <div class="user-info">
            <span class="user-badge">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <span class="role-badge">🔐 <?= htmlspecialchars($_SESSION['role']) ?></span>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </div>
    </div>

    <div class="container">
        <h2 class="page-title">Mi Perfil</h2>
        <p class="page-subtitle">Actualiza tu información personal</p>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= $message_type === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-section">
                <h3 class="section-title">
                    <span>📋</span> Información Personal
                </h3>

                <div class="form-group">
                    <label for="numero_cedula">Número de Cédula *</label>
                    <input type="text" id="numero_cedula" name="numero_cedula" 
                           value="<?= htmlspecialchars($user['numero_cedula']) ?>" 
                           required pattern="[0-9]+" 
                           title="Solo se permiten números">
                </div>

                <div class="form-group">
                    <label for="nombres_completos">Nombres Completos *</label>
                    <input type="text" id="nombres_completos" name="nombres_completos" 
                           value="<?= htmlspecialchars($user['nombres_completos']) ?>" 
                           required minlength="3">
                </div>

                <div class="form-group">
                    <label for="telefono_personal">Teléfono Personal</label>
                    <input type="tel" id="telefono_personal" name="telefono_personal" 
                           value="<?= htmlspecialchars($user['telefono_personal']) ?>" 
                           pattern="[0-9]{10}" 
                           title="Ingrese un número de 10 dígitos">
                </div>

                <div class="form-group">
                    <label for="correo_electronico">Correo Electrónico</label>
                    <input type="email" id="correo_electronico" name="correo_electronico" 
                           value="<?= htmlspecialchars($user['correo_electronico']) ?>">
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" name="update_profile" class="btn-primary">
                    💾 Guardar Cambios
                </button>
                <a href="javascript:history.back()" class="btn-secondary">
                    ← Volver
                </a>
            </div>
        </form>

        <!-- Información de contacto del administrador -->
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
            <h3 style="color: #2c3e50; margin-bottom: 10px;">¿Necesitas Ayuda?</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 14px;">
                Para soporte técnico o consultas, contacta al administrador:<br>
                <strong>Email:</strong> admin@clinicadelseno.com | <strong>Teléfono:</strong> +57 123 456 7890
            </p>
        </div>
    </div>
</body>
</html>