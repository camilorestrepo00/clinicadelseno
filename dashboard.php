<?php
session_start();
include("connection.php");

// Validar si está logueado y es un empleado
if (!isset($_SESSION['numero_cedula']) || $_SESSION['role'] !== 'Empleado') {
    header("Location: login.php");
    exit;
}

$con = connection();

// Obtener los datos del usuario logueado (opcional)
$cedula = $_SESSION['numero_cedula'];
$sql = "SELECT * FROM users WHERE numero_cedula = '$cedula' LIMIT 1";
$query = mysqli_query($con, $sql);
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Empleado</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido, <?= htmlspecialchars($user['nombres_completos']) ?></h1>
            <div class="user-info">
                <span class="badge"><?= htmlspecialchars($user['role']) ?></span>
                <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
            </div>
        </div>

        <div class="users-form">
            <h2>Tu información personal</h2>
            <ul style="text-align: left; font-weight: bold;">
                <li><strong>Cédula:</strong> <?= $user['numero_cedula'] ?></li>
                <li><strong>Correo:</strong> <?= $user['correo_electronico'] ?></li>
                <li><strong>Teléfono:</strong> <?= $user['telefono_personal'] ?></li>
                <li><strong>Fecha de nacimiento:</strong> <?= $user['fecha_nacimiento'] ?></li>
                <li><strong>Sede:</strong> <?= $user['sede_laborar'] ?></li>
                <li><strong>Cargo:</strong> <?= $user['cargo'] ?></li>
                <li><strong>Puntuación:</strong> <?= $user['puntuacion'] ?></li>
            </ul>
        </div>

        <div class="users-form">
            <h2>Próximamente</h2>
            <p>Puedes consultar tus solicitudes, vacaciones, licencias, etc.</p>
        </div>
    </div>
</body>
</html>