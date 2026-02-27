<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que solo administradores puedan eliminar
if ($_SESSION['role'] !== 'Administrador') {
    header("Location: index.php?seccion=solicitudes&error=No tienes permisos para eliminar solicitudes");
    exit;
}

include 'connection.php';
$con = connection();

// Validar que el ID de la solicitud esté presente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?seccion=solicitudes&error=ID de solicitud no válido");
    exit;
}

$id = intval($_GET['id']);

// Primero, obtener el nombre del archivo para eliminarlo del servidor
$sql_get = "SELECT archivo FROM solicitudes WHERE id = '$id'";
$query_get = mysqli_query($con, $sql_get);

if ($query_get && mysqli_num_rows($query_get) > 0) {
    $row = mysqli_fetch_assoc($query_get);
    $archivo = $row['archivo'];
    
    // Eliminar el archivo del servidor si existe
    if (!empty($archivo) && file_exists("uploads/" . $archivo)) {
        unlink("uploads/" . $archivo);
    }
    
    // Eliminar la solicitud de la base de datos
    $sql_delete = "DELETE FROM solicitudes WHERE id = '$id'";
    if (mysqli_query($con, $sql_delete)) {
        header("Location: index.php?seccion=solicitudes&success=Solicitud eliminada correctamente");
    } else {
        header("Location: index.php?seccion=solicitudes&error=Error al eliminar la solicitud: " . mysqli_error($con));
    }
} else {
    header("Location: index.php?seccion=solicitudes&error=Solicitud no encontrada");
}

exit;
?>