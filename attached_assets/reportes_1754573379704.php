<?php
$pageTitle = 'Reportes - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';
require_once 'config/database.php';
$db = Database::getInstance();

// Solo admin/directivo
if (!($auth->hasRole('admin') || $auth->hasRole('directivo'))) {
    header('Location: index.php');
    exit();
}

// Filtros
$curso_filter = $_GET['curso'] ?? '';
$estudiante_filter = $_GET['estudiante'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$tab = $_GET['tab'] ?? 'inasistencias';

// Datos para filtros
$cursos = $db->fetchAll("SELECT c.id, c.anio, c.division, esp.nombre as especialidad FROM cursos c LEFT JOIN especialidades esp ON c.especialidad_id = esp.id WHERE c.activo = 1 ORDER BY c.anio, c.division");
$estudiantes = $db->fetchAll("SELECT id, apellido, nombre FROM estudiantes WHERE activo = 1 ORDER BY apellido, nombre");

// Consulta de inasistencias
$where_inas = ["1=1"];
$params_inas = [];
if ($curso_filter) { $where_inas[] = "e.curso_id = ?"; $params_inas[] = $curso_filter; }
if ($estudiante_filter) { $where_inas[] = "e.id = ?"; $params_inas[] = $estudiante_filter; }
if ($fecha_desde) { $where_inas[] = "i.fecha >= ?"; $params_inas[] = $fecha_desde; }
if ($fecha_hasta) { $where_inas[] = "i.fecha <= ?"; $params_inas[] = $fecha_hasta; }
$where_clause_inas = implode(' AND ', $where_inas);

// Consulta de llamados
$where_llam = ["1=1"];
$params_llam = [];
if ($curso_filter) { $where_llam[] = "e.curso_id = ?"; $params_llam[] = $curso_filter; }
if ($estudiante_filter) { $where_llam[] = "e.id = ?"; $params_llam[] = $estudiante_filter; }
if ($fecha_desde) { $where_llam[] = "l.fecha >= ?"; $params_llam[] = $fecha_desde; }
if ($fecha_hasta) { $where_llam[] = "l.fecha <= ?"; $params_llam[] = $fecha_hasta; }
$where_clause_llam = implode(' AND ', $where_llam);
$llamados = $db->fetchAll("
    SELECT l.*, e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad
    FROM llamados_atencion l
    JOIN estudiantes e ON l.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE $where_clause_llam
    ORDER BY l.fecha DESC, e.apellido, e.nombre
    LIMIT 200
", $params_llam);
?>
<section class="reportes-section">
    <div class="section-header">
        <h2>Reportes</h2>
    </div>
    <div class="tab-content <?php if($tab==='inasistencias') echo 'active'; ?>" id="tab-inasistencias">
        <form method="GET" class="filters-form">
            <input type="hidden" name="tab" value="inasistencias">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso">
                        <option value="">Todos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" <?php if($curso_filter==$curso['id']) echo 'selected'; ?>>
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estudiante">Estudiante:</label>
                    <select name="estudiante" id="estudiante">
                        <option value="">Todos</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <option value="<?php echo $est['id']; ?>" <?php if($estudiante_filter==$est['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_desde">Desde:</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_hasta">Hasta:</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($llamados as $l): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($l['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($l['apellido'] . ', ' . $l['nombre']); ?></td>
                        <td><?php echo $l['anio'] . '° ' . $l['division'] . ' - ' . $l['especialidad']; ?></td>
                        <td><?php echo htmlspecialchars($l['motivo']); ?></td>
                        <td><?php echo htmlspecialchars($l['sancion']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($llamados)): ?>
                    <tr><td colspan="6" class="text-center">No hay datos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="export-actions">
            <a href="export_llamados.php?type=csv&curso=<?php echo urlencode($curso_filter); ?>&estudiante=<?php echo urlencode($estudiante_filter); ?>&fecha_desde=<?php echo urlencode($fecha_desde); ?>&fecha_hasta=<?php echo urlencode($fecha_hasta); ?>" class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar Excel</a>
            <a href="export_llamados.php?type=pdf&curso=<?php echo urlencode($curso_filter); ?>&estudiante=<?php echo urlencode($estudiante_filter); ?>&fecha_desde=<?php echo urlencode($fecha_desde); ?>&fecha_hasta=<?php echo urlencode($fecha_hasta); ?>" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Exportar PDF</a>
        </div>
    </div>
</section>
<style>
.reportes-section { max-width: 1200px; margin: 0 auto; }
.tabs-header { display: flex; gap: 10px; margin-bottom: 20px; }
.tab-btn { padding: 10px 20px; border: none; background: #e5e7eb; color: #222; border-radius: 8px 8px 0 0; cursor: pointer; font-weight: 500; text-decoration: none; }
.tab-btn.active { background: var(--primary-color); color: #fff; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.export-actions { margin: 20px 0; display: flex; gap: 10px; }
</style>
<?php include 'includes/footer.php'; ?> 