<?php
session_start();
include("connection.php");

if($_POST) {
    $con = connection();
    $numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
    $sql = "SELECT * FROM users WHERE numero_cedula = '$numero_cedula'";
    $query = mysqli_query($con, $sql);

    if($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_array($query);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nombres_completos'];
    // Guardar role en la clave usada por la app (compatibilidad con archivos existentes)
    $_SESSION['rol'] = $user['rol']; // valor original de la BD
    $_SESSION['role'] = $user['rol']; // clave que usa el resto del código (Empleado/Administrador)
    // Guardar la cédula con claves consistentes
    $_SESSION['user_cedula'] = $user['numero_cedula'];
    $_SESSION['numero_cedula'] = $user['numero_cedula'];

        if ($user['estado'] !== 'Activo') {
            header("Location: login.php?error=Usuario inactivo, contacte al administrador.");
            exit;
        }

        // Redirección según rol
        if ($user['rol'] == 'Administrador') {
            header("Location: index.php");
        } elseif ($user['rol'] == 'Jefe inmediato') {
            header("Location: ver_solicitudes_jefe.php");
        } elseif ($user['rol'] == 'Empleado') {
            header("Location: solicitud.php");
        } else {
            header("Location: solicitud.php");
        }
        exit();
    } else {
        header("Location: login.php?error=Usuario no encontrado.");
        exit;
    }
} else {
    header("Location: login.php");
    exit();
}
?>

<?php if (isset($_SESSION['login_error'])): ?>
    <div class="alert alert-danger" rol="alert">
        <?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
    </div>
<?php endif; ?>