<?php
session_start();
include("connection.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header("Location: login.php");
    exit;
}

$con = connection();

$id = $_GET['id'] ?? '';
if (!$id) {
    die("ID de contrato no especificado.");
}

// Obtener datos del contrato
$sql = "SELECT * FROM contratos WHERE id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$contrato = mysqli_fetch_assoc($result);

if (!$contrato) {
    die("Contrato no encontrado.");
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula_usuario = $_POST['cedula_usuario'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $estado = $_POST['estado'] ?? 'Activo';

    $sql_update = "UPDATE contratos SET cedula_usuario = ?, cargo = ?, fecha_inicio = ?, fecha_fin = ?, estado = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($con, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "sssssi", $cedula_usuario, $cargo, $fecha_inicio, $fecha_fin, $estado, $id);

    if (mysqli_stmt_execute($stmt_update)) {
        header("Location: index.php?seccion=contratos&msg=edit_success");
        exit;
    } else {
        $error = "Error al actualizar el contrato.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Contrato</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        body { background: #f8f9fa; }
        .edit-container {
            background: #fff;
            max-width: 500px;
            margin: 40px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
            padding: 30px;
        }
        h2 { text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select {
            width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;
        }
        .info-empleado {
            background: #f1f1f1;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .form-actions {
            text-align: center;
            margin-top: 20px;
        }
        .form-actions button, .form-actions a {
            padding: 8px 18px;
            border-radius: 4px;
            border: none;
            background: #e17055;
            color: #fff;
            font-weight: bold;
            margin-right: 10px;
            text-decoration: none;
            cursor: pointer;
        }
        .form-actions a {
            background: #b2bec3;
            color: #222;
        }
        .error {
            color: #b71c1c;
            background: #ffdada;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Editar Contrato</h2>
        <!-- Información del empleado asociada -->
        <div class="info-empleado">
            <strong>Empleado:</strong> <?= htmlspecialchars($contrato['nombres_completos'] ?? 'No registrado') ?><br>
            <strong>Cédula:</strong> <?= htmlspecialchars($contrato['numero_cedula'] ?? '') ?>
        </div>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="cedula_usuario">Número de cédula</label>
                <input type="text" name="cedula_usuario" id="cedula_usuario" value="<?= htmlspecialchars($contrato['cedula_usuario'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="cargo">Cargo</label>
                <input type="text" name="cargo" id="cargo" value="<?= htmlspecialchars($contrato['cargo'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_inicio">Fecha inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?= htmlspecialchars($contrato['fecha_inicio'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="<?= htmlspecialchars($contrato['fecha_fin'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select name="estado" id="estado">
                    <option value="Activo" <?= (isset($contrato['estado']) && $contrato['estado'] === 'Activo') ? 'selected' : '' ?>>Activo</option>
                    <option value="Inactivo" <?= (isset($contrato['estado']) && $contrato['estado'] === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit">Guardar cambios</button>
                <a href="index.php?seccion=contratos">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>