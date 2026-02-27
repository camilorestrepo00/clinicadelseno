<?php
session_start();
if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

include 'connection.php';
$con = connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $accion = mysqli_real_escape_string($con, $_POST['accion']);
    $comentario = mysqli_real_escape_string($con, $_POST['comentario']);

    $estado = ($accion == 'aprobar') ? 'aprobado' : 'rechazado';

    $sql = "UPDATE incapacidades SET estado = '$estado', comentario_admin = '$comentario' WHERE id = '$id'";
    if (mysqli_query($con, $sql)) {
        header('Location: index.php?seccion=incapacidades&mensaje=Incapacidad procesada exitosamente');
    } else {
        header('Location: index.php?seccion=incapacidades&error=Error al procesar la incapacidad');
    }
    exit();
}
?>