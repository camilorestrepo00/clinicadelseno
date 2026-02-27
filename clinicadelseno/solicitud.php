<?php
session_start();
// Debug opcional: añade ?debug=1 a la URL para ver rol y número de cédula
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    $dbg_role = isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'NULL';
    $dbg_user = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'NULL';
    $dbg_ced = isset($_SESSION['numero_cedula']) ? htmlspecialchars($_SESSION['numero_cedula']) : 'NULL';
    echo '<div style="position:fixed;right:10px;top:60px;background:#fff;border:1px solid #ccc;padding:8px;z-index:9999;">';
    echo 'Debug:<br>';
    echo 'role=' . $dbg_role . '<br>';
    echo 'user_name=' . $dbg_user . '<br>';
    echo 'numero_cedula=' . $dbg_ced . '<br>';
    echo '</div>';
}

if (
    !isset($_SESSION['user_name']) ||
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['Empleado', 'Jefe inmediato', 'Administrador'])
) {
    header('Location: login.php');
    exit();
}
require 'connection.php';
$con = connection();

// Mostrar solo las solicitudes del usuario actual
$sql = "SELECT s.*, u.nombres_completos 
        FROM solicitudes s 
        JOIN users u ON s.numero_cedula = u.numero_cedula 
        WHERE s.numero_cedula = '" . $_SESSION['numero_cedula'] . "' AND s.tipo_solicitud != 'Certificado' 
        ORDER BY s.fecha_solicitud DESC";

$query = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Solicitudes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: white;
            border-radius: 16px;
            padding: 24px 32px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
        }

        .header h1 {
            color: #ff69b4;
            font-size: 28px;
            font-weight: 600;
            margin: 0;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-name {
            color: #4a5568;
            font-size: 14px;
        }

        .btn-logout {
            background: #ff69b4;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #ff1493;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 105, 180, 0.3);
        }

        /* Navigation Tabs */
        .nav-tabs {
            display: flex;
            gap: 8px;
            background: white;
            padding: 8px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .nav-tab {
            flex: 1;
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-tab:hover {
            background: #f1f5f9;
        }

        .nav-tab.active {
            background: #ff69b4;
            color: white;
            box-shadow: 0 4px 12px rgba(255, 105, 180, 0.3);
        }

        /* Main Content */
        .main-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .form-title {
            text-align: center;
            font-size: 32px;
            color: #2d3748;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #2d3748;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #ff69b4;
            box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-upload {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 32px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload:hover {
            border-color: #ff69b4;
            background: #f7fafc;
        }

        .file-upload input {
            display: none;
        }

        .file-upload-text {
            color: #64748b;
            font-size: 14px;
        }

        .file-name {
            margin-top: 12px;
            color: #ff69b4;
            font-size: 13px;
            font-weight: 500;
        }

        /* Submit Button */
        .submit-container {
            display: flex;
            justify-content: center;
            margin-top: 32px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff85c0 0%, #ff69b4 100%);
            color: white;
            border: none;
            padding: 14px 48px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 105, 180, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 105, 180, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Time Input */
        .time-input-wrapper {
            position: relative;
        }

        .time-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        /* Table Styles */
        .table-content {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #f7fafc;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }

        table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        table tr:hover {
            background: #f7fafc;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .badge-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-aprobado {
            background: #dcfce7;
            color: #166534;
        }

        .badge-rechazado {
            background: #fee2e2;
            color: #991b1b;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .nav-tabs {
                flex-direction: column;
            }

            .main-content {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="img/logo.jpg" alt="Logo Clínica del Seno" class="header-logo">
                <h1>Sistema de Solicitudes</h1>
            </div>
            <div class="user-section">
                <span class="user-name">Usuario: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
                <a href="logout.php" class="btn-logout">
                    🔒 Salir
                </a>
            </div>
        </div>

        <?php
        if (isset($_GET['mensaje'])) {
            echo '<div id="message-alert" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px auto; max-width: 1200px; text-align: center; position: relative;">' . htmlspecialchars($_GET['mensaje']) . '<button onclick="closeMessage()" style="position: absolute; right: 10px; top: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button></div>';
        }
        if (isset($_GET['error'])) {
            echo '<div id="message-alert" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px auto; max-width: 1200px; text-align: center; position: relative;">' . htmlspecialchars($_GET['error']) . '<button onclick="closeMessage()" style="position: absolute; right: 10px; top: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button></div>';
        }
        ?>

        <?php if ($_SESSION['role'] === 'Empleado'): ?>
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showTab(event, 'nueva')">Nueva Solicitud</button>
            <button class="nav-tab" onclick="showTab(event, 'realizadas')">Solicitudes Realizadas</button>
            <button class="nav-tab" onclick="showTab(event, 'certificado')">Solicitud Certificado</button>
            <button class="nav-tab" onclick="showTab(event, 'incapacidades')">Incapacidades</button>
            <button class="nav-tab" onclick="showTab(event, 'perfil')">Perfil</button>
        </div>

        <!-- Main Content -->
        <!-- Tab: Nueva Solicitud -->
        <div id="tab-nueva" class="tab-content active">
            <div class="main-content">
                <h2 class="form-title">Nueva Solicitud</h2>
                
                <div class="form-container">
                    <form action="guardar_solicitud.php" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <!-- Tipo de solicitud -->
                            <div class="form-group full-width">
                                <label class="form-label">Tipo de solicitud</label>
                                <select name="tipo_solicitud" id="tipo_solicitud" class="form-select" required onchange="mostrarOpciones()">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Permiso">Permiso</option>
                                    <option value="Licencia">Licencia</option>
                                </select>
                            </div>

                            <!-- Opciones de permisos -->
                            <div id="opciones_permiso" style="display:none; grid-column: 1 / -1;">
                                <label class="form-label">Tipo de permiso</label>
                                <select name="detalle_solicitud" id="detalle_permiso" class="form-select">
                                    <option value="">Elige</option>
                                    <option value="PERMISO PARA ESTUDIO">PERMISO PARA ESTUDIO</option>
                                    <option value="PERMISOS MEDICOS">PERMISOS MEDICOS</option>
                                    <option value="PERMISOS DILIGENCIAS PERSONALES">PERMISOS DILIGENCIAS PERSONALES</option>
                                    <option value="PERMISOS ESPECIALES">PERMISOS ESPECIALES</option>
                                    <option value="PERMISO DE CUMPLEAÑOS">PERMISO DE CUMPLEAÑOS</option>
                                    <option value="DÍA DE LA FAMILIA">DÍA DE LA FAMILIA</option>
                                </select>
                            </div>

                            <!-- Opciones de licencias -->
                            <div id="opciones_licencia" style="display:none; grid-column: 1 / -1;">
                                <label class="form-label">Tipo de licencia</label>
                                <select name="detalle_solicitud" id="detalle_licencia" class="form-select">
                                    <option value="">Elige</option>
                                    <option value="LICENCIA DE LUTO">LICENCIA DE LUTO</option>
                                    <option value="LICENCIA DE PATERNIDAD">LICENCIA DE PATERNIDAD</option>
                                    <option value="LICENCIA DE MATERNIDAD">LICENCIA DE MATERNIDAD</option>
                                    <option value="LICENCIA DE LACTANCIA">LICENCIA DE LACTANCIA</option>
                                    <option value="LICENCIA DE MATRIMONIO">LICENCIA DE MATRIMONIO</option>
                                    <option value="LICENCIA NO REMUNERADA">LICENCIA NO REMUNERADA</option>
                                    <option value="LICENCIA REMUNERADA">LICENCIA REMUNERADA</option>
                                </select>
                            </div>

                            <!-- Fecha inicio -->
                            <div class="form-group">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-input" required>
                            </div>

                            <!-- Hora inicio -->
                            <div class="form-group">
                                <label class="form-label">Hora inicio</label>
                                <div class="time-input-wrapper">
                                    <input type="time" name="hora_inicio" id="hora_inicio" class="form-input" required>
                                    <span class="time-icon">🕐</span>
                                </div>
                            </div>

                            <!-- Fecha fin -->
                            <div class="form-group">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-input" required>
                            </div>

                            <!-- Hora salida -->
                            <div class="form-group">
                                <label class="form-label">Hora salida</label>
                                <div class="time-input-wrapper">
                                    <input type="time" name="hora_salida" id="hora_salida" class="form-input" required>
                                    <span class="time-icon">🕐</span>
                                </div>
                            </div>

                            <!-- Motivo de la solicitud -->
                            <div class="form-group full-width">
                                <label class="form-label">Motivo de la solicitud</label>
                                <textarea name="motivo" id="motivo" class="form-textarea" required placeholder="Describe el motivo de tu solicitud..."></textarea>
                            </div>

                            <!-- Adjuntar archivo -->
                            <div class="form-group full-width">
                                <label class="form-label">Adjuntar archivo (opcional)</label>
                                <label class="file-upload">
                                    <input type="file" name="archivo" id="fileInput">
                                    <div class="file-upload-text">
                                        📎 Haz clic para seleccionar un archivo<br>
                                        <small style="color: #94a3b8;">o arrastra y suelta aquí</small>
                                    </div>
                                    <div class="file-name" id="fileName"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="submit-container">
                            <button type="submit" class="btn-submit">Enviar Solicitud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Solicitudes Realizadas -->
        <div id="tab-realizadas" class="tab-content">
            <div class="main-content">
                <h2 class="form-title">Solicitudes Realizadas</h2>
                
                <div class="table-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Desde</th>
                                <th>Hora inicio</th>
                                <th>Hasta</th>
                                <th>Hora salida</th>
                                <th>Motivo</th>
                                <th>Estado Jefe</th>
                                <th>Estado Admin</th>
                                <th>Comentario</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        // Re-ejecutar la consulta para las solicitudes realizadas
                        $sql = "SELECT s.*, u.nombres_completos 
                                FROM solicitudes s 
                                JOIN users u ON s.numero_cedula = u.numero_cedula 
                                WHERE s.numero_cedula = '" . $_SESSION['numero_cedula'] . "' AND s.tipo_solicitud != 'Certificado' 
                                ORDER BY s.fecha_solicitud DESC";
                        $query = mysqli_query($con, $sql);
                        while ($row = mysqli_fetch_array($query)): 
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tipo_solicitud']) ?></td>
                                <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                                <td><?= htmlspecialchars($row['hora_inicio'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                                <td><?= htmlspecialchars($row['hora_salida'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['motivo']) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($row['estado_jefe'] ?? 'pendiente') ?>">
                                        <?= htmlspecialchars($row['estado_jefe'] ?? 'Pendiente') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower($row['estado_admin'] ?? 'pendiente') ?>">
                                        <?= htmlspecialchars($row['estado_admin'] ?? 'Pendiente') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $tiene_comentario = !empty($row['comentario_jefe']) || !empty($row['comentario_admin']);
                                        if ($tiene_comentario):
                                            if (!empty($row['comentario_jefe'])): ?>
                                                <strong>Jefe:</strong> <?= htmlspecialchars($row['comentario_jefe'] ?? '') ?><br>
                                            <?php endif;
                                            if (!empty($row['comentario_admin'])): ?>
                                                <strong>Admin:</strong> <?= htmlspecialchars($row['comentario_admin'] ?? '') ?>
                                            <?php endif;
                                        else: ?>
                                            Sin comentarios
                                        <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Certificado -->
        <div id="tab-certificado" class="tab-content">
            <div class="main-content">
                <h2 class="form-title">Solicitud de Certificado</h2>
                
                <div class="form-container">
                    <p style="text-align: center; color: #64748b; margin-bottom: 30px;">
                        Solicita un certificado para ti; si necesitas algo diferente, especifica en el detalle.
                    </p>
                    <form action="guardar_solicitud.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="numero_cedula" value="<?= htmlspecialchars($_SESSION['numero_cedula']) ?>">
                        <input type="hidden" name="tipo_solicitud" value="Certificado">
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label">Tipo de Certificado</label>
                                <select name="tipo_certificado" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Laboral">Laboral</option>
                                    <option value="Ingresos">Ingresos</option>
                                    <option value="Afiliacion">Afiliación</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Detalle (opcional)</label>
                                <textarea name="motivo" class="form-textarea" placeholder="Describe lo que necesitas..."></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Adjuntar archivo (opcional)</label>
                                <label class="file-upload">
                                    <input type="file" name="archivo" id="fileInputCert">
                                    <div class="file-upload-text">
                                        📎 Haz clic para seleccionar un archivo
                                    </div>
                                    <div class="file-name" id="fileNameCert"></div>
                                </label>
                            </div>
                        </div>

                        <div class="submit-container">
                            <button type="submit" class="btn-submit">Enviar Solicitud</button>
                        </div>
                    </form>
                </div>

                <!-- Mis certificados -->
                <div style="margin-top: 60px;">
                    <h2 class="form-title">Mis Certificados</h2>
                    
                    <div class="table-content">
                        <?php
                        $mi_cedula = $_SESSION['numero_cedula'] ?? '';
                        if ($mi_cedula) {
                            $cedula_safe = mysqli_real_escape_string($con, $mi_cedula);
                            $sql_mis = "SELECT * FROM certificados WHERE numero_cedula = '$cedula_safe' ORDER BY fecha_solicitud DESC";
                            $res_mis = mysqli_query($con, $sql_mis);
                            
                            if ($res_mis && mysqli_num_rows($res_mis) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Detalle</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Archivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($cert = mysqli_fetch_array($res_mis)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cert['tipo_certificado']) ?></td>
                                            <td><?= htmlspecialchars($cert['motivo'] ?? '-') ?></td>
                                            <td><?= !empty($cert['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($cert['fecha_solicitud'])) : '-' ?></td>
                                            <td>
                                                <span class="badge badge-<?= strtolower($cert['estado'] ?? 'pendiente') ?>">
                                                    <?= htmlspecialchars($cert['estado'] ?? 'Pendiente') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($cert['archivo'])): ?>
                                                    <a href="uploads/<?= htmlspecialchars($cert['archivo']) ?>" target="_blank">Ver</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p style="text-align: center; color: #64748b; padding: 40px;">No hay certificados disponibles.</p>
                            <?php endif;
                        } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Incapacidades -->
        <div id="tab-incapacidades" class="tab-content">
            <div class="main-content">
                <h2 class="form-title">Solicitud de Incapacidad</h2>
                
                <div class="form-container">
                    <form action="crear_incapacidad.php" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Fecha de Inicio *</label>
                                <input type="date" name="fecha_inicio" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora de Inicio</label>
                                <input type="time" name="hora_inicio" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Fecha de Fin *</label>
                                <input type="date" name="fecha_fin" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora de Fin</label>
                                <input type="time" name="hora_fin" class="form-input">
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Motivo *</label>
                                <textarea name="motivo" class="form-textarea" required placeholder="Describe el motivo de la incapacidad..."></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Archivo de Soporte (Certificado Médico, etc.)</label>
                                <input type="file" name="archivo_incapacidad" class="form-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx" title="Formatos permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX">
                                <small style="color: #64748b; margin-top: 8px; display: block;">Formatos permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX (Máximo 5MB)</small>
                            </div>
                        </div>
                        
                        <div class="submit-container">
                            <button type="submit" class="btn-submit">Enviar Solicitud de Incapacidad</button>
                        </div>
                    </form>
                </div>

                <!-- Mis Incapacidades -->
                <div style="margin-top: 60px;">
                    <h2 class="form-title">Mis Incapacidades</h2>
                    
                    <div class="table-content">
                        <?php
                        $mi_cedula = $_SESSION['numero_cedula'] ?? '';
                        if ($mi_cedula) {
                            $cedula_safe = mysqli_real_escape_string($con, $mi_cedula);
                            $sql_mis_incap = "SELECT * FROM incapacidades WHERE numero_cedula = '$cedula_safe' ORDER BY fecha_solicitud DESC";
                            $res_mis_incap = mysqli_query($con, $sql_mis_incap);
                            
                            if ($res_mis_incap && mysqli_num_rows($res_mis_incap) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha Inicio</th>
                                            <th>Hora Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Hora Fin</th>
                                            <th>Motivo</th>
                                            <th>Archivo</th>
                                            <th>Estado</th>
                                            <th>Comentario Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($incap = mysqli_fetch_array($res_mis_incap)): ?>
                                        <tr>
                                            <td><?= $incap['id'] ?></td>
                                            <td><?= htmlspecialchars($incap['fecha_inicio']) ?></td>
                                            <td><?= htmlspecialchars($incap['hora_inicio'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($incap['fecha_fin']) ?></td>
                                            <td><?= htmlspecialchars($incap['hora_fin'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($incap['motivo']) ?></td>
                                            <td>
                                                <?php if ($incap['archivo_incapacidad']): ?>
                                                    <a href="descargar_incapacidad.php?archivo=<?= urlencode($incap['archivo_incapacidad']) ?>" class="btn btn-info btn-sm" title="Descargar archivo">📥 Descargar</a>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8;">Sin archivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= strtolower($incap['estado']) ?>">
                                                    <?= htmlspecialchars($incap['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($incap['comentario_admin'] ?? 'Sin comentario') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p style="text-align: center; color: #64748b; padding: 40px;">No hay incapacidades registradas.</p>
                            <?php endif;
                        } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Perfil -->
        <div id="tab-perfil" class="tab-content">
            <div class="main-content">
                <h2 class="form-title">Editar Perfil</h2>
                
                <div class="form-container">
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $sql = "SELECT * FROM users WHERE id = '$user_id'";
                    $query = mysqli_query($con, $sql);
                    $user = mysqli_fetch_assoc($query);
                    $message = '';
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
                        $numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
                        $nombres_completos = mysqli_real_escape_string($con, $_POST['nombres_completos']);
                        $telefono_personal = mysqli_real_escape_string($con, $_POST['telefono_personal']);
                        $correo_electronico = mysqli_real_escape_string($con, $_POST['correo_electronico']);

                        $check_sql = "SELECT id FROM users WHERE numero_cedula = '$numero_cedula' AND id != '$user_id'";
                        $check_query = mysqli_query($con, $check_sql);
                        if (mysqli_num_rows($check_query) > 0) {
                            $message = "El número de cédula ya está en uso.";
                        } else {
                            $update_sql = "UPDATE users SET numero_cedula = '$numero_cedula', nombres_completos = '$nombres_completos', telefono_personal = '$telefono_personal', correo_electronico = '$correo_electronico' WHERE id = '$user_id'";
                            mysqli_query($con, $update_sql);
                            $message = "Perfil actualizado exitosamente.";
                            $_SESSION['user_name'] = $nombres_completos;
                            $_SESSION['numero_cedula'] = $numero_cedula;
                            $user = array_merge($user, $_POST);
                        }
                    }
                    ?>
                    <?php if ($message): ?>
                        <p style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; margin-bottom: 20px; text-align: center;"><?= htmlspecialchars($message) ?></p>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label">Número de Cédula</label>
                                <input type="text" name="numero_cedula" class="form-input" value="<?php echo htmlspecialchars($user['numero_cedula']); ?>" required>
                            </div>
                            <div class="form-group full-width">
                                <label class="form-label">Nombres Completos</label>
                                <input type="text" name="nombres_completos" class="form-input" value="<?php echo htmlspecialchars($user['nombres_completos']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Teléfono Personal</label>
                                <input type="tel" name="telefono_personal" class="form-input" value="<?php echo htmlspecialchars($user['telefono_personal']); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="correo_electronico" class="form-input" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>">
                            </div>
                        </div>
                        
                        <div class="submit-container">
                            <button type="submit" name="update_profile" class="btn-submit">Actualizar Perfil</button>
                        </div>
                    </form>
                </div>

                <!-- Información de contacto del administrador -->
                <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                    <h3 style="color: #2d3748; margin-bottom: 10px;">¿Necesitas Ayuda?</h3>
                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                        Para soporte técnico o consultas, contacta al administrador:<br>
                        <strong>Email:</strong> admin@clinicadelseno.com | <strong>Teléfono:</strong> +57 123 456 7890
                    </p>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Para Jefe o Administrador -->
        <div class="main-content">
            <h2 class="form-title">Solicitudes Recibidas</h2>
            
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Desde</th>
                            <th>Hora inicio</th>
                            <th>Hasta</th>
                            <th>Hora salida</th>
                            <th>Motivo</th>
                            <th>Estado Jefe</th>
                            <th>Estado Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_array($query)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombres_completos']) ?></td>
                            <td><?= htmlspecialchars($row['tipo_solicitud']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                            <td><?= htmlspecialchars($row['hora_inicio'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                            <td><?= htmlspecialchars($row['hora_salida'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['motivo']) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($row['estado_jefe'] ?? 'pendiente') ?>">
                                    <?= htmlspecialchars($row['estado_jefe'] ?? 'Pendiente') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($row['estado_admin'] ?? 'pendiente') ?>">
                                    <?= htmlspecialchars($row['estado_admin'] ?? 'Pendiente') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // File upload handlers
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const fileInputCert = document.getElementById('fileInputCert');
        const fileNameCert = document.getElementById('fileNameCert');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    fileName.textContent = '✓ ' + this.files[0].name;
                }
            });
        }

        if (fileInputCert) {
            fileInputCert.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    fileNameCert.textContent = '✓ ' + this.files[0].name;
                }
            });
        }

        // Tab navigation
        function showTab(event, tab) {
            // Prevenir comportamiento por defecto solo si hay evento
            if (event) {
                event.preventDefault();
            }
            
            // Ocultar todos los tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(t => t.classList.remove('active'));
            
            // Mostrar el tab seleccionado
            const selectedTab = document.getElementById('tab-' + tab);
            if (selectedTab) {
                selectedTab.classList.add('active');
            }
            
            // Remover clase active de todos los botones
            const buttons = document.querySelectorAll('.nav-tab');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Agregar clase active al botón correspondiente
            const activeButton = document.querySelector(`.nav-tab[onclick*="'${tab}'"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        }

        // Mostrar/ocultar opciones de permiso y licencia
        function mostrarOpciones() {
            const tipo = document.getElementById('tipo_solicitud').value;
            const permisoDiv = document.getElementById('opciones_permiso');
            const licenciaDiv = document.getElementById('opciones_licencia');
            
            if (permisoDiv) {
                permisoDiv.style.display = (tipo === 'Permiso') ? 'block' : 'none';
            }
            if (licenciaDiv) {
                licenciaDiv.style.display = (tipo === 'Licencia') ? 'block' : 'none';
            }
        }

        // Verificar si hay un parámetro 'tab' en la URL y activar esa pestaña al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) {
                showTab(null, tabParam);
            }

            // Ocultar mensaje automáticamente después de 5 segundos
            const messageAlert = document.getElementById('message-alert');
            if (messageAlert) {
                setTimeout(() => {
                    messageAlert.style.display = 'none';
                }, 5000);
            }
        });

        // Función para cerrar el mensaje
        function closeMessage() {
            const messageAlert = document.getElementById('message-alert');
            if (messageAlert) {
                messageAlert.style.display = 'none';
            }
        }
    </script>
</body>
</html>
