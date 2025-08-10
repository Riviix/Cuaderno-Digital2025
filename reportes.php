<?php
$pageTitle = 'Reportes - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

// Solo admin/directivo/preceptor
if (!($auth->hasRole('admin') || $auth->hasRole('directivo') || $auth->hasRole('preceptor'))) {
    header('Location: index.php?error=unauthorized');
    exit();
}

// Filtros
$curso_filter = $_GET['curso'] ?? '';
$estudiante_filter = $_GET['estudiante'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$tab = 'llamados'; // fijo a llamados

// Datos para filtros
$cursos = $db->fetchAll("
    SELECT c.id, c.anio, c.division, esp.nombre as especialidad 
    FROM cursos c 
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id 
    WHERE c.activo = 1 
    ORDER BY c.anio, c.division
");

$estudiantes = $db->fetchAll("
    SELECT e.id, e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad
    FROM estudiantes e 
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE e.activo = 1 
    ORDER BY e.apellido, e.nombre
");

// Construir condiciones WHERE
$where_conditions = ["1=1"];
$params = [];

if ($curso_filter) {
    $where_conditions[] = "e.curso_id = ?";
    $params[] = $curso_filter;
}

if ($estudiante_filter) {
    $where_conditions[] = "e.id = ?";
    $params[] = $estudiante_filter;
}

if ($fecha_desde) {
    $where_conditions[] = "l.fecha >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = "l.fecha <= ?";
    $params[] = $fecha_hasta;
}

$where_clause = implode(' AND ', $where_conditions);

// Obtener datos según la pestaña activa
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
    
// Estadísticas de llamados
$stats_llamados = [
    'total' => $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion l JOIN estudiantes e ON l.estudiante_id = e.id WHERE $where_clause", $params)['total'],
    'con_sancion' => $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion l JOIN estudiantes e ON l.estudiante_id = e.id WHERE $where_clause AND l.sancion IS NOT NULL AND l.sancion != ''", $params)['total'],
    'sin_sancion' => $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion l JOIN estudiantes e ON l.estudiante_id = e.id WHERE $where_clause AND (l.sancion IS NULL OR l.sancion = '')", $params)['total']
];
    
// Motivos más frecuentes
$motivos_frecuentes = $db->fetchAll("
    SELECT l.motivo, COUNT(*) as cantidad
    FROM llamados_atencion l 
    JOIN estudiantes e ON l.estudiante_id = e.id 
    WHERE $where_clause
    GROUP BY l.motivo
    ORDER BY cantidad DESC
    LIMIT 10
", $params);
    
// Sanciones más aplicadas
$sanciones_frecuentes = $db->fetchAll("
    SELECT l.sancion, COUNT(*) as cantidad
    FROM llamados_atencion l 
    JOIN estudiantes e ON l.estudiante_id = e.id 
    WHERE $where_clause AND l.sancion IS NOT NULL AND l.sancion != ''
    GROUP BY l.sancion
    ORDER BY cantidad DESC
    LIMIT 10
", $params);

// Estadísticas generales
$stats_generales = [
    'estudiantes_con_llamados' => $db->fetch("
        SELECT COUNT(DISTINCT l.estudiante_id) as total 
        FROM llamados_atencion l 
        JOIN estudiantes e ON l.estudiante_id = e.id 
        WHERE $where_clause
    ", $params)['total']
];
?>

<section class="reportes-section">
    <div class="section-header">
        <h2>Sistema de Reportes</h2>
        <div class="header-actions">
            <a href="export_llamados.php?<?php echo http_build_query($_GET); ?>" class="btn btn-warning">
                <i class="fas fa-file-excel"></i> Exportar Llamados
            </a>
        </div>
    </div>

    <!-- Filtros comunes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Reporte</h3>
        </div>
        <form method="GET" class="form-container">
            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso">
                        <option value="">Todos los cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" <?php echo $curso_filter == $curso['id'] ? 'selected' : ''; ?>>
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="estudiante">Estudiante:</label>
                    <select name="estudiante" id="estudiante">
                        <option value="">Todos los estudiantes</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <option value="<?php echo $est['id']; ?>" <?php echo $estudiante_filter == $est['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']) . 
                                      ($est['anio'] ? ' - ' . $est['anio'] . '° ' . $est['division'] : ''); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fecha_desde">Fecha Desde:</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_hasta">Fecha Hasta:</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generar Reporte
                </button>
                <a href="reportes.php?tab=<?php echo $tab; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar Filtros
                </a>
            </div>
        </form>
    </div>

    <!-- Contenido de Llamados -->
    <div class="tab-content active">
        <!-- Estadísticas de llamados -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats_llamados['total']); ?></h3>
                    <p>Total Llamados</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats_llamados['con_sancion']); ?></h3>
                    <p>Con Sanción</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats_llamados['sin_sancion']); ?></h3>
                    <p>Sin Sanción</p>
                </div>
            </div>
        </div>

        <!-- Análisis por motivos -->
        <?php if (!empty($motivos_frecuentes)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Motivos Más Frecuentes</h3>
            </div>
            <div class="card-body">
                <?php $total_motivos = array_sum(array_column($motivos_frecuentes, 'cantidad')); ?>
                <?php foreach ($motivos_frecuentes as $motivo): ?>
                <div class="motivo-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--medium-gray);">
                    <span><?php echo htmlspecialchars($motivo['motivo']); ?></span>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 100px; height: 8px; background: var(--light-gray); border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($motivo['cantidad'] / $total_motivos) * 100; ?>%; height: 100%; background: var(--warning-color);"></div>
                        </div>
                        <span class="status status-warning"><?php echo number_format($motivo['cantidad']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Análisis por sanciones -->
        <?php if (!empty($sanciones_frecuentes)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sanciones Más Aplicadas</h3>
            </div>
            <div class="card-body">
                <?php $total_sanciones = array_sum(array_column($sanciones_frecuentes, 'cantidad')); ?>
                <?php foreach ($sanciones_frecuentes as $sancion): ?>
                <div class="sancion-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--medium-gray);">
                    <span><?php echo htmlspecialchars($sancion['sancion']); ?></span>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 100px; height: 8px; background: var(--light-gray); border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo ($sancion['cantidad'] / $total_sanciones) * 100; ?>%; height: 100%; background: var(--danger-color);"></div>
                        </div>
                        <span class="status status-danger"><?php echo number_format($sancion['cantidad']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla detallada -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detalle de Llamados de Atención</h3>
            </div>
            
            <?php if (!empty($llamados)): ?>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($llamados as $llamado): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($llamado['fecha'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($llamado['apellido'] . ', ' . $llamado['nombre']); ?></strong>
                                <br><small>DNI: <?php echo htmlspecialchars($llamado['dni']); ?></small>
                            </td>
                            <td>
                                <?php echo $llamado['anio'] . '° ' . $llamado['division']; ?>
                                <br><small><?php echo htmlspecialchars($llamado['especialidad']); ?></small>
                            </td>
                            <td>
                                <span class="status status-warning"><?php echo htmlspecialchars($llamado['motivo']); ?></span>
                                <?php if ($llamado['observaciones']): ?>
                                    <br><small style="color: var(--secondary-color);">
                                        <?php echo htmlspecialchars(substr($llamado['observaciones'], 0, 50)) . (strlen($llamado['observaciones']) > 50 ? '...' : ''); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($llamado['sancion']): ?>
                                    <span class="status status-danger"><?php echo htmlspecialchars($llamado['sancion']); ?></span>
                                <?php else: ?>
                                    <span class="status status-success">Sin sanción</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <?php echo htmlspecialchars($llamado['usuario_apellido'] . ', ' . $llamado['usuario_nombre']); ?>
                                    <br><?php echo date('d/m/Y', strtotime($llamado['fecha_registro'])); ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body text-center" style="padding: 2rem;">
                <p style="color: var(--secondary-color);">No se encontraron llamados de atención con los criterios especificados</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.reportes-section { 
    max-width: 1200px; 
    margin: 0 auto; 
}

.tabs-header { 
    display: flex; 
    gap: 10px; 
    margin-bottom: 20px; 
}

.tab-btn { 
    padding: 10px 20px; 
    border: none; 
    background: #e5e7eb; 
    color: #374151; 
    border-radius: 8px 8px 0 0; 
    cursor: pointer; 
    font-weight: 500; 
    text-decoration: none;
    transition: all 0.2s;
}

.tab-btn:hover {
    background: #d1d5db;
}

.tab-btn.active { 
    background: var(--primary-color); 
    color: #fff; 
}

.tab-content { 
    display: block; 
}

.header-actions { 
    display: flex; 
    gap: 10px; 
}

@media (max-width: 768px) {
    .tabs-header {
        flex-direction: column;
    }
    
    .header-actions {
        flex-direction: column;
        margin-top: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr !important;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
