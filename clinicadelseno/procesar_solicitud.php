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
$decision = $_POST['accion'] ?? $_POST['decision'] ?? '';
$comentario = $_POST['comentario'] ?? '';
$tipo_revisor = $_POST['tipo_revisor'] ?? '';
$fecha_actual = date('Y-m-d H:i:s');

// Normalizar decisión a valores esperados
if ($decision === 'Aprobada' || strtolower($decision) === 'aprobar') {
    $decision = 'Aprobada';
} elseif ($decision === 'Rechazada' || strtolower($decision) === 'rechazar') {
    $decision = 'Rechazada';
} else {
    $decision = trim($decision);
}

// Validar
if ($id && $decision && ($decision === 'Aprobada' || $decision === 'Rechazada')) {
    $role = $_SESSION['role'];
    $id = intval($id);

    // Preparar valores y consultas según rol
    if ($role === 'Jefe inmediato') {
        // Actualizar campos del jefe
        $sql = "UPDATE solicitudes SET estado_jefe = ?, comentario_jefe = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            die('Error en prepare: ' . mysqli_error($con));
        }
        mysqli_stmt_bind_param($stmt, 'ssi', $decision, $comentario, $id);
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            die('Error al ejecutar: ' . mysqli_error($con));
        }
    } else { // Administrador
        // Administrador actualiza estado_admin
        $sql = "UPDATE solicitudes SET estado_admin = ?, comentario_admin = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            die('Error en prepare: ' . mysqli_error($con));
        }
        mysqli_stmt_bind_param($stmt, 'ssi', $decision, $comentario, $id);
        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            die('Error al ejecutar: ' . mysqli_error($con));
        }

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
         // Redirigir de vuelta a la página anterior con la sección correspondiente
        $redirect_base = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'ver_solicitudes_jefe.php';
        $seccion = ($decision === 'Aprobada') ? 'aprobadas' : 'rechazadas';
        $redirect = $redirect_base . (strpos($redirect_base, '?') === false ? '?' : '&') . 'seccion=' . $seccion . '&msg=success';
        header("Location: " . $redirect);
        exit;
    } else {
        $redirect_base = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'ver_solicitudes_jefe.php';
        $redirect = $redirect_base . (strpos($redirect_base, '?') === false ? '?' : '&') . 'msg=error';
        header("Location: " . $redirect);
        exit;
    }
} else {
    $redirect_base = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'ver_solicitudes_jefe.php';
    $redirect = $redirect_base . (strpos($redirect_base, '?') === false ? '?' : '&') . 'msg=error';
    header("Location: " . $redirect);
    exit;
}
?>