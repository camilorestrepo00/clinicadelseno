<?php 
    include("connection.php");
    $con=connection();

    $id=$_GET['id'];

    $sql="SELECT * FROM users WHERE id='$id'";
    $query=mysqli_query($con, $sql);

    $row=mysqli_fetch_array($query);

    // Obtener contrato activo asociado (si existe)
    $numero_cedula = $row['numero_cedula'];
    $sql_contract = "SELECT * FROM contratos WHERE identificacion = '$numero_cedula' AND fecha_retiro IS NULL ORDER BY id DESC LIMIT 1";
    $q_contract = mysqli_query($con, $sql_contract);
    $contract = mysqli_fetch_array($q_contract);
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

                <label for="role">Rol</label>
                <select name="role" id="role" required>
                    <option value="">Seleccionar Rol</option>
                    <option value="Empleado" <?= $row['role'] === 'Empleado' ? 'selected' : '' ?>>Empleado</option>
                    <option value="Jefe inmediato" <?= $row['role'] === 'Jefe inmediato' ? 'selected' : '' ?>>Jefe inmediato</option>
                    <option value="Administrador" <?= $row['role'] === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                </select>

                <h3>Datos de Contrato</h3>
                <label for="tipo_contrato">Tipo de Contrato</label>
                <select name="tipo_contrato" id="tipo_contrato">
                    <option value="Prestación de Servicios" <?= (isset($contract['tipo_contrato']) && $contract['tipo_contrato'] === 'Prestación de Servicios') ? 'selected' : '' ?>>Prestación de Servicios</option>
                </select>
                <input type="text" name="cargo_contrato" placeholder="Cargo en Contrato" value="<?= isset($contract['tipo_cargo']) ? htmlspecialchars($contract['tipo_cargo']) : '' ?>">
                <input type="number" name="horas" placeholder="Horas Semanales" min="0" value="<?= isset($contract['horas']) ? intval($contract['horas']) : '' ?>">
                <div class="form-group">
                    <label for="renovacion">Fecha de Renovación:</label>
                    <input type="date" id="renovacion" name="renovacion" value="<?= isset($contract['renovacion']) ? $contract['renovacion'] : '' ?>">
                </div>

                <input type="submit" value="Actualizar">
            </form>
        </div>
    </body>
</html>