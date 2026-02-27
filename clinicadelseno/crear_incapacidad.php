<?php
session_start();
if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

include 'connection.php';
$con = connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numero_cedula = mysqli_real_escape_string($con, $_SESSION['numero_cedula']);
    $fecha_inicio = mysqli_real_escape_string($con, $_POST['fecha_inicio']);
    $fecha_fin = mysqli_real_escape_string($con, $_POST['fecha_fin']);
    $motivo = mysqli_real_escape_string($con, $_POST['motivo']);

    $hora_inicio_raw = isset($_POST['hora_inicio']) ? trim($_POST['hora_inicio']) : '';
    $hora_fin_raw = isset($_POST['hora_fin']) ? trim($_POST['hora_fin']) : '';

    $hora_inicio = ($hora_inicio_raw === '') ? null : mysqli_real_escape_string($con, $hora_inicio_raw);
    $hora_fin = ($hora_fin_raw === '') ? null : mysqli_real_escape_string($con, $hora_fin_raw);

    $hora_inicio_sql = ($hora_inicio === null) ? 'NULL' : "'" . $hora_inicio . "'";
    $hora_fin_sql = ($hora_fin === null) ? 'NULL' : "'" . $hora_fin . "'";

    // Procesar archivo
    $archivo_incapacidad = null;
    if (isset($_FILES['archivo_incapacidad']) && $_FILES['archivo_incapacidad']['error'] == 0) {
        $file = $_FILES['archivo_incapacidad'];
        
        // Validar tamaño (máximo 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            header('Location: solicitud.php?tab=incapacidades&error=El archivo excede el tamaño máximo de 5MB');
            exit();
        }
        
        // Extensiones permitidas
        $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            header('Location: solicitud.php?tab=incapacidades&error=Tipo de archivo no permitido. Permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX');
            exit();
        }
        
        // Crear directorio si no existe
        $uploads_dir = 'uploads/incapacidades/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $timestamp = time();
        $random = rand(1000, 9999);
        $new_filename = $numero_cedula . '_incapacidad_' . $timestamp . '_' . $random . '.' . $file_ext;
        $file_path = $uploads_dir . $new_filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $archivo_incapacidad = mysqli_real_escape_string($con, $new_filename);
        } else {
            header('Location: solicitud.php?tab=incapacidades&error=Error al guardar el archivo');
            exit();
        }
    }

    // Insertar en base de datos
    $archivo_sql = ($archivo_incapacidad === null) ? 'NULL' : "'" . $archivo_incapacidad . "'";
    $sql = "INSERT INTO incapacidades (numero_cedula, fecha_inicio, hora_inicio, fecha_fin, hora_fin, motivo, archivo_incapacidad) VALUES ('$numero_cedula', '$fecha_inicio', $hora_inicio_sql, '$fecha_fin', $hora_fin_sql, '$motivo', $archivo_sql)";
    
    if (mysqli_query($con, $sql)) {
        header('Location: solicitud.php?tab=incapacidades&mensaje=Solicitud de incapacidad enviada exitosamente');
    } else {
        header('Location: solicitud.php?tab=incapacidades&error=Error al enviar la solicitud de incapacidad');
    }
    exit();
}
?>