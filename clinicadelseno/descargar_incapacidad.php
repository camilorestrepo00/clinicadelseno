<?php
session_start();

// Validar que el usuario esté logueado
if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Obtener el archivo solicitado
$archivo = isset($_GET['archivo']) ? basename($_GET['archivo']) : null;

if (!$archivo) {
    die('Archivo no especificado.');
}

// Ruta del archivo
$file_path = 'uploads/incapacidades/' . $archivo;

// Validar que el archivo existe
if (!file_exists($file_path)) {
    die('El archivo no existe o ha sido eliminado.');
}

// Validar que es un archivo permitido y que el usuario tiene acceso
include 'connection.php';
$con = connection();

// Obtener información del archivo de la base de datos
$archivo_escaped = mysqli_real_escape_string($con, $archivo);
$sql = "SELECT numero_cedula FROM incapacidades WHERE archivo_incapacidad = '$archivo_escaped' LIMIT 1";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

// Validar permisos: el archivo pertenece al usuario actual o el usuario es administrador
if (!$row) {
    die('Archivo no encontrado en la base de datos.');
}

if ($_SESSION['role'] !== 'Administrador' && $row['numero_cedula'] !== $_SESSION['numero_cedula']) {
    die('No tienes permiso para acceder a este archivo.');
}

// Obtener información del archivo
$file_info = pathinfo($file_path);
$file_size = filesize($file_path);
$file_type = mime_content_type($file_path);

// Si mime_content_type no está disponible, usar una alternativa
if ($file_type === false) {
    $ext = strtolower($file_info['extension']);
    $mime_types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];
    $file_type = $mime_types[$ext] ?? 'application/octet-stream';
}

// Descargar el archivo
header('Content-Type: ' . $file_type);
header('Content-Disposition: attachment; filename="' . $file_info['basename'] . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Leer y enviar el archivo
readfile($file_path);
exit();
?>
