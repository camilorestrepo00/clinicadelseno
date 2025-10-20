<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Jefe inmediato' && $_SESSION['role'] !== 'Administrador')) {
    header('Location: index.php');
    exit;
}

$con = connection();
$id = $_GET['id'] ?? '';
$tipo = $_GET['tipo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];
    $comentario = $_POST['comentario'];
    $fecha_actual = date('Y-m-d H:i:s');
    
    if ($tipo === 'jefe') {
        $sql = "UPDATE solicitudes SET 
                estado_jefe = ?, 
                comentario_jefe = ?,
                fecha_revision_jefe = ?
                WHERE id = ?";
    } else {
        $sql = "UPDATE solicitudes SET 
                estado_admin = ?, 
                comentario_admin = ?,
                fecha_revision_admin = ?
                WHERE id = ?";
    }
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $estado, $comentario, $fecha_actual, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header('Location: index.php?seccion=solicitudes&msg=success');
        exit;
    }
}

// Obtener datos de la solicitud
$sql = "SELECT s.*, u.nombres_completos 
        FROM solicitudes s 
        JOIN users u ON s.numero_cedula = u.numero_cedula 
        WHERE s.id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$solicitud = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisar Solicitud</title>
    <link href="CSS/style.css" rel="stylesheet">
</head>
<body>
    <div class="review-form">
        <h2>Revisar Solicitud</h2>
        <form method="POST">
            <div class="form-group">
                <label>Empleado:</label>
                <p><?= htmlspecialchars($solicitud['nombres_completos']) ?></p>
            </div>
            
            <div class="form-group">
                <label>Tipo de Solicitud:</label>
                <p><?= htmlspecialchars($solicitud['tipo_solicitud']) ?></p>
            </div>
            
            <div class="form-group">
                <label>Fecha Inicio:</label>
                <p><?= date('d/m/Y', strtotime($solicitud['fecha_inicio'])) ?></p>
            </div>
            
            <div class="form-group">
                <label>Fecha Fin:</label>
                <p><?= date('d/m/Y', strtotime($solicitud['fecha_fin'])) ?></p>
            </div>
            
            <div class="form-group">
                <label for="estado">Decisión:</label>
                <select name="estado" required>
                    <option value="">Seleccionar</option>
                    <option value="Aprobar">Aprobar</option>
                    <option value="Rechazar">Rechazar</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comentario">Comentarios:</label>
                <textarea name="comentario" rows="4" required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Guardar Decisión</button>
                <a href="index.php?seccion=solicitudes" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>