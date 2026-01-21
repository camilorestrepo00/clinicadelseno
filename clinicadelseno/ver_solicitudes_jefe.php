<?php
session_start();
// Permitir acceso a Jefe inmediato y Administrador
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Jefe inmediato', 'Administrador'])) {
    header("Location: login.php");
    exit;
}
?>

<?php
include("connection.php");

$con = connection();

// Consulta diferente según el rol
if ($_SESSION['role'] === 'Administrador') {
    $sql = "SELECT s.*, u.nombres_completos 
            FROM solicitudes s 
            JOIN users u ON s.numero_cedula = u.numero_cedula 
            WHERE s.estado_jefe = 'Aprobada' 
            AND s.estado_admin = 'Pendiente'
            ORDER BY s.id DESC";
} else if ($_SESSION['role'] === 'Jefe inmediato') {
    $sql = "SELECT s.*, u.nombres_completos 
            FROM solicitudes s 
            JOIN users u ON s.numero_cedula = u.numero_cedula 
            WHERE s.estado_jefe = 'Pendiente'
            ORDER BY s.id DESC";
}

$query = mysqli_query($con, $sql);

// Consulta para historial (todas menos las pendientes del rol actual)
if ($_SESSION['role'] === 'Jefe inmediato') {
    $sql_historial = "SELECT s.*, u.nombres_completos 
        FROM solicitudes s 
        JOIN users u ON s.numero_cedula = u.numero_cedula 
        WHERE s.estado_jefe != 'Pendiente'
        ORDER BY s.id DESC";
} else { // Administrador
    // Para mostrar todo el historial al administrador:
    $sql_historial = "SELECT s.*, u.nombres_completos 
        FROM solicitudes s 
        JOIN users u ON s.numero_cedula = u.numero_cedula 
        WHERE s.estado_jefe != 'Pendiente' OR s.estado_admin != 'Pendiente'
        ORDER BY s.id DESC";
}
$query_historial = mysqli_query($con, $sql_historial);

// Solicitudes aprobadas (incluye certificados emitibles)
$sql_aprobadas = "SELECT s.*, u.nombres_completos 
    FROM solicitudes s 
    JOIN users u ON s.numero_cedula = u.numero_cedula 
    WHERE s.estado_jefe = 'Aprobada' AND s.estado_admin = 'Aprobada'
    ORDER BY s.id DESC";
$query_aprobadas = mysqli_query($con, $sql_aprobadas);

// Certificados pendientes (tabla certificados estado Pendiente)
$sql_cert_pend = "SELECT c.*, u.nombres_completos FROM certificados c JOIN users u ON c.numero_cedula = u.numero_cedula WHERE c.estado = 'Pendiente' ORDER BY c.fecha_solicitud DESC";
$query_cert_pend = mysqli_query($con, $sql_cert_pend);

// Solicitudes rechazadas
$sql_rechazadas = "SELECT s.*, u.nombres_completos 
    FROM solicitudes s 
    JOIN users u ON s.numero_cedula = u.numero_cedula 
    WHERE s.estado_jefe = 'Rechazada' OR s.estado_admin = 'Rechazada'
    ORDER BY s.id DESC";
$query_rechazadas = mysqli_query($con, $sql_rechazadas);
?>

<?php
$seccion_actual = $_GET['seccion'] ?? 'pendientes';
?>

<?php
// Mostrar mensaje si existe
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'success') {
        echo '<div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 5px;">Solicitud procesada exitosamente.</div>';
    } elseif ($_GET['msg'] === 'error') {
        echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;">Error al procesar la solicitud.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes de Empleados</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    
    <div class="container">
        <div class="header" style="display: flex; align-items: center;">
            <img src="img/logo.jpg" alt="logo.jpg" style="height:90px; width:180px; object-fit:contain; margin-right:0px;">
            <h1>Gestión de solicitudes</h1>
            <div class="user-info">
                <span class="badge"><?= $_SESSION['role'] ?></span>
                <a href="perfil.php" class="btn btn-primary">Perfil</a>
                <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
            </div>
        </div>

        <!-- Menú de navegación para solicitudes -->
        <div class="menu" style="margin: 20px; text-align: center;">
            <a href="?seccion=crear" class="menu-btn<?= ($seccion_actual === 'crear' ? ' active' : '') ?>">Crear Solicitud</a>
            <a href="perfil.php" class="menu-btn">Perfil</a>
            <a href="?seccion=pendientes" id="btnPendientes" class="menu-btn<?= ($seccion_actual === 'pendientes' ? ' active' : '') ?>">Solicitudes pendientes</a>
            <a href="?seccion=aprobadas" id="btnAprobadas" class="menu-btn<?= ($seccion_actual === 'aprobadas' ? ' active' : '') ?>">Solicitudes aprobadas</a>
            <a href="?seccion=rechazadas" id="btnRechazadas" class="menu-btn<?= ($seccion_actual === 'rechazadas' ? ' active' : '') ?>">Solicitudes rechazadas</a>
        </div>

        <!-- Sección: Crear Solicitud -->
        <?php if ($seccion_actual === 'crear'): ?>
        <div id="crear" class="section active">
            <div class="form-container" style="max-width:600px;margin:0 auto;">
                <h2>Crear Nueva Solicitud</h2>
                <form action="guardar_solicitud.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="numero_cedula" value="<?= htmlspecialchars($_SESSION['numero_cedula']) ?>">
                    <div class="form-group">
                        <label>Tipo de Solicitud</label>
                        <select name="tipo_solicitud" id="tipo_solicitud" onchange="mostrarOpciones()" required>
                            <option value="">Seleccionar</option>
                            <option value="Permiso">Permiso</option>
                            <option value="Licencia">Licencia</option>
                        </select>
                    </div>
                    <div id="opciones_permiso" class="form-group" style="display:none;">
                        <label>Tipo de Permiso</label>
                        <select name="tipo_permiso">
                            <option value="">Seleccionar</option>
                            <option value="Personal">Personal</option>
                            <option value="Médico">Médico</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div id="opciones_licencia" class="form-group" style="display:none;">
                        <label>Tipo de Licencia</label>
                        <select name="tipo_licencia">
                            <option value="">Seleccionar</option>
                            <option value="Maternidad">Maternidad</option>
                            <option value="Paternidad">Paternidad</option>
                            <option value="Enfermedad">Enfermedad</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Fin</label>
                        <input type="date" name="fecha_fin" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Archivo (opcional)</label>
                        <input type="file" name="archivo">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sección: Solicitudes pendientes -->
        <?php if ($seccion_actual === 'pendientes'): ?>
        <div id="pendientes" class="section active">
            <div class="users-table">
                <h2>Solicitudes pendientes</h2>
                <table>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Motivo</th>
                        <th>Archivo</th>
                        <th>Estado Jefe</th>
                        <th>Estado Admin</th>
                        <th>Acciones</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_array($query)) : ?>
                        <tr>
                            <td><?= $row['nombres_completos'] ?></td>
                            <td><?= $row['tipo_solicitud'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_fin'])) ?></td>
                            <td><?= $row['motivo'] ?></td>
                            <td>
                                <?php if (!empty($row['archivo'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['archivo']) ?>" target="_blank">Ver archivo</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= strtolower($row['estado_jefe']) ?>"><?= $row['estado_jefe'] ?></span></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_admin']) ?>"><?= $row['estado_admin'] ?></span></td>
                            <td>
                                <?php if ($_SESSION['role'] === 'Jefe inmediato' && $row['estado_jefe'] === 'Pendiente'): ?>
                                    <button onclick="mostrarModal(<?= $row['id'] ?>, 'jefe')" class="btn btn-primary">Revisar</button>
                                <?php elseif ($_SESSION['role'] === 'Administrador' && $row['estado_jefe'] === 'Aprobada' && $row['estado_admin'] === 'Pendiente'): ?>
                                    <button onclick="mostrarModal(<?= $row['id'] ?>, 'admin')" class="btn btn-primary">Revisar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sección: Certificados pendientes por emitir (solo Administrador) -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrador'): ?>
        <div id="certificados-pendientes" class="section active">
            <div class="users-table" style="margin-top:40px;">
                <h2>Certificados pendientes por emitir</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Fecha solicitud</th>
                            <th>Motivo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($query_cert_pend): while ($c = mysqli_fetch_array($query_cert_pend)) : ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nombres_completos']) ?></td>
                            <td><?= htmlspecialchars($c['tipo_certificado']) ?></td>
                            <td><?= !empty($c['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($c['fecha_solicitud'])) : '-' ?></td>
                            <td><?= htmlspecialchars($c['motivo'] ?? '-') ?></td>
                            <td>
                                <a href="emitir_certificado.php?id=<?= intval($c['id']) ?>" class="btn btn-primary">Emitir</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5">No hay certificados pendientes.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sección: Solicitudes aprobadas -->
        <?php if ($seccion_actual === 'aprobadas'): ?>
        <div id="aprobadas" class="section active">
            <div class="users-table" style="margin-top:40px;">
                <h2>Solicitudes aprobadas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Motivo</th>
                            <th>Estado Jefe</th>
                            <th>Comentario Jefe</th>
                            <th>Estado Admin</th>
                            <th>Comentario Admin</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_array($query_aprobadas)) : ?>
                        <tr>
                            <td><?= $row['nombres_completos'] ?></td>
                            <td><?= $row['tipo_solicitud'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_fin'])) ?></td>
                            <td><?= $row['motivo'] ?></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_jefe']) ?>"><?= $row['estado_jefe'] ?></span></td>
                            <td><?= htmlspecialchars($row['comentario_jefe']) ?></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_admin']) ?>"><?= $row['estado_admin'] ?></span></td>
                            <td><?= htmlspecialchars($row['comentario_admin']) ?></td>
                            <td>
                                <?php if (!empty($row['archivo'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['archivo']) ?>" target="_blank">Ver archivo</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sección: Solicitudes rechazadas -->
        <?php if ($seccion_actual === 'rechazadas'): ?>
        <div id="rechazadas" class="section active">
            <div class="users-table" style="margin-top:40px;">
                <h2>Solicitudes rechazadas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Motivo</th>
                            <th>Estado Jefe</th>
                            <th>Comentario Jefe</th>
                            <th>Estado Admin</th>
                            <th>Comentario Admin</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_array($query_rechazadas)) : ?>
                        <tr>
                            <td><?= $row['nombres_completos'] ?></td>
                            <td><?= $row['tipo_solicitud'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_fin'])) ?></td>
                            <td><?= $row['motivo'] ?></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_jefe']) ?>"><?= $row['estado_jefe'] ?></span></td>
                            <td><?= htmlspecialchars($row['comentario_jefe']) ?></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_admin']) ?>"><?= $row['estado_admin'] ?></span></td>
                            <td><?= htmlspecialchars($row['comentario_admin']) ?></td>
                            <td>
                                <?php if (!empty($row['archivo'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['archivo']) ?>" target="_blank">Ver archivo</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sección: Historial de solicitudes -->
        <div id="historial" class="section">
            <div class="users-table" style="margin-top:40px;">
                <h2>Historial de solicitudes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Motivo</th>
                            <th>Estado Jefe</th>
                            <th>Comentario Jefe</th>
                            <th>Estado Admin</th>
                            <th>Comentario Admin</th>
                            <th>Archivo</th>
                            <th>Eliminar</th> <!-- Nueva columna -->
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_array($query_historial)) : ?>
                        <tr>
                            <td><?= $row['nombres_completos'] ?></td>
                            <td><?= $row['tipo_solicitud'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_fin'])) ?></td>
                            <td><?= $row['motivo'] ?></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_jefe']) ?>"><?= $row['estado_jefe'] ?></span></td>
                            <td><?= htmlspecialchars($row['comentario_jefe']) ?></td>
                            <td><span class="badge badge-<?= strtolower($row['estado_admin']) ?>"><?= $row['estado_admin'] ?></span></td>
                            <td><?= htmlspecialchars($row['comentario_admin']) ?></td>
                            <td>
                                <?php if (!empty($row['archivo'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['archivo']) ?>" target="_blank">Ver archivo</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="eliminar_solicitud.php?id=<?= $row['id'] ?>"
                                   onclick="return confirm('¿Seguro que deseas eliminar esta solicitud?');"
                                   class="btn btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para aprobar/rechazar -->
    <div id="modalRevision" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Revisar Solicitud</h2>
            <form action="procesar_solicitud.php" method="POST">
                <input type="hidden" id="solicitud_id" name="id">
                <input type="hidden" id="tipo_revisor" name="tipo_revisor">
                <div class="form-group">
                    <label>Decisión</label>
                    <select name="decision" required>
                        <option value="">Seleccionar</option>
                        <option value="Aprobada">Aprobar</option>
                        <option value="Rechazada">Rechazar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Comentario</label>
                    <textarea name="comentario" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <script>
    function mostrarModal(id, tipo) {
        document.getElementById('modalRevision').style.display = 'block';
        document.getElementById('solicitud_id').value = id;
        document.getElementById('tipo_revisor').value = tipo;
    }

    // Cerrar modal
    document.querySelector('.close').onclick = function() {
        document.getElementById('modalRevision').style.display = 'none';
    }

    function mostrarSeccion(seccion) {
        document.getElementById('pendientes').style.display = (seccion === 'pendientes') ? 'block' : 'none';
        document.getElementById('aprobadas').style.display = (seccion === 'aprobadas') ? 'block' : 'none';
        document.getElementById('rechazadas').style.display = (seccion === 'rechazadas') ? 'block' : 'none';
        document.getElementById('btnPendientes').classList.toggle('active', seccion === 'pendientes');
        document.getElementById('btnAprobadas').classList.toggle('active', seccion === 'aprobadas');
        document.getElementById('btnRechazadas').classList.toggle('active', seccion === 'rechazadas');
    }

    function mostrarOpciones() {
        var tipo = document.getElementById('tipo_solicitud').value;
        document.getElementById('opciones_permiso').style.display = (tipo === 'Permiso') ? 'block' : 'none';
        document.getElementById('opciones_licencia').style.display = (tipo === 'Licencia') ? 'block' : 'none';
    }
    </script>

    <style>
        .badge-pendiente {
            color: white;
            background-color: #ffc107;
        }
        .badge-rechazada {
            color: white;
            background-color: #dc3545;
        }
        .badge-aprobada {
            color: white;
            background-color:rgb(255, 255, 255);
        }
        .badge {
            display: inline-block;
            font-size: 14px;
            border-radius: 3px;
            padding: 5px 10px;
            font-weight: bold;
            background-color:rgb(214, 79, 79);
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .users-table th, .users-table td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        } 
        .users-table th {
            background-color: #f9f9f9;
            border: none;
            cursor: pointer;   margin: 0 10px;
            font-size: 16px;
            margin: 0 10px;
            padding: 10px 20px;
            transition: background 0.2s;
            border-radius: 5px;
        }
        .menu-btn.active, .menu-btn:hover {
            background:rgb(208, 106, 196);
            color: #fff;           
                  background:rgb(189, 71, 173);        
                  }        

        .section { display: none; }
        .section.active { display: block; }

        /* Modal styles */
        .modal {
            display: none; /* hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 6px;
            position: relative;
        }
        .modal .close {
            position: absolute;
            right: 12px;
            top: 8px;
            font-size: 22px;
            cursor: pointer;
        }
    </style>
</body>
</html>
