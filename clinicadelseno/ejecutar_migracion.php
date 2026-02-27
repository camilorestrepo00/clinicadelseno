<?php
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

include 'connection.php';
$con = connection();

// Ejecutar la migración
$sql = "ALTER TABLE incapacidades 
        ADD COLUMN archivo_incapacidad VARCHAR(255) NULL AFTER comentario_admin,
        ADD COLUMN fecha_archivo TIMESTAMP NULL AFTER archivo_incapacidad;";

try {
    if (mysqli_query($con, $sql)) {
        $mensaje = "✓ Migración ejecutada correctamente. Las columnas han sido agregadas a la tabla incapacidades.";
    } else {
        // Si ya existen las columnas, mostrar un mensaje diferente
        if (strpos(mysqli_error($con), 'Duplicate column name') !== false) {
            $mensaje = "ℹ Las columnas ya existen en la tabla incapacidades.";
        } else {
            $mensaje = "✗ Error: " . mysqli_error($con);
        }
    }
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        $mensaje = "ℹ Las columnas ya existen en la tabla incapacidades.";
    } else {
        $mensaje = "✗ Error: " . $e->getMessage();
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejecutar Migración</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            text-align: center;
        }
        
        h1 {
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .message {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #ff69b4;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .button:hover {
            background: #ff1493;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 105, 180, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Migración de Base de Datos</h1>
        
        <div class="message <?= strpos($mensaje, '✓') === 0 ? 'success' : (strpos($mensaje, 'ℹ') === 0 ? 'info' : 'error') ?>">
            <?= $mensaje ?>
        </div>
        
        <a href="index.php?seccion=incapacidades" class="button">Volver a Incapacidades</a>
    </div>
</body>
</html>
