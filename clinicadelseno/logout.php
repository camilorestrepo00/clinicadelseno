<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>
<div class="header">
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['role']) ?></h1>
    <a href="logout.php" class="btn btn-danger" style="float:right;">Cerrar sesión</a>
</div>
<?php
$sql = "SELECT * FROM users WHERE numero_cedula = ? AND estado = 'Activo' LIMIT 1";
?>