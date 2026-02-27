<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: index.php');
    exit;
}

$con = connection();
$id = $_GET['id'] ?? '';
$seccion = $_GET['seccion'] ?? 'registrados';
$fecha_retiro = $_GET['fecha_retiro'] ?? null;

if ($id) {
    $sql = "SELECT estado, numero_cedula FROM users WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $nuevo_estado = ($user['estado'] === 'Activo') ? 'Inactivo' : 'Activo';
    
    $sql_update = "UPDATE users SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql_update);
    mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $id);
    mysqli_stmt_execute($stmt);
    
    // Si el nuevo estado es Inactivo, guardar fecha de retiro en contratos
    if ($nuevo_estado === 'Inactivo' && $user['numero_cedula']) {
        // usar la fecha proporcionada o la fecha actual como fallback
        $fecha_to_use = !empty($fecha_retiro) ? $fecha_retiro : date('Y-m-d');
        $sql_contract = "UPDATE contratos SET fecha_retiro = ? WHERE identificacion = ? AND fecha_retiro IS NULL";
        $stmt_contract = mysqli_prepare($con, $sql_contract);
        mysqli_stmt_bind_param($stmt_contract, "ss", $fecha_to_use, $user['numero_cedula']);
        if (!mysqli_stmt_execute($stmt_contract)) {
            error_log('Error actualizando fecha_retiro: ' . mysqli_error($con));
        }
    }
}

header("Location: index.php?seccion=$seccion");
exit;