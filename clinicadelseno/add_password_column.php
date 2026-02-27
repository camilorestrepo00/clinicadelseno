<?php
include 'connection.php';
$con = connection();

// Agregar columna password si no existe
$sql = "ALTER TABLE users ADD COLUMN password VARCHAR(255) DEFAULT NULL";
$query = mysqli_query($con, $sql);

if ($query) {
    echo "Columna password agregada exitosamente.";
} else {
    echo "Error al agregar columna: " . mysqli_error($con);
}
?>