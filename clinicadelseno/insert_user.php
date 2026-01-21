<?php
session_start();
include("connection.php");
$con = connection();

// Solo el administrador puede crear roles
if ($_SESSION['role'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

$id = null;
$numero_cedula = $_POST['numero_cedula'];
$nombres_completos = $_POST['nombres_completos'];
$sede_laborar = $_POST['sede_laborar'];
$cargo = $_POST['cargo'];
$telefono_personal = $_POST['telefono_personal'];
$correo_electronico = $_POST['correo_electronico'];
$eps = $_POST['eps'];
$fondo_pensiones = $_POST['fondo_pensiones'];
$fondo_cesantias = $_POST['fondo_cesantias'];
$nivel_educativo = $_POST['nivel_educativo'];
$profesion = $_POST['profesion'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$lugar_nacimiento = $_POST['lugar_nacimiento'];
$ciudad_residencia = $_POST['ciudad_residencia'];
$direccion_residencia = $_POST['direccion_residencia'];
$barrio = $_POST['barrio'];
$grupo_rh = $_POST['grupo_rh'];
$contacto_emergencia_nombre = $_POST['contacto_emergencia_nombre'];
$contacto_emergencia_telefono = $_POST['contacto_emergencia_telefono'];
$estado_civil = $_POST['estado_civil'];
$tiene_hijos = $_POST['tiene_hijos'];
$hijo1_nombre_fecha = $_POST['hijo1_nombre_fecha'];
$hijo2_nombre_fecha = $_POST['hijo2_nombre_fecha'];
$hijo3_nombre_fecha = $_POST['hijo3_nombre_fecha'];
$comentarios = $_POST['comentarios'];
$puntuacion = $_POST['puntuacion'];
$email_adicional = $_POST['email_adicional'];
$fecha_ingreso = $_POST['fecha_ingreso'];
$estado_nomina = $_POST['estado_nomina'];
$role = $_POST['role']; // <- Nuevo campo

$sql = "INSERT INTO users (
    numero_cedula,
    nombres_completos,
    sede_laborar,
    cargo,
    telefono_personal,
    correo_electronico,
    eps,
    fondo_pensiones,
    fondo_cesantias,
    nivel_educativo,
    profesion,
    fecha_nacimiento,
    lugar_nacimiento,
    ciudad_residencia,
    direccion_residencia,
    barrio,
    grupo_rh,
    contacto_emergencia_nombre,
    contacto_emergencia_telefono,
    estado_civil,
    tiene_hijos,
    hijo1_nombre_fecha,
    hijo2_nombre_fecha,
    hijo3_nombre_fecha,
    comentarios,
    puntuacion,
    fecha_ingreso,
    estado_nomina,
    role
) VALUES (
    '$numero_cedula',
    '$nombres_completos',
    '$sede_laborar',
    '$cargo',
    '$telefono_personal',
    '$correo_electronico',
    '$eps',
    '$fondo_pensiones',
    '$fondo_cesantias',
    '$nivel_educativo',
    '$profesion',
    '$fecha_nacimiento',
    '$lugar_nacimiento',
    '$ciudad_residencia',
    '$direccion_residencia',
    '$barrio',
    '$grupo_rh',
    '$contacto_emergencia_nombre',
    '$contacto_emergencia_telefono',
    '$estado_civil',
    '$tiene_hijos',
    '$hijo1_nombre_fecha',
    '$hijo2_nombre_fecha',
    '$hijo3_nombre_fecha',
    '$comentarios',
    '$puntuacion',
    '$fecha_ingreso',
    '$estado_nomina',
    '$role'
)";

$query = mysqli_query($con, $sql);

if ($query) {
    Header("Location: index.php");
}
?>