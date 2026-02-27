<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
// Requerir sesión iniciada, sin forzar rol Administrador aquí
if (!isset($_SESSION['user_name']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}
?>
<?php
// Sin bloqueos, cualquier usuario puede ingresar
include 'connection.php';

$con = connection();

// Modifica la consulta al inicio del archivo para traer información de contratos
$sql = "SELECT u.*, c.tipo_contrato, c.tipo_cargo as cargo_contrato, c.horas, c.renovacion
    FROM users u
    LEFT JOIN contratos c ON u.numero_cedula = c.identificacion
    WHERE u.estado = 'Activo'";
$query = mysqli_query($con, $sql);

if (!$query) {
    die('Error en la consulta: ' . mysqli_error($con));
}

$sql_inactivos = "SELECT u.*, 
                  c.tipo_contrato, 
                  c.tipo_cargo as cargo_contrato, 
                  c.horas, 
                  c.renovacion,
                  c.fecha_retiro
                  FROM users u 
                  LEFT JOIN contratos c ON u.numero_cedula = c.identificacion 
                  WHERE u.estado = 'Inactivo'";
$query_inactivos = mysqli_query($con, $sql_inactivos);

// Al inicio del archivo, después de la conexión
$seccion_actual = $_GET['seccion'] ?? 'inicio';

// Función para verificar si una sección está activa
function is_seccion_active($seccion) {
    global $seccion_actual;
    return $seccion === $seccion_actual ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="CSS/style.css" rel="stylesheet">
    <title>Users CRUD</title>
    <style>
        .menu {
            margin: 20px;
            text-align: center;
        }
        .menu button {
            padding: 10px 20px;
            margin: 0 10px;
            font-size: 16px;
            cursor: pointer;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        .table-scroll {
            display: inline-block;
            min-width: 100%;
        }
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

        /* Badge styles */
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
    </style>
</head>
<body>
    <div class="header" style="display: flex; align-items: center;">
    <img src="img/logo.jpg" alt="logo.jpg" style="height:90px; width:180px; object-fit:contain; margin-right:0px;">        
    <h1>Sistema de Gestión de Usuarios</h1>
        <div class="user-info">
            <?php if(isset($_SESSION['user_name'])): ?>
                <span class="user-welcome">Bienvenido, <?= $_SESSION['user_name'] ?></span>
                <span class="user-role">Rol: <?= $_SESSION['role'] ?></span>
                <a href="logout.php" class="btn btn-danger" style="margin-left:15px;">Cerrar sesión</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="menu">
        <?php
        $secciones = ['inicio', 'crear', 'registrados', 'inactivos', 'solicitudes', 'contratos', 'certificado', 'incapacidades', 'perfil'];
        $nombres = [
            'inicio' => 'Inicio',
            'crear' => 'Crear Usuario',
            'registrados' => 'Usuarios Activos',
            'inactivos' => 'Usuarios Inactivos',
            'solicitudes' => 'Solicitudes',
            'contratos' => 'Contratos',
            'certificado' => 'Solicitud Certificado',
            'incapacidades' => 'Incapacidades',
            'perfil' => 'Perfil'
        ];
        
        foreach($secciones as $seccion) {
            $clase = ($seccion === $seccion_actual) ? 'menu-btn active' : 'menu-btn';
            echo "<a href='?seccion={$seccion}' class='{$clase}'>{$nombres[$seccion]}</a>";
        }
        ?>
    </div>

    <!-- Sección: Inicio -->
    <div id="inicio" class="section <?= ($seccion_actual === 'inicio') ? 'active' : '' ?>">
        <div class="welcome-container">
            <h2>¡Bienvenido al Sistema de Gestión!</h2>
            <div class="welcome-content">
                <p>Usuario: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>
                <p>Rol: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>
                <div class="welcome-actions">
                    <p>Como administrador, puedes:</p>
                    <ul>
                        <li>Crear, editar y eliminar usuarios</li>
                        <li>Gestionar usuarios activos e inactivos</li>
                        <li>Gestionar solicitudes de empleados (permisos, licencias, certificados)</li>
                        <li>Gestionar contratos laborales</li>
                        <li>Gestionar incapacidades (aprobar/rechazar)</li>
                        <li>Ver reportes y estadísticas</li>
                        <li>Exportar datos de empleados</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Crear usuario -->
    <div id="crear" class="users-form section <?= ($seccion_actual === 'crear') ? 'active' : '' ?>">
        <h1>Crear usuario</h1>
        <form action="insert_user.php" method="POST">
            <input type="text" name="numero_cedula" placeholder="Número de Cédula de Ciudadanía" required>
            <input type="text" name="nombres_completos" placeholder="Nombres y Apellidos Completos" required>
            <input type="text" name="sede_laborar" placeholder="Sede a Laborar">
            <input type="text" name="cargo" placeholder="Cargo">
            <input type="tel" name="telefono_personal" placeholder="Número de Teléfono Personal">
            <input type="email" name="correo_electronico" placeholder="Correo Electrónico Personal">
            <input type="text" name="eps" placeholder="EPS">
            <input type="text" name="fondo_pensiones" placeholder="Fondo de Pensiones">
            <input type="text" name="fondo_cesantias" placeholder="Fondo de Cesantías">
            <input type="text" name="nivel_educativo" placeholder="Nivel Educativo">
            <input type="text" name="profesion" placeholder="Profesión">
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            <input type="text" name="lugar_nacimiento" placeholder="Lugar de Nacimiento">
            <input type="text" name="ciudad_residencia" placeholder="Ciudad de Residencia">
            <input type="text" name="direccion_residencia" placeholder="Dirección de Residencia">
            <input type="text" name="barrio" placeholder="Barrio">
            <input type="text" name="grupo_rh" placeholder="Grupo RH">
            <input type="text" name="contacto_emergencia_nombre" placeholder="Nombre Persona Contacto en Caso de Emergencia">
            <input type="tel" name="contacto_emergencia_telefono" placeholder="Número Persona Contacto">
            <input type="text" name="estado_civil" placeholder="Estado Civil">
            <input type="text" name="tiene_hijos" placeholder="Tiene Hijos">
            <input type="text" name="hijo1_nombre_fecha" placeholder="Nombres y Fecha de Nacimiento Hijo 1">
            <input type="text" name="hijo2_nombre_fecha" placeholder="Nombres y Fecha de Nacimiento Hijo 2">
            <input type="text" name="hijo3_nombre_fecha" placeholder="Nombres y Fecha de Nacimiento Hijo 3">
            <input type="text" name="comentarios" placeholder="Comentarios">
            <input type="number" name="puntuacion" placeholder="Puntuación">
            <input type="email" name="email_adicional" placeholder="Dirección de Correo Electrónico">
            <div class="form-group">
                <label for="fecha_ingreso">Fecha de Ingreso:</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>
            </div>
            <input type="text" name="estado_nomina" placeholder="Estado Nómina">
            <select name="role" required>
                <option value="">Seleccionar Rol</option>
                <option value="Empleado">Empleado</option>
                <option value="Jefe inmediato">Jefe inmediato</option>
                <option value="Administrador">Administrador</option>
            </select>
            <input type="text" name="tipo_contrato" placeholder="Tipo de Contrato">
            <input type="text" name="cargo_contrato" placeholder="Cargo en Contrato">
            <input type="text" name="horas" placeholder="Horas Semanales">
            <div class="form-group">
                <label for="renovacion">Fecha de Renovación:</label>
                <input type="date" id="renovacion" name="renovacion">
            </div>
            <input type="submit" value="Agregar">
        </form>
    </div>

    <!-- Sección: Usuarios registrados -->
    <div id="registrados" class="section <?= ($seccion_actual === 'registrados') ? 'active' : '' ?>">
        <h2>Usuarios registrados</h2>
        <a href="export_empleados.php" class="btn btn-success" style="margin-bottom:15px;display:inline-block;">Descargar empleados (CSV)</a>
        <!-- Para la tabla de usuarios registrados -->
        <div class="table-container">
            <div class="table-horizontal-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Sede</th>
                            <th>Cargo</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>EPS</th>
                            <th>Fondo Pensiones</th>
                            <th>Fondo Cesantías</th>
                            <th>Nivel Educativo</th>
                            <th>Profesión</th>
                            <th>Fecha Nacimiento</th>
                            <th>Lugar Nacimiento</th>
                            <th>Ciudad Residencia</th>
                            <th>Dirección Residencia</th>
                            <th>Barrio</th>
                            <th>Grupo RH</th>
                            <th>Contacto Emergencia</th>
                            <th>Teléfono Emergencia</th>
                            <th>Estado Civil</th>
                            <th>Tiene Hijos</th>
                            <th>Hijo 1</th>
                            <th>Hijo 2</th>
                            <th>Hijo 3</th>
                            <th>Comentarios</th>
                            <th>Puntuación</th>
                            <th>Fecha Ingreso</th>
                            <th>Estado Nómina</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Tipo Contrato</th>
                            <th>Cargo Contrato</th>
                            <th>Horas Semanales</th>
                            <th>Fecha Renovación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_array($query)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['numero_cedula'] ?></td>
                                <td><?= $row['nombres_completos'] ?></td>
                                <td><?= $row['sede_laborar'] ?></td>
                                <td><?= $row['cargo'] ?></td>
                                <td><?= $row['telefono_personal'] ?></td>
                                <td><?= $row['correo_electronico'] ?></td>
                                <td><?= $row['eps'] ?></td>
                                <td><?= $row['fondo_pensiones'] ?></td>
                                <td><?= $row['fondo_cesantias'] ?></td>
                                <td><?= $row['nivel_educativo'] ?></td>
                                <td><?= $row['profesion'] ?></td>
                                <td><?= $row['fecha_nacimiento'] ?></td>
                                <td><?= $row['lugar_nacimiento'] ?></td>
                                <td><?= $row['ciudad_residencia'] ?></td>
                                <td><?= $row['direccion_residencia'] ?></td>
                                <td><?= $row['barrio'] ?></td>
                                <td><?= $row['grupo_rh'] ?></td>
                                <td><?= $row['contacto_emergencia_nombre'] ?></td>
                                <td><?= $row['contacto_emergencia_telefono'] ?></td>
                                <td><?= $row['estado_civil'] ?></td>
                                <td><?= $row['tiene_hijos'] ?></td>
                                <td><?= $row['hijo1_nombre_fecha'] ?></td>
                                <td><?= $row['hijo2_nombre_fecha'] ?></td>
                                <td><?= $row['hijo3_nombre_fecha'] ?></td>
                                <td><?= $row['comentarios'] ?></td>
                                <td><?= $row['puntuacion'] ?></td>
                                <td><?= $row['fecha_ingreso'] ?></td>
                                <td><?= $row['estado_nomina'] ?></td>
                                <td><?= $row['role'] ?></td>
                                <td>
                                    <span class="badge <?= strtolower($row['estado']) ?>">
                                        <?= $row['estado'] ?>
                                    </span>
                                </td>
                                <td><?= $row['tipo_contrato'] ? htmlspecialchars($row['tipo_contrato']) : 'No asignado' ?></td>
                                <td><?= $row['cargo_contrato'] ? htmlspecialchars($row['cargo_contrato']) : 'No asignado' ?></td>
                                <td><?= $row['horas'] ? htmlspecialchars($row['horas']) : 'N/A' ?></td>
                                <td><?= $row['renovacion'] ? date('d/m/Y', strtotime($row['renovacion'])) : 'No aplica' ?></td>
                                <td class="action-buttons">
                                    <a href="update.php?id=<?= $row['id'] ?>" class="users-table--edit">Editar</a>
                                    <button onclick="cambiarEstado(<?= $row['id'] ?>, '<?= $row['estado'] ?>')" 
                                            class="users-table--status <?= $row['estado'] === 'Inactivo' ? 'inactive' : 'active' ?>">
                                        <?= $row['estado'] === 'Inactivo' ? 'Activar' : 'Inactivar' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección: Usuarios inactivos -->
    <div id="inactivos" class="section <?= ($seccion_actual === 'inactivos') ? 'active' : '' ?>">
        <h2>Usuarios Inactivos</h2>
        <!-- Para la tabla de usuarios inactivos -->
        <div class="table-container">
            <div class="table-horizontal-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Sede</th>
                            <th>Cargo</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>EPS</th>
                            <th>Fondo Pensiones</th>
                            <th>Fondo Cesantías</th>
                            <th>Nivel Educativo</th>
                            <th>Profesión</th>
                            <th>Fecha Nacimiento</th>
                            <th>Lugar Nacimiento</th>
                            <th>Ciudad Residencia</th>
                            <th>Dirección Residencia</th>
                            <th>Barrio</th>
                            <th>Grupo RH</th>
                            <th>Contacto Emergencia</th>
                            <th>Teléfono Emergencia</th>
                            <th>Estado Civil</th>
                            <th>Tiene Hijos</th>
                            <th>Hijo 1</th>
                            <th>Hijo 2</th>
                            <th>Hijo 3</th>
                            <th>Comentarios</th>
                            <th>Puntuación</th>
                            <th>Fecha Ingreso</th>
                            <th>Estado Nómina</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Tipo Contrato</th>
                            <th>Cargo Contrato</th> 
                            <th>Horas Semanales</th>
                            <th>Fecha Renovación</th>
                            <th>Fecha Retiro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_array($query_inactivos)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['numero_cedula'] ?></td>
                                <td><?= $row['nombres_completos'] ?></td>
                                <td><?= $row['sede_laborar'] ?></td>
                                <td><?= $row['cargo'] ?></td>
                                <td><?= $row['telefono_personal'] ?></td>
                                <td><?= $row['correo_electronico'] ?></td>
                                <td><?= $row['eps'] ?></td>
                                <td><?= $row['fondo_pensiones'] ?></td>
                                <td><?= $row['fondo_cesantias'] ?></td>
                                <td><?= $row['nivel_educativo'] ?></td>
                                <td><?= $row['profesion'] ?></td>
                                <td><?= $row['fecha_nacimiento'] ?></td>
                                <td><?= $row['lugar_nacimiento'] ?></td>
                                <td><?= $row['ciudad_residencia'] ?></td>
                                <td><?= $row['direccion_residencia'] ?></td>
                                <td><?= $row['barrio'] ?></td>
                                <td><?= $row['grupo_rh'] ?></td>
                                <td><?= $row['contacto_emergencia_nombre'] ?></td>
                                <td><?= $row['contacto_emergencia_telefono'] ?></td>
                                <td><?= $row['estado_civil'] ?></td>
                                <td><?= $row['tiene_hijos'] ?></td>
                                <td><?= $row['hijo1_nombre_fecha'] ?></td>
                                <td><?= $row['hijo2_nombre_fecha'] ?></td>
                                <td><?= $row['hijo3_nombre_fecha'] ?></td>
                                <td><?= $row['comentarios'] ?></td>
                                <td><?= $row['puntuacion'] ?></td>
                                <td><?= $row['fecha_ingreso'] ?></td>
                                <td><?= $row['estado_nomina'] ?></td>
                                <td><?= $row['role'] ?></td>
                                <td>
                                    <span class="badge badge-inactivo">
                                        <?= $row['estado'] ?>
                                    </span>
                                </td>
                                <!-- Nuevas columnas con información del contrato -->
                                <td><?= $row['tipo_contrato'] ? htmlspecialchars($row['tipo_contrato']) : 'No asignado' ?></td>
                                <td><?= $row['cargo_contrato'] ? htmlspecialchars($row['cargo_contrato']) : 'No asignado' ?></td>
                                <td><?= $row['horas'] ? htmlspecialchars($row['horas']) : 'N/A' ?></td>
                                <td><?= $row['renovacion'] ? date('d/m/Y', strtotime($row['renovacion'])) : 'No aplica' ?></td>
                                <td><?= !empty($row['fecha_retiro']) ? date('d/m/Y', strtotime($row['fecha_retiro'])) : '-' ?></td>
                                <td class="action-buttons">
                                    <a href="update.php?id=<?= $row['id'] ?>" class="users-table--edit">Editar</a>
                                    <button onclick="cambiarEstado(<?= $row['id'] ?>, '<?= $row['estado'] ?>')" 
                                            class="users-table--status inactive">
                                        Activar
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección: Solicitudes -->
    <div id="solicitudes" class="section <?= ($seccion_actual === 'solicitudes') ? 'active' : '' ?>">
        <h2>Gestión de Solicitudes</h2>
        
       
        <div class="table-container">
            <div class="table-horizontal-scroll">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Motivo</th>
                            <th>Archivo</th>
                            <th>Estado Jefe</th>
                            <th>Comentario Jefe</th>
                            <th>Estado Admin</th>
                            <th>Comentario Admin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta sin filtro de rol
                        $sql = "SELECT s.*, u.nombres_completos 
                                FROM solicitudes s 
                                JOIN users u ON s.numero_cedula = u.numero_cedula 
                                ORDER BY s.id DESC";
                        
                        $query = mysqli_query($con, $sql);
                        while($row = mysqli_fetch_array($query)):
                            // garantizar valores por defecto 'Pendiente' para evitar comparaciones vacías
                            $estado_jefe = !empty($row['estado_jefe']) ? $row['estado_jefe'] : 'Pendiente';
                            $estado_admin = !empty($row['estado_admin']) ? $row['estado_admin'] : 'Pendiente';
                            $estado_final = 'Pendiente';
                            if ($estado_jefe === 'Rechazada' || $estado_admin === 'Rechazada') {
                                $estado_final = 'Rechazada';
                            } elseif ($estado_jefe === 'Aprobada' && $estado_admin === 'Aprobada') {
                                $estado_final = 'Aprobada';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombres_completos'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['tipo_solicitud'] ?? '-') ?></td>
                            <td><?= !empty($row['fecha_inicio']) ? date('d/m/Y', strtotime($row['fecha_inicio'])) : '-' ?></td>
                            <td><?= !empty($row['fecha_fin']) ? date('d/m/Y', strtotime($row['fecha_fin'])) : '-' ?></td>
                            <td><?= htmlspecialchars($row['motivo'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($row['archivo'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['archivo']) ?>" target="_blank">Ver archivo</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($estado_jefe ?: 'pendiente') ?>">
                                    <?= $estado_jefe ?: '-' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['comentario_jefe'] ?? '-') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($estado_admin ?: 'pendiente') ?>">
                                    <?= $estado_admin ?: '-' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['comentario_admin'] ?? '-') ?></td>
                            <td>
                                <?php
                                // Mostrar el botón al admin solo cuando el jefe haya aprobado y admin esté pendiente
                                if (
                                    $_SESSION['role'] === 'Administrador' &&
                                    $estado_jefe === 'Aprobada' &&
                                    $estado_admin === 'Pendiente'
                                ): ?>
                                    <button onclick="mostrarModal(<?= $row['id'] ?>, 'admin')" class="btn btn-primary">Revisar</button>
                                <?php endif; ?>
                                <a href="eliminar_solicitud.php?id=<?= $row['id'] ?>" class="btn btn-danger">Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para inactivar usuario con fecha de retiro -->
    <div id="modalRetiro" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalRetiro').style.display='none'">&times;</span>
            <h2>Inactivar Usuario</h2>
            <form id="formRetiro" onsubmit="confirmarRetiro(event)">
                <input type="hidden" id="usuario_id" name="usuario_id">
                <div class="form-group">
                    <label for="fecha_retiro">Fecha de Retiro *</label>
                    <input type="date" id="fecha_retiro" name="fecha_retiro" required>
                </div>
                <button type="submit" class="btn btn-primary">Confirmar Inactivación</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalRetiro').style.display='none'">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para aprobar/rechazar -->
    <div id="modalRevision" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Revisar Solicitud</h2>
            <form action="procesar_solicitud.php" method="POST">
                <input type="hidden" id="solicitud_id" name="id">
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
        // Función para mostrar el modal y llenar el id de la solicitud
        function mostrarModal(id, tipo) {
            var modal = document.getElementById('modalRevision');
            var inputId = document.getElementById('solicitud_id');
            if (!modal || !inputId) return;
            inputId.value = id;
            modal.style.display = 'flex';
        }

        // Cerrar modal al hacer clic en la X o fuera del contenido
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('modalRevision');
            if (!modal) return;
            var closeBtn = modal.querySelector('.close');
            closeBtn && closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
        
        // Función para activar/inactivar usuario
        var currentSection = '<?= $seccion_actual ?>';
        function cambiarEstado(id, estado) {
            if (estado === 'Inactivo') {
                // Si ya está inactivo, solo activar
                var confirmar = confirm('¿Activar este usuario?');
                if (!confirmar) return;
                var url = 'toggle_status.php?id=' + encodeURIComponent(id) + '&seccion=' + encodeURIComponent(currentSection);
                window.location.href = url;
            } else {
                // Si está activo, mostrar modal para inactivación con fecha de retiro
                var modal = document.getElementById('modalRetiro');
                document.getElementById('usuario_id').value = id;
                // Establecer fecha actual por defecto
                var hoy = new Date().toISOString().split('T')[0];
                document.getElementById('fecha_retiro').value = hoy;
                modal.style.display = 'flex';
            }
        }
        
        function confirmarRetiro(event) {
            event.preventDefault();
            var usuarioId = document.getElementById('usuario_id').value;
            var fechaRetiro = document.getElementById('fecha_retiro').value;
            var url = 'toggle_status.php?id=' + encodeURIComponent(usuarioId) + '&fecha_retiro=' + encodeURIComponent(fechaRetiro) + '&seccion=' + encodeURIComponent(currentSection);
            window.location.href = url;
        }
        </script>

    <!-- Sección: Gestión de Contratos -->
    <div id="contratos" class="section <?= ($seccion_actual === 'contratos') ? 'active' : '' ?>">
        <h2>Gestión de Contratos</h2>
        
        <!-- Formulario de creación -->
        <div class="contracts-form">
            <h3>Crear Nuevo Contrato</h3>
            <form action="insert_contract.php" method="POST">
                <div class="form-group">
                    <label class="required">Tipo de Identificación</label>
                    <select name="tipo_identificacion" required>
                        <option value="">Seleccionar</option>
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="PA">Pasaporte</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required">Identificación</label>
                    <input type="text" name="identificacion" required>
                </div>

                <div class="form-group">
                    <label class="required">Nombres y Apellidos</label>
                    <input type="text" name="nombres_apellidos" required>
                </div>

                <div class="form-group">
                    <label class="required">Cargo</label>
                    <input type="text" name="cargo" required>
                </div>

                <div class="form-group">
                    <label class="required">Tipo de Cargo</label>
                    <select name="tipo_cargo" required>
                        <option value="">Seleccionar</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Operativo">Operativo</option>
                        <option value="Directivo">Directivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required">Horas Semanales</label>
                    <input type="number" name="horas" min="1" max="48" required>
                </div>

                <div class="form-group">
                    <label class="required">Sede</label>
                    <input type="text" name="sede" required>
                </div>

                <div class="form-group">
                    <label class="required">Tipo de Contrato</label>
                    <select name="tipo_contrato" required>
                        <option value="Prestación de Servicios" selected>Prestación de Servicios</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha de Renovación</label>
                    <input type="date" name="renovacion">
                </div>

                <input type="submit" value="Guardar Contrato">
            </form>
        </div>

        <!-- Tabla de contratos -->
        <div class="table-container">
            <h3>Contratos Registrados</h3>
            <div class="table-horizontal-scroll">
                <table class="contracts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo ID</th>
                            <th>Identificación</th>
                            <th>Nombres y Apellidos</th>
                            <th>Cargo</th>
                            <th>Tipo Cargo</th>
                            <th>Horas</th>
                            <th>Sede</th>
                            <th>Tipo Contrato</th>
                            <th>Renovación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_contratos = "SELECT * FROM contratos ORDER BY id DESC";
                        $query_contratos = mysqli_query($con, $sql_contratos);
                        while($contrato = mysqli_fetch_array($query_contratos)):
                        ?>
                        <tr>
                            <td><?= $contrato['id'] ?></td>
                            <td><?= $contrato['tipo_identificacion'] ?></td>
                            <td><?= $contrato['identificacion'] ?></td>
                            <td><?= $contrato['nombres_apellidos'] ?></td>
                            <td><?= $contrato['cargo'] ?></td>
                            <td><?= $contrato['tipo_cargo'] ?></td>
                            <td><?= $contrato['horas'] ?></td>
                            <td><?= $contrato['sede'] ?></td>
                            <td><?= $contrato['tipo_contrato'] ?></td>
                            <td><?= $contrato['renovacion'] ? date('d/m/Y', strtotime($contrato['renovacion'])) : 'No aplica' ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($contrato['estado']) ?>">
                                    <?= $contrato['estado'] ?>
                            </td>
                            <td>
                                <a href="edit_contract.php?id=<?= $contrato['id'] ?>" class="contract-edit">Editar</a>
                                <a href="toggle_contract_status.php?id=<?= $contrato['id'] ?>" 
                                   class="contract-status"
                                   onclick="return confirm('¿Estás seguro de cambiar el estado del contrato?')">
                                    <?= $contrato['estado'] === 'Activo' ? 'Inactivar' : 'Activar' ?>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección: Solicitud de Certificado (reemplaza el chat) -->
    <div id="certificado" class="section <?= ($seccion_actual === 'certificado') ? 'active' : '' ?>">
        <h2>Solicitud de Certificado</h2>
        <!-- Formulario removido: las solicitudes deben ser creadas por los empleados desde su perfil. -->
        <div class="form-container" style="max-width:600px;">
        </div>

        <hr>
        <h3>Solicitudes de Certificado recientes</h3>
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        
                        <th>Motivo</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener solicitudes de certificado desde la tabla `certificados`
                    $sql_cert = "SELECT c.*, u.nombres_completos FROM certificados c JOIN users u ON c.numero_cedula = u.numero_cedula ORDER BY c.fecha_solicitud DESC LIMIT 50";
                    $res_cert = mysqli_query($con, $sql_cert);
                    while ($r = mysqli_fetch_array($res_cert)):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nombres_completos']) ?></td>
                        <td><?= htmlspecialchars($r['tipo_certificado'] ?? 'Certificado') ?></td>
                        <td><?= !empty($r['fecha_solicitud']) ? date('d/m/Y', strtotime($r['fecha_solicitud'])) : '-' ?></td>
                        <td><?= htmlspecialchars($r['estado'] ?? 'Pendiente') ?></td>
                        <td>
                            <?php if (!empty($r['archivo'])): ?>
                                <a href="uploads/<?= htmlspecialchars($r['archivo']) ?>" target="_blank">Ver</a>
                                <br>
                                <small>Emitido por: <?= htmlspecialchars($r['emitido_por'] ?? '-') ?> <?= !empty($r['fecha_emision']) ? ' el ' . date('d/m/Y H:i', strtotime($r['fecha_emision'])) : '' ?></small>
                            <?php else: ?>
                                <!-- Formulario rápido para subir el archivo final (emitir) -->
                                <form action="emitir_certificado.php" method="POST" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center;">
                                    <input type="hidden" name="id" value="<?= intval($r['id']) ?>">
                                    <input type="file" name="archivo" required>
                                    <button type="submit" class="btn btn-sm">Enviar</button>
                                </form>
                            <?php endif; ?>

                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección: Perfil -->
    <div id="perfil" class="section <?= ($seccion_actual === 'perfil') ? 'active' : '' ?>">
        <style>
            .perfil-page-wrapper {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }

            .perfil-page-header {
                background: white;
                padding: 20px 40px;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                flex-wrap: wrap;
                gap: 20px;
            }

            .perfil-page-header-left {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .perfil-page-header h1 {
                color: #2c3e50;
                font-size: 1.8rem;
                font-weight: 600;
            }

            .perfil-page-user-info {
                display: flex;
                align-items: center;
                gap: 20px;
                flex-wrap: wrap;
            }

            .perfil-user-badge {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 500;
            }

            .perfil-role-badge {
                background: #3498db;
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 500;
            }

            .perfil-logout-btn {
                background: #e74c3c;
                color: white;
                padding: 10px 20px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
            }

            .perfil-logout-btn:hover {
                background: #c0392b;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
            }

            .perfil-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            }

            .perfil-title {
                color: #2c3e50;
                font-size: 2rem;
                margin-bottom: 10px;
                font-weight: 600;
            }

            .perfil-subtitle {
                color: #7f8c8d;
                margin-bottom: 30px;
            }

            .perfil-message {
                padding: 15px 20px;
                border-radius: 10px;
                margin-bottom: 25px;
                font-weight: 500;
                animation: slideIn 0.3s ease;
            }

            .perfil-message.success {
                background: #d4edda;
                color: #155724;
                border-left: 4px solid #28a745;
            }

            .perfil-message.error {
                background: #f8d7da;
                color: #721c24;
                border-left: 4px solid #dc3545;
            }

            .perfil-section {
                margin-bottom: 30px;
            }

            .perfil-section-title {
                color: #2c3e50;
                font-size: 1.3rem;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #ecf0f1;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .perfil-form-group {
                margin-bottom: 20px;
            }

            .perfil-form-group label {
                display: block;
                color: #34495e;
                font-weight: 500;
                margin-bottom: 8px;
                font-size: 0.95rem;
            }

            .perfil-form-group input {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 1rem;
                transition: all 0.3s ease;
                background: #f8f9fa;
            }

            .perfil-form-group input:focus {
                outline: none;
                border-color: #667eea;
                background: white;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .perfil-form-group input:hover {
                border-color: #b0b0b0;
            }

            .perfil-btn-group {
                display: flex;
                gap: 15px;
                margin-top: 30px;
                flex-wrap: wrap;
            }

            .perfil-btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 14px 30px;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                flex: 1;
                min-width: 150px;
            }

            .perfil-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            }

            @media (max-width: 768px) {
                .perfil-page-header {
                    flex-direction: column;
                    text-align: center;
                }

                .perfil-container {
                    padding: 25px;
                }

                .perfil-btn-group {
                    flex-direction: column;
                }

                .perfil-btn-primary {
                    width: 100%;
                }

                .perfil-page-user-info {
                    justify-content: center;
                }
            }
        </style>

        <div class="perfil-page-wrapper">
            <div class="perfil-page-header">
                <div class="perfil-page-header-left">
                    <img src="img/logo.jpg" alt="Logo" style="height:70px; width:140px; object-fit:contain; border-radius: 8px;">
                    <h1>Sistema de Gestión</h1>
                </div>
                <div class="perfil-page-user-info">
                    <span class="perfil-user-badge">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span class="perfil-role-badge">🔐 <?= htmlspecialchars($_SESSION['role']) ?></span>
                    <a href="logout.php" class="perfil-logout-btn">Cerrar sesión</a>
                </div>
            </div>

            <div class="perfil-container">
                <h2 class="perfil-title">Mi Perfil</h2>
                <p class="perfil-subtitle">Actualiza tu información personal</p>

                <?php
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT * FROM users WHERE id = '$user_id'";
                $query = mysqli_query($con, $sql);
                $user = mysqli_fetch_assoc($query);
                $message = '';
                $message_type = '';
                
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
                    $numero_cedula = mysqli_real_escape_string($con, $_POST['numero_cedula']);
                    $nombres_completos = mysqli_real_escape_string($con, $_POST['nombres_completos']);
                    $telefono_personal = mysqli_real_escape_string($con, $_POST['telefono_personal']);
                    $correo_electronico = mysqli_real_escape_string($con, $_POST['correo_electronico']);

                    $check_sql = "SELECT id FROM users WHERE numero_cedula = '$numero_cedula' AND id != '$user_id'";
                    $check_query = mysqli_query($con, $check_sql);
                    if (mysqli_num_rows($check_query) > 0) {
                        $message = "El número de cédula ya está en uso.";
                        $message_type = 'error';
                    } else {
                        $update_sql = "UPDATE users SET numero_cedula = '$numero_cedula', nombres_completos = '$nombres_completos', telefono_personal = '$telefono_personal', correo_electronico = '$correo_electronico' WHERE id = '$user_id'";
                        if (mysqli_query($con, $update_sql)) {
                            $message = "Perfil actualizado exitosamente.";
                            $message_type = 'success';
                            $_SESSION['user_name'] = $nombres_completos;
                            $_SESSION['numero_cedula'] = $numero_cedula;
                            $query = mysqli_query($con, $sql);
                            $user = mysqli_fetch_assoc($query);
                        } else {
                            $message = "Error al actualizar el perfil.";
                            $message_type = 'error';
                        }
                    }
                }
                ?>

                <?php if ($message): ?>
                    <div class="perfil-message <?= $message_type ?>">
                        <?= $message_type === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="perfil-section">
                        <h3 class="perfil-section-title">
                            <span>📋</span> Información Personal
                        </h3>

                        <div class="perfil-form-group">
                            <label for="numero_cedula">Número de Cédula *</label>
                            <input type="text" id="numero_cedula" name="numero_cedula" 
                                   value="<?= htmlspecialchars($user['numero_cedula']) ?>" 
                                   required pattern="[0-9]+" 
                                   title="Solo se permiten números">
                        </div>

                        <div class="perfil-form-group">
                            <label for="nombres_completos">Nombres Completos *</label>
                            <input type="text" id="nombres_completos" name="nombres_completos" 
                                   value="<?= htmlspecialchars($user['nombres_completos']) ?>" 
                                   required minlength="3">
                        </div>

                        <div class="perfil-form-group">
                            <label for="telefono_personal">Teléfono Personal</label>
                            <input type="tel" id="telefono_personal" name="telefono_personal" 
                                   value="<?= htmlspecialchars($user['telefono_personal']) ?>" 
                                   pattern="[0-9]{10}" 
                                   title="Ingrese un número de 10 dígitos">
                        </div>

                        <div class="perfil-form-group">
                            <label for="correo_electronico">Correo Electrónico</label>
                            <input type="email" id="correo_electronico" name="correo_electronico" 
                                   value="<?= htmlspecialchars($user['correo_electronico']) ?>">
                        </div>
                    </div>

                    <div class="perfil-btn-group">
                        <button type="submit" name="update_profile" class="perfil-btn-primary">
                            💾 Guardar Cambios
                        </button>
                    </div>
                </form>

                <!-- Información de contacto del administrador -->
                <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                    <h3 style="color: #2c3e50; margin-bottom: 10px;">¿Necesitas Ayuda?</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 14px;">
                        Para soporte técnico o consultas, contacta al administrador:<br>
                        <strong>Email:</strong> admin@clinicadelseno.com | <strong>Teléfono:</strong> +57 123 456 7890
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Incapacidades -->
    <div id="incapacidades" class="section <?= ($seccion_actual === 'incapacidades') ? 'active' : '' ?>">
        <h2>Gestión de Incapacidades</h2>
        
        <?php
        if (isset($_GET['mensaje'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
        <?php if ($_SESSION['role'] === 'Administrador'): ?>
            <!-- Vista para Administrador: Revisar y autorizar -->
            <div class="table-container">
                <h3>Incapacidades Pendientes</h3>
                <div class="table-horizontal-scroll">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
                                <th>Fecha Inicio</th>
                                <th>Hora Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Hora Fin</th>
                                <th>Motivo</th>
                                <th>Archivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_incap = "SELECT i.*, u.nombres_completos FROM incapacidades i JOIN users u ON i.numero_cedula = u.numero_cedula WHERE i.estado = 'pendiente' ORDER BY i.fecha_solicitud DESC";
                            $res_incap = mysqli_query($con, $sql_incap);
                            while ($incap = mysqli_fetch_array($res_incap)):
                            ?>
                            <tr>
                                <td><?= $incap['id'] ?></td>
                                <td><?= htmlspecialchars($incap['nombres_completos']) ?></td>
                                <td><?= $incap['fecha_inicio'] ?></td>
                                <td><?= $incap['hora_inicio'] ?? '-' ?></td>
                                <td><?= $incap['fecha_fin'] ?></td>
                                <td><?= $incap['hora_fin'] ?? '-' ?></td>
                                <td><?= htmlspecialchars($incap['motivo']) ?></td>
                                <td>
                                    <?php if ($incap['archivo_incapacidad']): ?>
                                        <a href="descargar_incapacidad.php?archivo=<?= urlencode($incap['archivo_incapacidad']) ?>" class="btn btn-info btn-sm" title="Descargar archivo">📥 Descargar</a>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-warning">Pendiente</span></td>
                                <td>
                                    <button onclick="mostrarModalIncapacidad(<?= $incap['id'] ?>, 'aprobar')" class="btn btn-success btn-sm">Aprobar</button>
                                    <button onclick="mostrarModalIncapacidad(<?= $incap['id'] ?>, 'rechazar')" class="btn btn-danger btn-sm">Rechazar</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="table-container">
                <h3>Todas las Incapacidades</h3>
                <div class="table-horizontal-scroll">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
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
                            <?php
                            $sql_all_incap = "SELECT i.*, u.nombres_completos FROM incapacidades i JOIN users u ON i.numero_cedula = u.numero_cedula ORDER BY i.fecha_solicitud DESC";
                            $res_all_incap = mysqli_query($con, $sql_all_incap);
                            while ($incap = mysqli_fetch_array($res_all_incap)):
                            ?>
                            <tr>
                                <td><?= $incap['id'] ?></td>
                                <td><?= htmlspecialchars($incap['nombres_completos']) ?></td>
                                <td><?= $incap['fecha_inicio'] ?></td>
                                <td><?= $incap['hora_inicio'] ?? '-' ?></td>
                                <td><?= $incap['fecha_fin'] ?></td>
                                <td><?= $incap['hora_fin'] ?? '-' ?></td>
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
                                <td><?= htmlspecialchars($incap['comentario_admin'] ?? '') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Vista para Empleado: Crear solicitud -->
            <div class="form-container">
                <h3>Solicitar Incapacidad</h3>
                <form action="crear_incapacidad.php" method="POST">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin *</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="form-group">
                        <label for="motivo">Motivo *</label>
                        <textarea id="motivo" name="motivo" required rows="4" placeholder="Describe el motivo de la incapacidad"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                </form>
            </div>
            
            <div class="table-container">
                <h3>Mis Incapacidades</h3>
                <div class="table-horizontal-scroll">
                    <table class="users-table">
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
                            <?php
                            $cedula = $_SESSION['numero_cedula'];
                            $sql_my_incap = "SELECT * FROM incapacidades WHERE numero_cedula = '$cedula' ORDER BY fecha_solicitud DESC";
                            $res_my_incap = mysqli_query($con, $sql_my_incap);
                            while ($incap = mysqli_fetch_array($res_my_incap)):
                            ?>
                            <tr>
                                <td><?= $incap['id'] ?></td>
                                <td><?= $incap['fecha_inicio'] ?></td>
                                <td><?= $incap['hora_inicio'] ?? '-' ?></td>
                                <td><?= $incap['fecha_fin'] ?></td>
                                <td><?= $incap['hora_fin'] ?? '-' ?></td>
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
                                <td><?= htmlspecialchars($incap['comentario_admin'] ?? '') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para procesar incapacidad -->
    <div id="modalIncapacidad" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Procesar Incapacidad</h2>
            <form action="procesar_incapacidad.php" method="POST">
                <input type="hidden" id="incapacidad_id" name="id">
                <input type="hidden" id="accion" name="accion">
                <div class="form-group">
                    <label>Comentario</label>
                    <textarea name="comentario" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <script>
        // Función para mostrar el modal de incapacidad
        function mostrarModalIncapacidad(id, accion) {
            var modal = document.getElementById('modalIncapacidad');
            var inputId = document.getElementById('incapacidad_id');
            var inputAccion = document.getElementById('accion');
            if (!modal || !inputId || !inputAccion) return;
            inputId.value = id;
            inputAccion.value = accion;
            modal.style.display = 'flex';
        }

        // Cerrar modal al hacer clic en la X o fuera del contenido
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('modalIncapacidad');
            if (!modal) return;
            var closeBtn = modal.querySelector('.close');
            closeBtn && closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>



</body>
</html>
