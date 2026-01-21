<?php 
    include("connection.php");
    $con=connection();

    $id=$_GET['id'];

    $sql="SELECT * FROM users WHERE id='$id'";
    $query=mysqli_query($con, $sql);

    $row=mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="css/style.css" rel="stylesheet">
        <title>Editar usuarios</title>
        
    </head>
    <body>
        <div class="users-form">
            <h1>Editar usuario</h1>
            <form action="edit_user.php" method="POST">
                <input type="hidden" name="id" value="<?= $row['id']?>">
                <input type="text" name="numero_cedula" placeholder="NÃºmero de CÃ©dula de CiudadanÃ­a" value="<?= $row['numero_cedula']?>">
                <input type="text" name="nombres_completos" placeholder="Nombres y Apellidos Completos" value="<?= $row['nombres_completos']?>">
                <input type="text" name="sede_laborar" placeholder="Sede a Laborar" value="<?= $row['sede_laborar']?>">
                <input type="text" name="cargo" placeholder="Cargo" value="<?= $row['cargo']?>">
                <input type="tel" name="telefono_personal" placeholder="NÃºmero de TelÃ©fono Personal" value="<?= $row['telefono_personal']?>">
                <input type="email" name="correo_electronico" placeholder="Correo ElectrÃ³nico Personal" value="<?= $row['correo_electronico']?>">
                <input type="text" name="eps" placeholder="EPS" value="<?= $row['eps']?>">
                <input type="text" name="fondo_pensiones" placeholder="Fondo de Pensiones" value="<?= $row['fondo_pensiones']?>">
                <input type="text" name="fondo_cesantias" placeholder="Fondo de CesantÃ­as" value="<?= $row['fondo_cesantias']?>">
                <input type="text" name="nivel_educativo" placeholder="Nivel Educativo" value="<?= $row['nivel_educativo']?>">
                <input type="text" name="profesion" placeholder="ProfesiÃ³n" value="<?= $row['profesion']?>">
                <input type="date" name="fecha_nacimiento" placeholder="Fecha de Nacimiento" value="<?= $row['fecha_nacimiento']?>">
                <input type="text" name="lugar_nacimiento" placeholder="Lugar de Nacimiento" value="<?= $row['lugar_nacimiento']?>">
                <input type="text" name="ciudad_residencia" placeholder="Ciudad de Residencia" value="<?= $row['ciudad_residencia']?>">
                <input type="text" name="direccion_residencia" placeholder="DirecciÃ³n de Residencia" value="<?= $row['direccion_residencia']?>">
                <input type="text" name="barrio" placeholder="Barrio" value="<?= $row['barrio']?>">
                <input type="text" name="grupo_rh" placeholder="Grupo RH" value="<?= $row['grupo_rh']?>">
                <input type="text" name="contacto_emergencia_nombre" placeholder="Nombre Persona Contacto en Caso de Emergencia" value="<?= $row['contacto_emergencia_nombre']?>">
                <input type="tel" name="contacto_emergencia_telefono" placeholder="NÃºmero Persona Contacto" value="<?= $row['contacto_emergencia_telefono']?>">
                <input type="text" name="estado_civil" placeholder="Estado Civil" value="<?= $row['estado_civil']?>">
                <input type="text" name="tiene_hijos" placeholder="Tiene Hijos" value="<?= $row['tiene_hijos']?>">
                <input type="text" name="hijo1_nombre_fecha" placeholder="Nombres y Fecha de Nacimiento Hijo 1" value="<?= $row['hijo1_nombre_fecha']?>">
                <input type="text" name="hijo2_nombre_fecha" placeholder="Nombres y Fecha de Nacimiento Hijo 2" value="<?= $row['hijo2_nombre_fecha']?>">
                <input type="text" name="hijo3_nombre_fecha" placeholder="Nombres y Fecha de Nacimiento Hijo 3" value="<?= $row['hijo3_nombre_fecha']?>">
                <input type="text" name="comentarios" placeholder="Comentarios" value="<?= $row['comentarios']?>">
                <input type="number" name="puntuacion" placeholder="PuntuaciÃ³n" value="<?= $row['puntuacion']?>">
                <input type="date" name="fecha_ingreso" placeholder="Fecha de Ingreso" value="<?= $row['fecha_ingreso']?>">
                <input type="text" name="estado_nomina" placeholder="Estado NÃ³mina" value="<?= $row['estado_nomina']?>">
                <input type="submit" value="Actualizar">
            </form>
        </div>
    </body>
</html>