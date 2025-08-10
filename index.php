<?php 
$pageTitle = 'Dashboard - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

// Obtener estadísticas generales
$stats = [];

// Total estudiantes activos
$stats['total_estudiantes'] = $db->fetch("SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1")['total'];

// Total cursos activos
$stats['total_cursos'] = $db->fetch("SELECT COUNT(*) as total FROM cursos WHERE activo = 1")['total'];

// Notas registradas en los últimos 7 días
$stats['notas_semana'] = $db->fetch("SELECT COUNT(*) as total FROM notas WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['total'] ?? 0;

// Llamados últimos 7 días
$stats['llamados_semana'] = $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['total'] ?? 0;

// Cumpleaños de hoy
$stats['cumpleanos_hoy'] = $db->fetch("SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1 AND DATE_FORMAT(fecha_nacimiento, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')")['total'] ?? 0;

// Estudiantes por turno
$estudiantes_por_turno = $db->fetchAll("
    SELECT t.nombre as turno, COUNT(e.id) as cantidad
    FROM turnos t
    LEFT JOIN cursos c ON t.id = c.turno_id AND c.activo = 1
    LEFT JOIN estudiantes e ON c.id = e.curso_id AND e.activo = 1
    GROUP BY t.id, t.nombre
    ORDER BY t.id
");

// Últimos llamados de atención
$ultimos_llamados = $db->fetchAll("
    SELECT l.*, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre as especialidad
    FROM llamados_atencion l
    JOIN estudiantes e ON l.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE l.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY l.fecha DESC, l.id DESC
    LIMIT 10
") ?: [];

// Cumpleañeros de hoy
$cumpleaneros = $db->fetchAll("
    SELECT e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad,
           YEAR(CURDATE()) - YEAR(e.fecha_nacimiento) as edad
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE e.activo = 1 
    AND DATE_FORMAT(e.fecha_nacimiento, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')
    ORDER BY e.apellido, e.nombre
") ?: [];
?>

<section class="dashboard-section">
    <div class="section-header">
        <h2>Panel de Control</h2>
        <div class="header-info">
            <span class="current-date">
                <i class="fas fa-calendar"></i>
                <?php echo strftime('%A, %d de %B de %Y', time()); ?>
            </span>
        </div>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            No tienes permisos para acceder a esa sección.
        </div>
    <?php endif; ?>

    <!-- Estadísticas principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_estudiantes']); ?></h3>
                <p>Estudiantes Activos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_cursos']); ?></h3>
                <p>Cursos Activos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['notas_semana']); ?></h3>
                <p>Notas (7 días)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['llamados_semana']); ?></h3>
                <p>Llamados (7 días)</p>
            </div>
        </div>
        
        <?php if ($stats['cumpleanos_hoy'] > 0): ?>
        <div class="stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-birthday-cake"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['cumpleanos_hoy']); ?></h3>
                <p>Cumpleaños Hoy</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Acciones rápidas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Acciones Rápidas</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="estudiantes.php?action=nuevo" class="quick-action">
                    <div class="quick-action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <strong>Agregar Estudiante</strong>
                        <br><small>Registrar nuevo alumno</small>
                    </div>
                </a>
                <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
                <a href="notas.php?action=nueva" class="quick-action">
                    <div class="quick-action-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <strong>Cargar Nota</strong>
                        <br><small>Registrar calificación</small>
                    </div>
                </a>
                <?php endif; ?>
                <a href="llamados.php?action=nuevo" class="quick-action">
                    <div class="quick-action-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <strong>Nuevo Llamado</strong>
                        <br><small>Registrar llamado de atención</small>
                    </div>
                </a>
                <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo') || $auth->hasRole('preceptor')): ?>
                <a href="reportes.php" class="quick-action">
                    <div class="quick-action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>
                        <strong>Ver Reportes</strong>
                        <br><small>Estadísticas</small>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Estudiantes por turno -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estudiantes por Turno</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($estudiantes_por_turno)): ?>
                    <?php foreach ($estudiantes_por_turno as $turno): ?>
                    <div class="turno-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--medium-gray);">
                        <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($turno['turno']); ?></span>
                        <span class="status status-success"><?php echo number_format($turno['cantidad']); ?> estudiantes</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center" style="color: var(--secondary-color); padding: 2rem;">No hay datos de turnos disponibles</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cumpleañeros de hoy -->
        <?php if (!empty($cumpleaneros)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-birthday-cake"></i> Cumpleaños de Hoy
                </h3>
            </div>
            <div class="card-body">
                <?php foreach ($cumpleaneros as $cumpleanero): ?>
                <div class="cumpleanero-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--medium-gray);">
                    <div>
                        <strong><?php echo htmlspecialchars($cumpleanero['apellido'] . ', ' . $cumpleanero['nombre']); ?></strong>
                        <br><small><?php echo $cumpleanero['anio'] . '° ' . $cumpleanero['division'] . ' - ' . $cumpleanero['especialidad']; ?></small>
                    </div>
                    <span class="status" style="background: #8b5cf6; color: white;"><?php echo $cumpleanero['edad']; ?> años</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Últimos llamados -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Llamados de Atención</h3>
                <a href="llamados.php" class="btn btn-sm btn-secondary">Ver todos</a>
            </div>
            <div class="card-body">
                <?php if (!empty($ultimos_llamados)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Estudiante</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimos_llamados as $llamado): ?>
                            <tr>
                                <td><?php echo date('d/m', strtotime($llamado['fecha'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($llamado['apellido'] . ', ' . $llamado['nombre']); ?></strong>
                                    <br><small><?php echo $llamado['anio'] . '° ' . $llamado['division']; ?></small>
                                </td>
                                <td>
                                    <span class="status status-warning">
                                        <?php echo htmlspecialchars(substr($llamado['motivo'], 0, 30)) . (strlen($llamado['motivo']) > 30 ? '...' : ''); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center" style="color: var(--secondary-color); padding: 2rem;">No hay llamados recientes</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.current-date {
    color: var(--secondary-color);
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.header-info {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr !important;
    }
    
    .header-info {
        margin-top: 1rem;
    }
}
</style>

<?php 
// Configurar locale para fechas en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');
include 'includes/footer.php'; 
?>
