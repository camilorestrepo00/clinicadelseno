<?php
include("connection.php");
$con = connection();

$id = $_POST["id"];
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
$fecha_ingreso = $_POST['fecha_ingreso'];
$estado_nomina = $_POST['estado_nomina'];

$sql = "UPDATE users SET 
    numero_cedula='$numero_cedula',
    nombres_completos='$nombres_completos',
    sede_laborar='$sede_laborar',
    cargo='$cargo',
    telefono_personal='$telefono_personal',
    correo_electronico='$correo_electronico',
    eps='$eps',
    fondo_pensiones='$fondo_pensiones',
    fondo_cesantias='$fondo_cesantias',
    nivel_educativo='$nivel_educativo',
    profesion='$profesion',
    fecha_nacimiento='$fecha_nacimiento',
    lugar_nacimiento='$lugar_nacimiento',
    ciudad_residencia='$ciudad_residencia',
    direccion_residencia='$direccion_residencia',
    barrio='$barrio',
    grupo_rh='$grupo_rh',
    contacto_emergencia_nombre='$contacto_emergencia_nombre',
    contacto_emergencia_telefono='$contacto_emergencia_telefono',
    estado_civil='$estado_civil',
    tiene_hijos='$tiene_hijos',
    hijo1_nombre_fecha='$hijo1_nombre_fecha',
    hijo2_nombre_fecha='$hijo2_nombre_fecha',
    hijo3_nombre_fecha='$hijo3_nombre_fecha',
    comentarios='$comentarios',
    puntuacion='$puntuacion',
    fecha_ingreso='$fecha_ingreso',
    estado_nomina='$estado_nomina'
WHERE id='$id'";

$query = mysqli_query($con, $sql);

if($query){
    Header("Location: index.php");
}else{
    echo "Error al actualizar el registro";
}
?>