<?php
session_start();
include("connection.php");

// Verificar si el usuario está autenticado y es empleado
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Empleado') {
    header("Location: login.php");
    exit;
}

// Verificar que se envíen todos los campos requeridos
if (!isset($_POST['tipo_solicitud']) || !isset($_POST['fecha_inicio']) || 
    !isset($_POST['fecha_fin']) || !isset($_POST['motivo'])) {
    $_SESSION['error'] = "Todos los campos son obligatorios";
    header("Location: solicitud.php");
    exit;
}

$con = connection();

// Obtener y sanitizar datos
$tipo_solicitud = mysqli_real_escape_string($con, $_POST['tipo_solicitud']);
$fecha_inicio = mysqli_real_escape_string($con, $_POST['fecha_inicio']);
$fecha_fin = mysqli_real_escape_string($con, $_POST['fecha_fin']);
$motivo = mysqli_real_escape_string($con, $_POST['motivo']);
$numero_cedula = $_SESSION['user_cedula'];
$fecha_solicitud = date('Y-m-d H:i:s');
$estado = 'Pendiente';

// Crear la solicitud usando prepared statement
$sql = "INSERT INTO solicitudes (
            numero_cedula, 
            tipo_solicitud, 
            fecha_inicio, 
            fecha_fin, 
            motivo, 
            fecha_solicitud, 
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "sssssss", 
    $numero_cedula,
    $tipo_solicitud,
    $fecha_inicio,
    $fecha_fin,
    $motivo,
    $fecha_solicitud,
    $estado
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['mensaje'] = "Solicitud creada exitosamente";
} else {
    $_SESSION['error'] = "Error al crear la solicitud: " . mysqli_error($con);
}

mysqli_stmt_close($stmt);
mysqli_close($con);

header("Location: solicitud.php");
exit;
?>