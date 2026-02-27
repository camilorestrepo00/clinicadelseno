<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $con = connection();
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar que las nuevas contraseñas coincidan
    if ($new_password !== $confirm_password) {
        $error = "Las nuevas contraseñas no coinciden.";
    } else {
        // Obtener la contraseña actual del usuario
        $sql = "SELECT password FROM users WHERE id = '$user_id'";
        $query = mysqli_query($con, $sql);
        $user = mysqli_fetch_assoc($query);

        $password_valid = !$user['password'] || password_verify($current_password, $user['password']);

        if ($password_valid) {
            // Hash de la nueva contraseña
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Actualizar la contraseña
            $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
            $update_query = mysqli_query($con, $update_sql);

            if ($update_query) {
                $success = "Contraseña cambiada exitosamente.";
            } else {
                $error = "Error al cambiar la contraseña.";
            }
        } else {
            $error = "Contraseña actual incorrecta.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
</head>
<body>
    <h2>Cambiar Contraseña</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <input type="password" name="current_password" placeholder="Contraseña Actual" required><br>
        <input type="password" name="new_password" placeholder="Nueva Contraseña" required><br>
        <input type="password" name="confirm_password" placeholder="Confirmar Nueva Contraseña" required><br>
        <input type="submit" value="Cambiar Contraseña">
    </form>
    <a href="index.php">Volver</a>
</body>
</html>