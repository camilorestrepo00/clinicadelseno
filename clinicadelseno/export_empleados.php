<?php 
require 'connection.php';
$con = connection();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=empleados.csv');

$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, [
    'ID', 'Cédula', 'Nombres', 'Sede', 'Cargo', 'Teléfono', 'Email', 'EPS', 'Fondo Pensiones', 'Fondo Cesantías',
    'Nivel Educativo', 'Profesión', 'Fecha Nacimiento', 'Lugar Nacimiento', 'Ciudad Residencia', 'Dirección Residencia',
    'Barrio', 'Grupo RH', 'Contacto Emergencia', 'Teléfono Emergencia', 'Estado Civil', 'Tiene Hijos',
    'Hijo 1', 'Hijo 2', 'Hijo 3', 'Comentarios', 'Puntuación', 'Fecha Ingreso', 'Estado Nómina', 'Rol', 'Estado'
]);

$sql = "SELECT * FROM users WHERE estado = 'Activo'";
$query = mysqli_query($con, $sql);

while ($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, [
        $row['id'], $row['numero_cedula'], $row['nombres_completos'], $row['sede_laborar'], $row['cargo'],
        $row['telefono_personal'], $row['correo_electronico'], $row['eps'], $row['fondo_pensiones'], $row['fondo_cesantias'],
        $row['nivel_educativo'], $row['profesion'], $row['fecha_nacimiento'], $row['lugar_nacimiento'], $row['ciudad_residencia'],
        $row['direccion_residencia'], $row['barrio'], $row['grupo_rh'], $row['contacto_emergencia_nombre'],
        $row['contacto_emergencia_telefono'], $row['estado_civil'], $row['tiene_hijos'], $row['hijo1_nombre_fecha'],
        $row['hijo2_nombre_fecha'], $row['hijo3_nombre_fecha'], $row['comentarios'], $row['puntuacion'],
        $row['fecha_ingreso'], $row['estado_nomina'], $row['role'], $row['estado']
    ]);
}
fclose($output);
exit();