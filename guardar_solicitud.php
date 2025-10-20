<?php
session_start();

if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

include 'connection.php';

$con = connection();

// Manejo de upload (si hay)
$archivo_nombre = null;
if (isset($_FILES['archivo']) && isset($_FILES['archivo']['error']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir(__DIR__ . '/uploads')) {
        @mkdir(__DIR__ . '/uploads', 0777, true);
    }
    $tmp_name = $_FILES['archivo']['tmp_name'];
    $nombre_original = basename($_FILES['archivo']['name']);
    $archivo_nombre = uniqid() . "_" . $nombre_original;
    move_uploaded_file($tmp_name, __DIR__ . "/uploads/" . $archivo_nombre);
}

// Inputs
$numero_cedula    = trim($_POST['numero_cedula'] ?? $_SESSION['numero_cedula'] ?? '');
$tipo_solicitud   = trim($_POST['tipo_solicitud'] ?? '');
$tipo_certificado = trim($_POST['tipo_certificado'] ?? '');
$motivo           = trim($_POST['motivo'] ?? '');

// Campos opcionales
$fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
$hora_inicio  = trim($_POST['hora_inicio'] ?? '');
$fecha_fin    = trim($_POST['fecha_fin'] ?? '');
$hora_salida  = trim($_POST['hora_salida'] ?? '');

$fecha_solicitud = date('Y-m-d H:i:s');

// Validaciones mínimas
if (empty($numero_cedula) || empty($tipo_solicitud)) {
    header('Location: solicitud.php?msg=error_missing');
    exit();
}

// Helper de logging
function log_error_insert($con, $tipo, $numero_cedula, $last_error, $post_data) {
    $logmsg = "[" . date('Y-m-d H:i:s') . "] Error INSERT {$tipo} for {$numero_cedula}: {$last_error} -- POST=" . json_encode($post_data) . "\n";
    if (!is_dir(__DIR__ . '/logs')) @mkdir(__DIR__ . '/logs', 0777, true);
    @file_put_contents(__DIR__ . '/logs/guardar_solicitud.log', $logmsg, FILE_APPEND);
    error_log($logmsg);
}

// Si es una solicitud de Certificado, insertar en tabla `certificados`
if (strcasecmp($tipo_solicitud, 'Certificado') === 0) {
    $sql = "INSERT INTO certificados (numero_cedula, tipo_certificado, motivo, archivo, creado_por, fecha_solicitud, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        log_error_insert($con, 'certificados_prepare', $numero_cedula, mysqli_error($con), $_POST);
        header('Location: solicitud.php?msg=error_sql');
        exit();
    }
    $creado_por = $_SESSION['user_name'] ?? 'usuario';
    $estado_cert = 'Pendiente';
    mysqli_stmt_bind_param($stmt, 'sssssss', $numero_cedula, $tipo_certificado, $motivo, $archivo_nombre, $creado_por, $fecha_solicitud, $estado_cert);
    $exec_ok = mysqli_stmt_execute($stmt);
    $last_error = mysqli_error($con);
    if (!$exec_ok || mysqli_errno($con)) {
        log_error_insert($con, 'certificados', $numero_cedula, $last_error, $_POST);
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            die('Error SQL certificados: ' . $last_error . '\nPOST: ' . print_r($_POST, true));
        }
        header('Location: solicitud.php?msg=error_sql');
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    // Otras solicitudes van a la tabla `solicitudes`
    $sql = "INSERT INTO solicitudes (numero_cedula, tipo_solicitud, tipo_certificado, fecha_inicio, hora_inicio, fecha_fin, hora_salida, motivo, archivo, fecha_solicitud, estado, estado_jefe, estado_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        log_error_insert($con, 'solicitudes_prepare', $numero_cedula, mysqli_error($con), $_POST);
        header('Location: solicitud.php?msg=error_sql');
        exit();
    }
    $estado_por_defecto = 'Pendiente';
    $estado_jefe = 'Pendiente';
    $estado_admin = 'Pendiente';
    mysqli_stmt_bind_param($stmt, "sssssssssssss", $numero_cedula, $tipo_solicitud, $tipo_certificado, $fecha_inicio, $hora_inicio, $fecha_fin, $hora_salida, $motivo, $archivo_nombre, $fecha_solicitud, $estado_por_defecto, $estado_jefe, $estado_admin);
    $exec_ok = mysqli_stmt_execute($stmt);
    $last_error = mysqli_error($con);
    if (!$exec_ok || mysqli_errno($con)) {
        log_error_insert($con, 'solicitudes', $numero_cedula, $last_error, $_POST);
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            die('Error SQL solicitudes: ' . $last_error . '\nPOST: ' . print_r($_POST, true));
        }
        header('Location: solicitud.php?msg=error_sql');
        exit();
    }
    mysqli_stmt_close($stmt);
}

// Redirigir según rol para evitar enviar administradores a la página de empleados
$role = $_SESSION['role'] ?? '';
if (in_array($role, ['Administrador', 'Jefe inmediato'])) {
    header("Location: index.php?seccion=certificado&msg=success");
} else {
    header("Location: solicitud.php?msg=success");
}
exit();
?>