<?php
include 'connection.php';
session_start();

if (isset($_GET['id'])) {
    $con = connection();
    $id = intval($_GET['id']);
    $sql = "DELETE FROM users WHERE id = $id";
    mysqli_query($con, $sql);
    if (mysqli_affected_rows($con) > 0) {
        $_SESSION['message'] = "Usuario eliminado exitosamente.";
    } else {
        $_SESSION['error'] = "Error al eliminar el usuario. Por favor, inténtelo de nuevo.";
    }
}

header("Location: index.php");
exit();
?>