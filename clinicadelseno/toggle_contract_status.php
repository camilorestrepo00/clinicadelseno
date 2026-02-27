<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: index.php');
    exit;
}

$con = connection();
$id = $_GET['id'] ?? '';

if ($id) {
    $sql = "SELECT estado FROM contratos WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $contrato = mysqli_fetch_assoc($result);

    $nuevo_estado = ($contrato['estado'] === 'Activo') ? 'Inactivo' : 'Activo';

    $sql_update = "UPDATE contratos SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql_update);
    mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php?seccion=contratos");
exit;
?>