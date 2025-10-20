<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

require 'connection.php';
$con = connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // aceptar id desde POST (hidden) o desde la query string (form inline que apunta a ?id=)
    $id = 0;
    if (isset($_POST['id']) && intval($_POST['id']) > 0) {
        $id = intval($_POST['id']);
    } elseif (isset($_GET['id']) && intval($_GET['id']) > 0) {
        $id = intval($_GET['id']);
    }
    if (!$id) die('ID inválido');

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        die('No se recibió archivo.');
    }

    if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0777, true);
    $archivo_nombre = uniqid() . '_' . basename($_FILES['archivo']['name']);
    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], __DIR__ . '/uploads/' . $archivo_nombre)) {
        die('Error al guardar el archivo.');
    }

    $fecha = date('Y-m-d H:i:s');
    $emitido_por = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'admin';

    // Comprobar si las columnas emitido_por y fecha_emision existen
    $cols_res = mysqli_query($con, "SHOW COLUMNS FROM certificados LIKE 'emitido_por'");
    $has_emitido_por = ($cols_res && mysqli_num_rows($cols_res) > 0);
    $cols_res2 = mysqli_query($con, "SHOW COLUMNS FROM certificados LIKE 'fecha_emision'");
    $has_fecha_emision = ($cols_res2 && mysqli_num_rows($cols_res2) > 0);

    if ($has_emitido_por && $has_fecha_emision) {
        $sql = "UPDATE certificados SET archivo = ?, estado = 'Emitido', emitido_por = ?, fecha_emision = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $archivo_nombre, $emitido_por, $fecha, $id);
    } else {
        // Version compatible si no existen esas columnas
        $sql = "UPDATE certificados SET archivo = ?, estado = 'Emitido' WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $archivo_nombre, $id);
    }
    if (mysqli_stmt_execute($stmt)) {
        // Determinar URL de redirección de manera segura.
       
        $redirect = $default;

        // Preferir parámetro 'return' enviado por POST o GET (desde formulario/enlace)
        $ret = '';
        if (!empty($_POST['return'])) $ret = $_POST['return'];
        elseif (!empty($_GET['return'])) $ret = $_GET['return'];

        // Si se proporcionó 'return', permitir sólo rutas locales seguras (sin esquema ni host).
        if (!empty($ret) && is_string($ret)) {
            // Reject absolute URLs with scheme
            if (!preg_match('#^[a-zA-Z]+:\\/\\/#', $ret) && strpos($ret, '://') === false) {
                // Basic sanitation: permitir sólo caracteres básicos de rutas y query
                if (preg_match('#^[a-zA-Z0-9_\-\./\?=&%]+$#', $ret)) {
                    $redirect = $ret;
                }
            }
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            // Como fallback, usar HTTP_REFERER si pertenece al mismo host
            $ref = $_SERVER['HTTP_REFERER'];
            $ref_parts = parse_url($ref);
            $host = $_SERVER['HTTP_HOST'] ?? '';
            if (!empty($ref_parts['host']) && $ref_parts['host'] === $host) {
                $path = $ref_parts['path'] ?? '';
                $query = !empty($ref_parts['query']) ? ('?' . $ref_parts['query']) : '';
                $candidate = ltrim($path, '/') . $query;
                if (preg_match('#^[a-zA-Z0-9_\-\./\?=&%]+$#', $candidate)) {
                    $redirect = $candidate;
                }
            }
        }

        header('Location: ' . $redirect);
        exit();
    }
    die('Error al emitir certificado: ' . mysqli_error($con));
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) die('ID requerido');
$res = mysqli_query($con, "SELECT * FROM certificados WHERE id = " . intval($id));
if (!$res || mysqli_num_rows($res) == 0) die('Certificado no encontrado');
$row = mysqli_fetch_assoc($res);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Emitir Certificado</title>
</head>
<body>
    <h2>Emitir Certificado para <?php echo htmlspecialchars($row['numero_cedula']); ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo intval($id); ?>">
        <?php
        // Mantener la ruta de retorno si fue pasada por GET
        if (!empty($_GET['return'])) {
            $safe_return = htmlspecialchars($_GET['return'], ENT_QUOTES);
            echo '<input type="hidden" name="return" value="' . $safe_return . '">';
        }
        ?>
        <label>Subir archivo final (PDF):</label>
        <input type="file" name="archivo" accept="application/pdf" required>
        <button type="submit">Emitir</button>
    </form>
    <p><a href="<?php echo $default; ?>">Volver</a></p>
</body>
</html>
