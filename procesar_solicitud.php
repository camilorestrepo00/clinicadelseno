<?php
session_start();
include("connection.php");

// Verificar que el usuario tenga permisos
if (!isset($_SESSION['role'])) {
    die("Sesión expirada. Por favor, vuelve a iniciar sesión.");
}
if (!in_array($_SESSION['role'], ['Administrador', 'Jefe inmediato'])) {
    header("Location: login.php");
    exit;
}

$con = connection();
// Aceptar tanto los nombres que envía solicitud.php como otras variantes
$id = $_POST['solicitud_id'] ?? $_POST['id'] ?? '';
$accion = $_POST['accion'] ?? $_POST['decision'] ?? '';
$comentario = $_POST['comentario'] ?? '';
$fecha_actual = date('Y-m-d H:i:s');

// Normalizar acción a valores esperados
$accion_lower = strtolower(trim($accion));
if ($accion_lower === 'aprobar' || $accion_lower === 'aprobar como jefe' || $accion_lower === 'aprobar final') {
    $decision = 'Aprobada';
} elseif ($accion_lower === 'rechazar' || $accion_lower === 'rechazada') {
    $decision = 'Rechazada';
} else {
    $decision = '';
}

// Validar
if ($id && $decision) {
    $role = $_SESSION['role'];

    // Preparar valores y consultas según rol
    if ($role === 'Jefe inmediato') {
        // Actualizar campos del jefe y el estado global
        $nuevo_estado_global = ($decision === 'Aprobada') ? 'Aprobada por Jefe' : 'Rechazada';
        $sql = "UPDATE solicitudes SET estado_jefe = ?, comentario_jefe = ?, fecha_revision_jefe = ?, estado = ?, procesado_por = ?, fecha_proceso = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssi', $decision, $comentario, $fecha_actual, $nuevo_estado_global, $_SESSION['user_name'], $fecha_actual, $id);
        $ok = mysqli_stmt_execute($stmt);
    } else { // Administrador
        // Antes de actualizar, comprobar si ya aprobó el jefe (opcional)
        $row_sol = null;
        $res_sol = mysqli_query($con, "SELECT estado_jefe FROM solicitudes WHERE id = " . intval($id));
        if ($res_sol) $row_sol = mysqli_fetch_assoc($res_sol);

        // Determinar estado global
        if ($decision === 'Aprobada') {
            $nuevo_estado_global = 'Aprobada';
        } else {
            $nuevo_estado_global = 'Rechazada';
        }

        $sql = "UPDATE solicitudes SET estado_admin = ?, comentario_admin = ?, fecha_revision_admin = ?, estado = ?, procesado_por = ?, fecha_proceso = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssi', $decision, $comentario, $fecha_actual, $nuevo_estado_global, $_SESSION['user_name'], $fecha_actual, $id);
        $ok = mysqli_stmt_execute($stmt);

        // Si admin aprueba y es solicitud de tipo Certificado, crear entrada en certificados
        if ($ok && $decision === 'Aprobada') {
            $res_sol_full = mysqli_query($con, "SELECT numero_cedula, tipo_solicitud, tipo_certificado, motivo, archivo FROM solicitudes WHERE id = " . intval($id));
            if ($res_sol_full && $row_full = mysqli_fetch_array($res_sol_full)) {
                if (isset($row_full['tipo_solicitud']) && $row_full['tipo_solicitud'] === 'Certificado') {
                    $nc = $row_full['numero_cedula'];
                    $tc = $row_full['tipo_certificado'] ?? 'Laboral';
                    $mot = $row_full['motivo'] ?? null;
                    $arch = $row_full['archivo'] ?? null;
                    $creado_por = $row_full['numero_cedula'];
                    $fecha_solic = date('Y-m-d H:i:s');
                    $sql_ins = "INSERT INTO certificados (numero_cedula, tipo_certificado, motivo, archivo, creado_por, fecha_solicitud, estado) VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')";
                    $stmt2 = mysqli_prepare($con, $sql_ins);
                    mysqli_stmt_bind_param($stmt2, 'ssssss', $nc, $tc, $mot, $arch, $creado_por, $fecha_solic);
                    mysqli_stmt_execute($stmt2);
                }
            }
        }
    }

    if (!empty($ok) && $ok) {
        // Redirigir de vuelta a la página anterior (si existe) o a solicitud.php
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'solicitud.php';
        header("Location: " . $redirect . (strpos($redirect, '?') === false ? '?msg=success' : '&msg=success'));
        exit;
    } else {
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'solicitud.php';
        header("Location: " . $redirect . (strpos($redirect, '?') === false ? '?msg=error' : '&msg=error'));
        exit;
    }
} else {
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'solicitud.php';
    header("Location: " . $redirect . (strpos($redirect, '?') === false ? '?msg=error' : '&msg=error'));
    exit;
}
?>