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
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Solicitudes</h1>
            <div class="user-info-menu">
                <!-- Avatar eliminado -->
                <div class="user-details">
                    <span class="badge"><?= $_SESSION['role'] ?></span>
                    <span class="user-name">Usuario: <?= $_SESSION['user_name'] ?></span>
                </div>
                
                    <a href="logout.php" class="btn btn-danger" title="Cerrar sesión">
                        <span>🔒</span> Salir
                    </a>
                </div>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'Empleado'): ?>
        <!-- Menú de pestañas -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('nueva')">Nueva Solicitud</button>
            <button class="tab-btn" onclick="showTab('realizadas')">Solicitudes Realizadas</button>
            <button class="tab-btn" onclick="showTab('certificado')">Solicitud Certificado</button>
            <button class="tab-btn" onclick="showTab('perfil')">Perfil</button>
        </div>
        <!-- Contenido de pestañas -->
        <div id="tab-nueva" class="tab-content" style="display:block;">
            <div class="form-container">
                <h2>Nueva Solicitud</h2>
                <form action="guardar_solicitud.php" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <label for="tipo_solicitud">Tipo de solicitud</label>
                        <select name="tipo_solicitud" id="tipo_solicitud" required onchange="mostrarOpciones()">
                            <option value="">Elige</option>
                            <option value="Permiso">Permiso</option>
                            <option value="Licencia">Licencia</option>
                        </select>
                    </div>

                    <!-- Opciones de permisos -->
                    <div id="opciones_permiso" style="display:none; margin-top:10px;">
                        <label for="detalle_permiso">Tipo de permiso</label>
                        <select name="detalle_solicitud" id="detalle_permiso">
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
                    <div id="opciones_licencia" style="display:none; margin-top:10px;">
                        <label for="detalle_licencia">Tipo de licencia</label>
                        <select name="detalle_solicitud" id="detalle_licencia">
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

                    <div class="form-row">
                        <label for="fecha_inicio">Fecha inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required placeholder="Fecha inicio">
                    </div>
                    <div class="form-row">
                        <label for="hora_inicio">Hora inicio</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" required placeholder="Hora inicio">
                    </div>
                    <div class="form-row">
                        <label for="fecha_fin">Fecha fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required placeholder="Fecha fin">
                    </div>
                    <div class="form-row">
                        <label for="hora_salida">Hora salida</label>
                        <input type="time" id="hora_salida" name="hora_salida" required placeholder="Hora salida">
                    </div>
                    <div class="form-row">
                        <label for="motivo">Motivo de la solicitud</label>
                        <textarea id="motivo" name="motivo" required placeholder="Motivo de la solicitud"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Adjuntar archivo (opcional):</label>
                        <input type="file" name="archivo">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                </form>
            </div>
        </div>
        <div id="tab-realizadas" class="tab-content" style="display:none;">
            <div class="users-table">
                <h2>Solicitudes Realizadas</h2>
                <table>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th>Desde</th>  
                        <th>Hora inicio</th>
                        <th>Hasta</th>
                        <th>Hora salida</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Comentario</th>
                    </tr>
                    <?php while ($row = mysqli_fetch_array($query)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombres_completos']) ?></td>
                            <td><?= htmlspecialchars($row['tipo_solicitud']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                            <td><?= htmlspecialchars($row['hora_inicio']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                            <td><?= htmlspecialchars($row['hora_salida']) ?></td>
                            <td><?= htmlspecialchars($row['motivo']) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($row['estado']) ?>">
                                    <?= htmlspecialchars($row['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['estado'] !== 'Pendiente'): ?>
                                    <?= htmlspecialchars($row['comentario_revision'] ?? 'Sin comentarios') ?>
                                <?php else: ?>
                                    En espera de revisión
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['estado'] !== 'Pendiente'): ?>
                                    <?= htmlspecialchars($row['procesado_por'] ?? 'N/A') ?>
                                    <br>
                                    <small><?= htmlspecialchars($row['fecha_proceso'] ?? '') ?></small>
                                <?php else: ?>
                                    Pendiente
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
        <div id="tab-certificado" class="tab-content" style="display:none;">
            <div class="form-container" style="max-width:600px;margin:0 auto;">
                <h2>Solicitud de Certificado</h2>
                <p>Solicita un certificado para ti; si necesitas algo diferente, especifica en el detalle.</p>
                <form action="guardar_solicitud.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="numero_cedula" value="<?= htmlspecialchars($_SESSION['numero_cedula']) ?>">
                    <input type="hidden" name="tipo_solicitud" value="Certificado">
                    <div class="form-group">
                        <label>Tipo de Certificado</label>
                        <select name="tipo_certificado" required>
                            <option value="">Seleccionar</option>
                            <option value="Laboral">Laboral</option>
                            <option value="Ingresos">Ingresos</option>
                            <option value="Afiliacion">Afiliación</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Detalle (opcional)</label>
                        <textarea name="motivo"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Adjuntar archivo (opcional)</label>
                        <input type="file" name="archivo">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                </form>
            </div>
            
            <!-- Listado de certificados emitidos al empleado -->
            <div class="users-table" style="max-width:900px;margin:30px auto;">
                <h3>Mis Certificados</h3>
                <?php
                $mi_cedula = $_SESSION['numero_cedula'] ?? '';
                if ($mi_cedula) {
                    $cedula_safe = mysqli_real_escape_string($con, $mi_cedula);
                    $sql_mis = "SELECT * FROM certificados WHERE numero_cedula = '$cedula_safe' ORDER BY fecha_solicitud DESC";
                    $res_mis = mysqli_query($con, $sql_mis);
                    if (!$res_mis) {
                        // Mostrar error de base de datos para depuración
                        echo '<p style="color:#b00;">Error en la consulta de certificados: ' . htmlspecialchars(mysqli_error($con)) . '</p>';
                    } else {
                        // Mostrar número de certificados encontrados (ayuda a entender por qué la tabla está vacía)
                        $count_cert = mysqli_num_rows($res_mis);
                        echo '<p style="color:#333; font-size:14px;">(Debug) Certificados encontrados: ' . $count_cert . '</p>';
                    }
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
                                    <td><?= htmlspecialchars($cert['estado'] ?? 'Pendiente') ?></td>
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
                        <p>No hay certificados disponibles.</p>
                    <?php endif;
                } else {
                    echo '<p>No se pudo determinar tu número de cédula en sesión.</p>';
                }
                ?>
            </div>
        </div>
        <div id="tab-perfil" class="tab-content" style="display:none;">
            <div class="form-container" style="max-width:600px;margin:0 auto;">
                <h2>Editar Perfil</h2>
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
                        $user = array_merge($user, $_POST); // Update local array
                    }
                }
                ?>
                <?php if ($message): ?>
                    <p><?php echo $message; ?></p>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Número de Cédula</label>
                        <input type="text" name="numero_cedula" value="<?php echo $user['numero_cedula']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nombres Completos</label>
                        <input type="text" name="nombres_completos" value="<?php echo $user['nombres_completos']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono Personal</label>
                        <input type="tel" name="telefono_personal" value="<?php echo $user['telefono_personal']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo_electronico" value="<?php echo $user['correo_electronico']; ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Actualizar Perfil</button>
                </form>
            </div>
        </div>
        <script>
            function showTab(tab) {
                document.getElementById('tab-nueva').style.display = (tab === 'nueva') ? 'block' : 'none';
                document.getElementById('tab-realizadas').style.display = (tab === 'realizadas') ? 'block' : 'none';
                document.getElementById('tab-certificado').style.display = (tab === 'certificado') ? 'block' : 'none';
                document.getElementById('tab-perfil').style.display = (tab === 'perfil') ? 'block' : 'none';
                var btns = document.getElementsByClassName('tab-btn');
                for (var i = 0; i < btns.length; i++) {
                    btns[i].classList.remove('active');
                }
                if(tab === 'nueva') btns[0].classList.add('active');
                else if(tab === 'realizadas') btns[1].classList.add('active');
                else if(tab === 'certificado') btns[2].classList.add('active');
                else if(tab === 'perfil') btns[3].classList.add('active');
                // Scroll automático al abrir el chat
                if(tab === 'certificado') {
                    // no-op
                }
            }

            function mostrarOpciones() {
                var tipo = document.getElementById('tipo_solicitud').value;
                document.getElementById('opciones_permiso').style.display = (tipo === 'Permiso') ? 'block' : 'none';
                document.getElementById('opciones_licencia').style.display = (tipo === 'Licencia') ? 'block' : 'none';
            }
        </script>
        <?php else: ?>
        <!-- Para admin o jefe, muestra solo la tabla como antes -->
        <div class="users-table">
            <h2>Solicitudes Recibidas</h2>
            <table>
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Desde</th>
                    <th>hora inicio</th>
                    <th>Hasta</th>
                    <th>hora final</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Comentario</th>
                </tr>
                <?php while ($row = mysqli_fetch_array($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombres_completos']) ?></td>
                        <td><?= htmlspecialchars($row['tipo_solicitud']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                        <td><?= htmlspecialchars($row['hora_inicio']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                        <td><?= htmlspecialchars($row['hora_salida']) ?></td>
                        <td><?= htmlspecialchars($row['motivo']) ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($row['estado']) ?>">
                                <?= htmlspecialchars($row['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['estado'] !== 'Pendiente'): ?>
                                <?= htmlspecialchars($row['comentario_revision'] ?? 'Sin comentarios') ?>
                            <?php else: ?>
                                En espera de revisión
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['estado'] !== 'Pendiente'): ?>
                                <?= htmlspecialchars($row['procesado_por'] ?? 'N/A') ?>
                                <br>
                                <small><?= htmlspecialchars($row['fecha_proceso'] ?? '') ?></small>
                            <?php else: ?>
                                Pendiente
                            <?php endif; ?>
                        </td>
                        <?php if ($_SESSION['role'] !== 'Empleado' && $row['estado'] === 'Pendiente'): ?>
                        <td>
                            <form action="procesar_solicitud.php" method="POST" class="action-buttons">
                                <input type="hidden" name="solicitud_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="accion" value="aprobar" class="btn btn-success btn-sm">
                                    Aprobar
                                </button>
                                <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm">
                                    Rechazar
                                </button>
                                <textarea name="comentario" placeholder="Comentario (opcional)"></textarea>
                            </form>
                        </td>
                        <?php elseif ($_SESSION['role'] === 'Jefe inmediato' && $row['estado'] === 'Pendiente'): ?>
                        <td>
                            <form action="procesar_solicitud.php" method="POST" class="action-buttons">
                                <input type="hidden" name="solicitud_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="accion" value="aprobar" class="btn btn-success btn-sm">
                                    Aprobar como Jefe
                                </button>
                                <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm">
                                    Rechazar
                                </button>
                                <textarea name="comentario" placeholder="Comentario (opcional)"></textarea>
                            </form>
                        </td>
                        <?php elseif ($_SESSION['role'] === 'Administrador' && $row['estado'] === 'Aprobada por Jefe'): ?>
                        <td>
                            <form action="procesar_solicitud.php" method="POST" class="action-buttons">
                                <input type="hidden" name="solicitud_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="accion" value="aprobar" class="btn btn-success btn-sm">
                                    Aprobar Final
                                </button>
                                <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm">
                                    Rechazar
                                </button>
                                <textarea name="comentario" placeholder="Comentario (opcional)"></textarea>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<style>
.user-info-menu {
    display: flex;
    align-items: center;
    gap: 20px;
}
/* .user-avatar img { ... }  Eliminado */
.user-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.user-actions {
    display: flex;
    gap: 10px;
}
.btn-info {
    background: #17a2b8;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
}
.btn-info:hover {
    background: #138496;
}
.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    justify-content: center;
}
.tab-btn {
    background: #f1f1f1;
    border: none;
    padding: 10px 24px;
    border-radius: 20px 20px 0 0;
    font-size: 16px;
    cursor: pointer;
    color: #333;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: background 0.2s, color 0.2s;
}
.tab-btn.active {
    background: #17a2b8;
    color: #fff;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(23,162,184,0.15);
}
.tab-btn:hover {
    background: #e0e0e0;
    color: #138496;
}
.chat-container-whatsapp {
    background: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    padding: 20px;
    max-width: 500px;
    margin: 0 auto 30px auto;
}
.chat-messages-whatsapp {
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
    border: 1px solid #e0e0e0;
}
.bubble {
    max-width: 70%;
    margin-bottom: 10px;
    padding: 10px 16px;
    border-radius: 18px;
    position: relative;
    font-size: 15px;
    word-break: break-word;
    box-shadow: 0 1px 3px rgba(0,0,0,0.07);
}
.bubble.mine {
    background: #d1f7c4;
    margin-left: auto;
    text-align: right;
}
.bubble.theirs {
    background: #e9ecef;
    margin-right: auto;
    text-align: left;
}
.bubble-meta {
    font-size: 11px;
    color: #888;
    margin-top: 2px;
}
.chat-form-whatsapp {
    display: flex;
    gap: 8px;
    align-items: center;
}
.chat-form-whatsapp textarea {
    flex: 1;
    border-radius: 8px;
    border: 1px solid #ccc;
    padding: 8px;
    resize: none;
}
.chat-form-whatsapp button {
    background: #17a2b8;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    cursor: pointer;
    font-weight: bold;
}
.chat-form-whatsapp button:hover {
    background: #138496;
}
.file-label {
    cursor: pointer;
    font-size: 20px;
}

/* Restaurar diseño compacto y centrado del formulario de nueva solicitud */
.form-container {
    background: transparent; /* mantener fondo de la página */
    padding: 10px 0 20px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px 12px;
    justify-content: center;
    align-items: center;
}
.form-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.form-row label {
    font-size: 13px;
    font-weight: 600;
    color: #222;
}
.form-row input[type="date"], .form-row input[type="time"], .form-row select, .form-row input[type="text"] {
    width: 140px;
    padding: 6px 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}
.form-row textarea {
    width: 300px;
    height: 50px;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    resize: vertical;
}
.form-group input[type="file"] {
    display: inline-block;
}
.btn-primary {
    background: #ffbed0; /* tono suave similar a la paleta rosa */
    border: 1px solid #f39fb1;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}
.btn-primary:hover { opacity: 0.95; }

/* Responsive: en pantallas pequeñas apilar campos */
@media (max-width: 700px) {
    .form-container { gap:10px; }
    .form-row input[type="date"], .form-row input[type="time"], .form-row select { width: 180px; }
    .form-row textarea { width: 90%; }
}
</style>
