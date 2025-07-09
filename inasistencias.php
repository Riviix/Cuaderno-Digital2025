<?php 
$pageTitle = 'Inasistencias - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'includes/auto_seed.php';

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$estudiante_id = $_GET['estudiante'] ?? '';
$curso_id = $_GET['curso'] ?? '';

// Procesar formulario de nueva inasistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_inasistencia'])) {
    $estudiante_id = $_POST['estudiante_id'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $justificada = isset($_POST['justificada']) ? 1 : 0;
    $motivo = $_POST['motivo'] ?? '';
    $certificado_medico = isset($_POST['certificado_medico']) ? 1 : 0;
    $observaciones = $_POST['observaciones'] ?? '';
    $usuario_id = $_SESSION['user_id'];

    try {
        $db->query("
            INSERT INTO inasistencias (estudiante_id, fecha, tipo, justificada, motivo, certificado_medico, observaciones, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [$estudiante_id, $fecha, $tipo, $justificada, $motivo, $certificado_medico, $observaciones, $usuario_id]);
        
        $success_message = "Inasistencia registrada correctamente";
    } catch (Exception $e) {
        $error_message = "Error al registrar la inasistencia: " . $e->getMessage();
    }
}

// Filtros para el listado
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01'); // Primer día del mes actual
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d'); // Hoy
$tipo_filter = $_GET['tipo'] ?? '';
$justificada_filter = $_GET['justificada'] ?? '';

// Construir consulta con filtros
$where_conditions = ["1=1"];
$params = [];

if ($fecha_desde) {
    $where_conditions[] = "i.fecha >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = "i.fecha <= ?";
    $params[] = $fecha_hasta;
}

if ($tipo_filter) {
    $where_conditions[] = "i.tipo = ?";
    $params[] = $tipo_filter;
}

if ($justificada_filter !== '') {
    $where_conditions[] = "i.justificada = ?";
    $params[] = $justificada_filter;
}

if ($estudiante_id) {
    $where_conditions[] = "i.estudiante_id = ?";
    $params[] = $estudiante_id;
}

if ($curso_id) {
    $where_conditions[] = "e.curso_id = ?";
    $params[] = $curso_id;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener inasistencias
$inasistencias = $db->fetchAll("
    SELECT i.*, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre as especialidad,
           u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM inasistencias i
    JOIN estudiantes e ON i.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN usuarios u ON i.usuario_id = u.id
    WHERE $where_clause
    ORDER BY i.fecha DESC, e.apellido, e.nombre
    LIMIT 200
", $params);

// Obtener datos para formularios
$estudiantes = $db->fetchAll("
    SELECT e.id, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre as especialidad
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE e.activo = 1
    ORDER BY e.apellido, e.nombre
");

$cursos = $db->fetchAll("
    SELECT c.id, c.anio, c.division, esp.nombre as especialidad
    FROM cursos c
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE c.activo = 1
    ORDER BY c.anio, c.division
");

// Estadísticas
$total_inasistencias = $db->fetch("
    SELECT COUNT(*) as total FROM inasistencias i
    JOIN estudiantes e ON i.estudiante_id = e.id
    WHERE $where_clause
", $params)['total'];

$inasistencias_hoy = $db->fetch("
    SELECT COUNT(*) as total FROM inasistencias WHERE fecha = CURDATE()
")['total'];

$justificadas = $db->fetch("
    SELECT COUNT(*) as total FROM inasistencias i
    JOIN estudiantes e ON i.estudiante_id = e.id
    WHERE $where_clause AND i.justificada = 1
", $params)['total'];
?>

<main class="main-content">
<section class="inasistencias-section">
    <div class="section-header">
        <h2>Gestión de Inasistencias</h2>
        <a href="inasistencias.php?action=nueva" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Inasistencia
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
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_inasistencias; ?></h3>
                <p>Total Inasistencias</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $inasistencias_hoy; ?></h3>
                <p>Inasistencias Hoy</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $justificadas; ?></h3>
                <p>Justificadas</p>
            </div>
        </div>
    </div>

    <!-- Formulario de nueva inasistencia -->
    <?php if ($action === 'nueva' || $estudiante_id): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Nueva Inasistencia</h3>
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
                    <label for="tipo">Tipo:</label>
                    <select name="tipo" id="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="completa">Falta Completa</option>
                        <option value="tarde">Llegada Tarde</option>
                        <option value="retiro_anticipado">Retiro Anticipado</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="motivo">Motivo:</label>
                    <input type="text" name="motivo" id="motivo" placeholder="Motivo de la inasistencia">
                </div>
                
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea name="observaciones" id="observaciones" placeholder="Observaciones adicionales"></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="justificada" value="1">
                        Justificada
                    </label>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="certificado_medico" value="1">
                        Con Certificado Médico
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="registrar_inasistencia" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Inasistencia
                </button>
                <a href="inasistencias.php" class="btn btn-secondary">
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
                    <label for="tipo_filter">Tipo:</label>
                    <select name="tipo" id="tipo_filter">
                        <option value="">Todos los tipos</option>
                        <option value="completa" <?php echo $tipo_filter === 'completa' ? 'selected' : ''; ?>>Falta Completa</option>
                        <option value="tarde" <?php echo $tipo_filter === 'tarde' ? 'selected' : ''; ?>>Llegada Tarde</option>
                        <option value="retiro_anticipado" <?php echo $tipo_filter === 'retiro_anticipado' ? 'selected' : ''; ?>>Retiro Anticipado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="justificada_filter">Justificada:</label>
                    <select name="justificada" id="justificada_filter">
                        <option value="">Todas</option>
                        <option value="1" <?php echo $justificada_filter === '1' ? 'selected' : ''; ?>>Sí</option>
                        <option value="0" <?php echo $justificada_filter === '0' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="inasistencias.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de inasistencias -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Inasistencias Registradas</h3>
            <div class="header-actions">
                <a href="inasistencias.php?action=exportar" class="btn btn-success btn-sm">
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
                        <th>Tipo</th>
                        <th>Justificada</th>
                        <th>Motivo</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inasistencias as $inasistencia): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($inasistencia['fecha'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($inasistencia['apellido'] . ', ' . $inasistencia['nombre']); ?></strong><br>
                            <small>DNI: <?php echo htmlspecialchars($inasistencia['dni']); ?></small>
                        </td>
                        <td><?php echo $inasistencia['anio'] . '° ' . $inasistencia['division'] . ' - ' . $inasistencia['especialidad']; ?></td>
                        <td>
                            <span class="status status-<?php echo $inasistencia['tipo'] === 'completa' ? 'danger' : 'warning'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $inasistencia['tipo'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($inasistencia['justificada']): ?>
                                <span class="status status-active">Sí</span>
                                <?php if ($inasistencia['certificado_medico']): ?>
                                    <br><small><i class="fas fa-file-medical"></i> Con certificado</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="status status-inactive">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($inasistencia['motivo'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($inasistencia['usuario_apellido'] . ', ' . $inasistencia['usuario_nombre']); ?></td>
                        <td>
                            <a href="inasistencia_editar.php?id=<?php echo $inasistencia['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($inasistencias)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No hay inasistencias registradas</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<style>
.inasistencias-section {
    max-width: 1200px;
    margin: 0 auto;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    margin-right: 8px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .checkbox-group {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

</main>
<?php include 'includes/footer.php'; ?> 