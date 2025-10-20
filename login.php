<?php
session_start();
require 'connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = connection();
    $numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
    $rol_form = mysqli_real_escape_string($con, $_POST['rol']);

    // Buscar usuario por número de cédula
    $sql = "SELECT * FROM users WHERE numero_cedula = '$numero_cedula'";
    $query = mysqli_query($con, $sql);

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        if ($user['estado'] !== 'Activo') {
            $error = "Usuario inactivo, contacte al administrador.";
        } elseif ($user['role'] !== $rol_form) {
            $error = "El rol seleccionado no corresponde al usuario.";
        } else {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombres_completos'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['numero_cedula'] = $user['numero_cedula'];

            // Redirección según rol
            if ($user['role'] == 'Administrador') {
                header("Location: index.php");
            } elseif ($user['role'] == 'Jefe inmediato') {
                header("Location: ver_solicitudes_jefe.php");
            } else {
                header("Location: solicitud.php");
            }
            exit();
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        body {
            background-color:rgb(225, 92, 203);
            font-family: Arial, sans-serif;
        }
        .login-container {
            width: 300px;
            margin: 100px auto;         
            padding: 20px;  
            /* Quita el fondo blanco y usa el mismo color del body */
            background-color: rgb(246, 142, 229);
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container input[type="text"],
        .login-container select {
            width: 100%;    
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color:rgb(213, 6, 6);  
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color:rgb(200, 72, 209);
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
    <script>    
        // Aquí puedes agregar scripts adicionales si es necesario
    </script>       



 </head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="numero_cedula" placeholder="Número de cédula" required>
            <select name="rol" required>
                <option value="">Seleccione su rol</option>
                <option value="Empleado">Empleado</option>
                <option value="Jefe inmediato">Jefe inmediato</option>
                <option value="Administrador">Administrador</option>
            </select>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>