<?php
include("connection.php");
$con = connection();

$id = mysqli_real_escape_string($con, $_POST['id']);
$numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
$nombres_completos = mysqli_real_escape_string($con, $_POST['nombres_completos']);
$sede_laborar = mysqli_real_escape_string($con, $_POST['sede_laborar']);
$cargo = mysqli_real_escape_string($con, $_POST['cargo']);
$telefono_personal = mysqli_real_escape_string($con, $_POST['telefono_personal']);
$correo_electronico = mysqli_real_escape_string($con, $_POST['correo_electronico']);
$eps = mysqli_real_escape_string($con, $_POST['eps']);
$fondo_pensiones = mysqli_real_escape_string($con, $_POST['fondo_pensiones']);
$fondo_cesantias = mysqli_real_escape_string($con, $_POST['fondo_cesantias']);
$nivel_educativo = mysqli_real_escape_string($con, $_POST['nivel_educativo']);
$profesion = mysqli_real_escape_string($con, $_POST['profesion']);
$fecha_nacimiento = mysqli_real_escape_string($con, $_POST['fecha_nacimiento']);
$lugar_nacimiento = mysqli_real_escape_string($con, $_POST['lugar_nacimiento']);
$ciudad_residencia = mysqli_real_escape_string($con, $_POST['ciudad_residencia']);
$direccion_residencia = mysqli_real_escape_string($con, $_POST['direccion_residencia']);
$barrio = mysqli_real_escape_string($con, $_POST['barrio']);
$grupo_rh = mysqli_real_escape_string($con, $_POST['grupo_rh']);
$contacto_emergencia_nombre = mysqli_real_escape_string($con, $_POST['contacto_emergencia_nombre']);
$contacto_emergencia_telefono = mysqli_real_escape_string($con, $_POST['contacto_emergencia_telefono']);
$estado_civil = mysqli_real_escape_string($con, $_POST['estado_civil']);
$tiene_hijos = mysqli_real_escape_string($con, $_POST['tiene_hijos']);
$hijo1_nombre_fecha = mysqli_real_escape_string($con, $_POST['hijo1_nombre_fecha']);
$hijo2_nombre_fecha = mysqli_real_escape_string($con, $_POST['hijo2_nombre_fecha']);
$hijo3_nombre_fecha = mysqli_real_escape_string($con, $_POST['hijo3_nombre_fecha']);
$comentarios = mysqli_real_escape_string($con, $_POST['comentarios']);
$puntuacion = mysqli_real_escape_string($con, $_POST['puntuacion']);
$fecha_ingreso = mysqli_real_escape_string($con, $_POST['fecha_ingreso']);
$estado_nomina = mysqli_real_escape_string($con, $_POST['estado_nomina']);
$role = isset($_POST['role']) ? mysqli_real_escape_string($con, $_POST['role']) : '';

// Campos de contrato
$tipo_contrato = isset($_POST['tipo_contrato']) ? mysqli_real_escape_string($con, $_POST['tipo_contrato']) : '';
$cargo_contrato = isset($_POST['cargo_contrato']) ? mysqli_real_escape_string($con, $_POST['cargo_contrato']) : '';
$horas = isset($_POST['horas']) && $_POST['horas'] !== '' ? intval($_POST['horas']) : NULL;
$renovacion = isset($_POST['renovacion']) && $_POST['renovacion'] !== '' ? mysqli_real_escape_string($con, $_POST['renovacion']) : NULL;

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
    estado_nomina='$estado_nomina',
    role='$role'
WHERE id='$id'";

$query = mysqli_query($con, $sql);
if($query){
    // Actualizar o insertar contrato asociado
    $chk_sql = "SELECT id FROM contratos WHERE identificacion = '$numero_cedula' AND fecha_retiro IS NULL ORDER BY id DESC LIMIT 1";
    $chk_res = mysqli_query($con, $chk_sql);
    $horas_sql = is_null($horas) ? "NULL" : intval($horas);
    $renov_sql = is_null($renovacion) ? "NULL" : "'" . $renovacion . "'";

    if($chk_res && mysqli_num_rows($chk_res) > 0){
        $c = mysqli_fetch_assoc($chk_res);
        $contract_id = $c['id'];
        $update_contract_sql = "UPDATE contratos SET 
            tipo_contrato='$tipo_contrato', 
            tipo_cargo='$cargo_contrato', 
            horas={$horas_sql}, 
            renovacion={$renov_sql}, 
            cargo='$cargo', 
            nombres_apellidos='$nombres_completos', 
            sede='$sede_laborar' 
            WHERE id='$contract_id'";
        mysqli_query($con, $update_contract_sql);
    } else {
        $insert_contract_sql = "INSERT INTO contratos (
            tipo_identificacion,
            identificacion,
            nombres_apellidos,
            cargo,
            tipo_cargo,
            horas,
            sede,
            tipo_contrato,
            renovacion
        ) VALUES (
            'CC',
            '$numero_cedula',
            '$nombres_completos',
            '$cargo',
            '$cargo_contrato',
            ".($horas_sql === "NULL" ? "NULL" : $horas_sql).",
            '$sede_laborar',
            '$tipo_contrato',
            ".($renov_sql === "NULL" ? "NULL" : $renov_sql)."
        )";
        mysqli_query($con, $insert_contract_sql);
    }

    Header("Location: index.php");
}else{
    echo "Error al actualizar el registro: " . mysqli_error($con);
}
?>