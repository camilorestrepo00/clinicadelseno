<?php
session_start();

if (!isset($_SESSION['numero_cedula'])) {
    header("Location: login.php");
    exit;
}

include 'connection.php';

$con = connection();

$archivo_nombre = null;
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    // Asegúrate de que la carpeta uploads exista
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    $tmp_name = $_FILES['archivo']['tmp_name'];
    $nombre_original = basename($_FILES['archivo']['name']);
    $archivo_nombre = uniqid() . "_" . $nombre_original;
    move_uploaded_file($tmp_name, "uploads/" . $archivo_nombre);
}

// Recoge los valores del formulario
$numero_cedula   = $_SESSION['numero_cedula']; // O $_POST si lo envías por formulario
$tipo_solicitud  = $_POST['tipo_solicitud'] ?? '';
$fecha_inicio    = $_POST['fecha_inicio'] ?? '';
$fecha_fin       = $_POST['fecha_fin'] ?? '';
$motivo          = $_POST['motivo'] ?? '';

// Inserta en la base de datos
$sql = "INSERT INTO solicitudes (numero_cedula, tipo_solicitud, fecha_inicio, fecha_fin, motivo, archivo) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $numero_cedula, $tipo_solicitud, $fecha_inicio, $fecha_fin, $motivo, $archivo_nombre);
mysqli_stmt_execute($stmt);

header("Location: index.php?seccion=solicitudes&msg=success");
exit;
?>