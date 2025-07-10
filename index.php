<?php 
$pageTitle = 'Dashboard - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'config/database.php';
$db = Database::getInstance();

// Obtener estadísticas del dashboard
$stats = [];

// Total de estudiantes
$stats['total_estudiantes'] = $db->fetch("SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1")['total'];

// Total de cursos
$stats['total_cursos'] = $db->fetch("SELECT COUNT(*) as total FROM cursos WHERE activo = 1")['total'];

// Faltas del día
$stats['faltas_hoy'] = $db->fetch("SELECT COUNT(*) as total FROM inasistencias WHERE fecha = CURDATE()")['total'];

// Llamados de atención recientes (últimos 7 días)
$stats['llamados_recientes'] = $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['total'];

// Cumpleaños del día
$stats['cumpleanos_hoy'] = $db->fetch("SELECT COUNT(*) as total FROM estudiantes WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d') AND activo = 1")['total'];

// Estudiantes por turno
$turnos = $db->fetchAll("
    SELECT t.nombre, COUNT(e.id) as total 
    FROM turnos t 
    LEFT JOIN cursos c ON t.id = c.turno_id 
    LEFT JOIN estudiantes e ON c.id = e.curso_id AND e.activo = 1
    GROUP BY t.id, t.nombre
");

// Últimas inasistencias
$ultimas_inasistencias = $db->fetchAll("
    SELECT i.fecha, i.tipo, e.apellido, e.nombre, c.anio, c.division
    FROM inasistencias i
    JOIN estudiantes e ON i.estudiante_id = e.id
    JOIN cursos c ON e.curso_id = c.id
    ORDER BY i.fecha DESC, i.fecha_registro DESC
    LIMIT 10
");

// Últimos llamados de atención
$ultimos_llamados = $db->fetchAll("
    SELECT la.fecha, la.motivo, e.apellido, e.nombre, c.anio, c.division
    FROM llamados_atencion la
    JOIN estudiantes e ON la.estudiante_id = e.id
    JOIN cursos c ON e.curso_id = c.id
    ORDER BY la.fecha DESC, la.fecha_registro DESC
    LIMIT 5
");

// Obtener inasistencias recientes
$inasistencias_recientes = $db->fetchAll("
    SELECT i.fecha, i.tipo, e.apellido, e.nombre, c.anio, c.division
    FROM inasistencias i
    JOIN estudiantes e ON i.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    WHERE i.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY i.fecha DESC, e.apellido, e.nombre
    LIMIT 5
");

// Obtener llamados recientes
$llamados_recientes = $db->fetchAll("
    SELECT la.fecha, la.motivo, e.apellido, e.nombre, c.anio, c.division
    FROM llamados_atencion la
    JOIN estudiantes e ON la.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    WHERE la.fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY la.fecha DESC, e.apellido, e.nombre
    LIMIT 5
");
?>

<section class="dashboard">
    <h2>Panel Principal</h2>
    
    <!-- Estadísticas rápidas -->
    <div class="stats-grid grid-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_estudiantes']; ?></h3>
                <p>Estudiantes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_cursos']; ?></h3>
                <p>Cursos</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['faltas_hoy']; ?></h3>
                <p>Faltas Hoy</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['llamados_recientes']; ?></h3>
                <p>Llamados Recientes</p>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos por turno -->
    <div class="quick-access">
        <a href="cursos.php?turno=1" class="btn turno-m">
            <i class="fas fa-sun"></i>
            Turno Mañana
            <span class="turno-count"><?php echo $turnos[0]['total'] ?? 0; ?> estudiantes</span>
        </a>
        <a href="cursos.php?turno=2" class="btn turno-t">
            <i class="fas fa-cloud-sun"></i>
            Turno Tarde
            <span class="turno-count"><?php echo $turnos[1]['total'] ?? 0; ?> estudiantes</span>
        </a>
        <a href="cursos.php?turno=3" class="btn turno-c">
            <i class="fas fa-moon"></i>
            Contraturno
            <span class="turno-count"><?php echo $turnos[2]['total'] ?? 0; ?> estudiantes</span>
        </a>
    </div>

    <div class="dashboard-grid grid-2">
        <!-- Notificaciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notificaciones</h3>
                <i class="fas fa-bell"></i>
            </div>
            <div class="notifications-list">
                <div class="notification-item">
                    <i class="fas fa-calendar-times"></i>
                    <span>Faltas del día: <strong><?php echo $stats['faltas_hoy']; ?></strong></span>
                </div>
                <div class="notification-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Llamados de atención recientes: <strong><?php echo $stats['llamados_recientes']; ?></strong></span>
                </div>
                <div class="notification-item">
                    <i class="fas fa-birthday-cake"></i>
                    <span>Cumpleaños del día: <strong><?php echo $stats['cumpleanos_hoy']; ?></strong></span>
                </div>
                <div class="notification-item">
                    <i class="fas fa-clock"></i>
                    <span>Horarios de contraturno activos</span>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Acciones Rápidas</h3>
                <i class="fas fa-bolt"></i>
            </div>
            <div class="quick-actions">
                <a href="inasistencias.php?action=nueva" class="action-btn">
                    <i class="fas fa-plus"></i>
                    Registrar Inasistencia
                </a>
                <a href="llamados.php?action=nuevo" class="action-btn">
                    <i class="fas fa-exclamation"></i>
                    Nuevo Llamado de Atención
                </a>
                <a href="estudiantes.php?action=nuevo" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    Agregar Estudiante
                </a>
                <a href="reportes.php" class="action-btn">
                    <i class="fas fa-chart-bar"></i>
                    Generar Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Últimas actividades -->
    <div class="dashboard-grid grid-2">
        <!-- Últimas inasistencias -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimas Inasistencias</h3>
                <a href="inasistencias.php" class="btn btn-sm btn-primary">Ver todas</a>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimas_inasistencias as $inasistencia): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($inasistencia['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($inasistencia['apellido'] . ', ' . $inasistencia['nombre']); ?></td>
                            <td><?php echo $inasistencia['anio'] . '° ' . $inasistencia['division']; ?></td>
                            <td>
                                <span class="status status-<?php echo $inasistencia['tipo'] === 'completa' ? 'danger' : 'warning'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $inasistencia['tipo'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ultimas_inasistencias)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay inasistencias registradas</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Últimos llamados de atención -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Llamados de Atención</h3>
                <a href="llamados.php" class="btn btn-sm btn-primary">Ver todos</a>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimos_llamados as $llamado): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($llamado['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($llamado['apellido'] . ', ' . $llamado['nombre']); ?></td>
                            <td><?php echo $llamado['anio'] . '° ' . $llamado['division']; ?></td>
                            <td><?php echo htmlspecialchars(substr($llamado['motivo'], 0, 50)) . (strlen($llamado['motivo']) > 50 ? '...' : ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ultimos_llamados)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay llamados de atención registrados</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>