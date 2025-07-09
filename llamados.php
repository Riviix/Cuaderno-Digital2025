<?php 
$pageTitle = 'Llamados de Atención - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'includes/auto_seed.php';

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$estudiante_id = $_GET['estudiante'] ?? '';

// Procesar formulario de nuevo llamado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_llamado'])) {
    $estudiante_id = $_POST['estudiante_id'];
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $descripcion = $_POST['descripcion']; // Descripción del hecho
    $sancion = $_POST['sancion'] ?? '';
    $usuario_id = $_SESSION['user_id'];

    try {
        $db->query("
            INSERT INTO llamados_atencion (estudiante_id, fecha, motivo, sancion, observaciones, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$estudiante_id, $fecha, $motivo, $sancion, $descripcion, $usuario_id]);
        
        $success_message = "Llamado de atención registrado correctamente";
    } catch (Exception $e) {
        $error_message = "Error al registrar el llamado: " . $e->getMessage();
    }
}

// Filtros para el listado
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$motivo_filter = $_GET['motivo'] ?? '';

// Construir consulta con filtros
$where_conditions = ["1=1"];
$params = [];

if ($fecha_desde) {
    $where_conditions[] = "l.fecha >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = "l.fecha <= ?";
    $params[] = $fecha_hasta;
}

if ($motivo_filter) {
    $where_conditions[] = "l.motivo LIKE ?";
    $params[] = "%$motivo_filter%";
}

if ($estudiante_id) {
    $where_conditions[] = "l.estudiante_id = ?";
    $params[] = $estudiante_id;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener llamados
$llamados = $db->fetchAll("
    SELECT l.*, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre as especialidad,
           u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM llamados_atencion l
    JOIN estudiantes e ON l.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    WHERE $where_clause
    ORDER BY l.fecha DESC, e.apellido, e.nombre
    LIMIT 200
", $params);

// Obtener estudiantes para formulario
$estudiantes = $db->fetchAll("
    SELECT e.id, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre as especialidad
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE e.activo = 1
    ORDER BY e.apellido, e.nombre
");

// Estadísticas
$total_llamados = $db->fetch("
    SELECT COUNT(*) as total FROM llamados_atencion l
    JOIN estudiantes e ON l.estudiante_id = e.id
    WHERE $where_clause
", $params)['total'];

$llamados_hoy = $db->fetch("
    SELECT COUNT(*) as total FROM llamados_atencion WHERE fecha = CURDATE()
")['total'];

$llamados_con_sancion = $db->fetch("
    SELECT COUNT(*) as total FROM llamados_atencion l
    JOIN estudiantes e ON l.estudiante_id = e.id
    WHERE $where_clause AND l.sancion IS NOT NULL AND l.sancion != ''
", $params)['total'];
?>

<main class="main-content">
<section class="llamados-section">
    <div class="section-header">
        <h2>Gestión de Llamados de Atención</h2>
        <a href="llamados.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Llamado
        </a>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="stats-cards grid-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_llamados; ?></h3>
                <p>Total Llamados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $llamados_hoy; ?></h3>
                <p>Llamados Hoy</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $llamados_con_sancion; ?></h3>
                <p>Llamados con Sanción</p>
            </div>
        </div>
    </div>

    <!-- Formulario de nuevo llamado -->
    <?php if ($action === 'nuevo' || $estudiante_id): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Nuevo Llamado de Atención</h3>
        </div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="estudiante_id">Estudiante:</label>
                    <select name="estudiante_id" id="estudiante_id" required>
                        <option value="">Seleccionar estudiante</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <option value="<?php echo $est['id']; ?>" 
                                <?php echo $estudiante_id == $est['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre'] . ' - ' . $est['anio'] . '° ' . $est['division'] . ' ' . $est['especialidad']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="motivo">Motivo:</label>
                    <select name="motivo" id="motivo" required>
                        <option value="">Seleccionar motivo</option>
                        <option value="Amonestación verbal">Amonestación verbal</option>
                        <option value="Amonestación escrita">Amonestación escrita</option>
                        <option value="Suspensión 1 día">Suspensión 1 día</option>
                        <option value="Suspensión 3 días">Suspensión 3 días</option>
                        <option value="Suspensión 5 días">Suspensión 5 días</option>
                        <option value="Suspensión 10 días">Suspensión 10 días</option>
                        <option value="Expulsión">Expulsión</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" id="descripcion" placeholder="Describir detalladamente lo ocurrido" required></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sancion">Sanción Aplicada:</label>
                    <select name="sancion" id="sancion">
                        <option value="">Sin sanción</option>
                        <option value="Amonestación verbal">Amonestación verbal</option>
                        <option value="Amonestación escrita">Amonestación escrita</option>
                        <option value="Suspensión 1 día">Suspensión 1 día</option>
                        <option value="Suspensión 3 días">Suspensión 3 días</option>
                        <option value="Suspensión 5 días">Suspensión 5 días</option>
                        <option value="Suspensión 10 días">Suspensión 10 días</option>
                        <option value="Expulsión">Expulsión</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="registrar_llamado" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Llamado
                </button>
                <a href="llamados.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros para el listado -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="filters-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_desde">Fecha Desde:</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" 
                           value="<?php echo htmlspecialchars($fecha_desde); ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_hasta">Fecha Hasta:</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" 
                           value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                </div>
                
                <div class="form-group">
                    <label for="motivo_filter">Motivo:</label>
                    <input type="text" name="motivo" id="motivo_filter" 
                           value="<?php echo htmlspecialchars($motivo_filter); ?>" 
                           placeholder="Buscar por motivo">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="llamados.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de llamados -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Llamados de Atención Registrados</h3>
            <div class="header-actions">
                <a href="llamados.php?action=exportar" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Exportar
                </a>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estudiante</th>
                        <th>Curso</th>
                        <th>Motivo</th>
                        <th>Sanción</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($llamados as $llamado): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($llamado['fecha'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($llamado['apellido'] . ', ' . $llamado['nombre']); ?></strong><br>
                            <small>DNI: <?php echo htmlspecialchars($llamado['dni']); ?></small>
                        </td>
                        <td><?php echo $llamado['anio'] . '° ' . $llamado['division'] . ' - ' . $llamado['especialidad']; ?></td>
                        <td><?php echo htmlspecialchars($llamado['motivo']); ?></td>
                        <td>
                            <?php if ($llamado['sancion']): ?>
                                <span class="status status-warning"><?php echo htmlspecialchars($llamado['sancion']); ?></span>
                            <?php else: ?>
                                <span class="status status-inactive">Sin sanción</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($llamado['usuario_apellido'] . ', ' . $llamado['usuario_nombre']); ?></td>
                        <td>
                            <a href="llamado_editar.php?id=<?php echo $llamado['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="llamado_ver.php?id=<?php echo $llamado['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($llamados)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No hay llamados de atención registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.llamados-section {
    max-width: 1200px;
    margin: 0 auto;
}

.header-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

</main>
<?php include 'includes/footer.php'; ?> 