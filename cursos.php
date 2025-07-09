<?php 
$pageTitle = 'Cursos - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'includes/auto_seed.php';

require_once 'config/database.php';
$db = Database::getInstance();

// Filtros
$anio_filter = $_GET['anio'] ?? '';
$division_filter = $_GET['division'] ?? '';
$especialidad_filter = $_GET['especialidad'] ?? '';
$turno_filter = $_GET['turno'] ?? '';

// Construir consulta con filtros
$where_conditions = ["c.activo = 1"];
$params = [];

if ($anio_filter) {
    $where_conditions[] = "c.anio = ?";
    $params[] = $anio_filter;
}

if ($division_filter) {
    $where_conditions[] = "c.division = ?";
    $params[] = $division_filter;
}

if ($especialidad_filter) {
    $where_conditions[] = "esp.id = ?";
    $params[] = $especialidad_filter;
}

if ($turno_filter) {
    $where_conditions[] = "t.id = ?";
    $params[] = $turno_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener cursos
$cursos = $db->fetchAll("
    SELECT c.*, esp.nombre as especialidad_nombre, t.nombre as turno_nombre,
           COUNT(e.id) as total_estudiantes,
           COUNT(CASE WHEN e.activo = 1 THEN 1 END) as estudiantes_activos
    FROM cursos c
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    LEFT JOIN estudiantes e ON c.id = e.curso_id
    WHERE $where_clause
    GROUP BY c.id
    ORDER BY c.anio, c.division
", $params);

// Obtener datos para filtros
$especialidades = $db->fetchAll("SELECT id, nombre FROM especialidades WHERE activa = 1");
$turnos = $db->fetchAll("SELECT id, nombre FROM turnos");
$anios = range(1, 7);
$divisions = ['A', 'B', 'C', 'D', 'E'];

// Estadísticas
$total_cursos = count($cursos);
$total_estudiantes = $db->fetch("
    SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1
")['total'];

$cursos_por_anio = $db->fetchAll("
    SELECT c.anio, COUNT(*) as cantidad
    FROM cursos c
    WHERE c.activo = 1
    GROUP BY c.anio
    ORDER BY c.anio
");
?>

<main class="main-content">
<section class="cursos-section">
    <div class="section-header">
        <h2>Gestión de Cursos</h2>
        <a href="curso_nuevo.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Curso
        </a>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-cards grid-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_cursos; ?></h3>
                <p>Total Cursos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_estudiantes; ?></h3>
                <p>Total Estudiantes</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($cursos_por_anio); ?></h3>
                <p>Años Activos</p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="filters-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="anio">Año:</label>
                    <select name="anio" id="anio">
                        <option value="">Todos los años</option>
                        <?php foreach ($anios as $anio): ?>
                        <option value="<?php echo $anio; ?>" <?php echo $anio_filter == $anio ? 'selected' : ''; ?>>
                            <?php echo $anio; ?>°
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="division">División:</label>
                    <select name="division" id="division">
                        <option value="">Todas las divisiones</option>
                        <?php foreach ($divisions as $division): ?>
                        <option value="<?php echo $division; ?>" <?php echo $division_filter === $division ? 'selected' : ''; ?>>
                            <?php echo $division; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="especialidad">Especialidad:</label>
                    <select name="especialidad" id="especialidad">
                        <option value="">Todas las especialidades</option>
                        <?php foreach ($especialidades as $esp): ?>
                        <option value="<?php echo $esp['id']; ?>" <?php echo $especialidad_filter == $esp['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($esp['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="turno">Turno:</label>
                    <select name="turno" id="turno">
                        <option value="">Todos los turnos</option>
                        <?php foreach ($turnos as $turno): ?>
                        <option value="<?php echo $turno['id']; ?>" <?php echo $turno_filter == $turno['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($turno['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="cursos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de cursos -->
    <div class="cursos-grid">
        <?php foreach ($cursos as $curso): ?>
        <div class="curso-card">
            <div class="curso-header">
                <h3><?php echo $curso['anio']; ?>° <?php echo htmlspecialchars($curso['division']); ?></h3>
                <span class="curso-especialidad"><?php echo htmlspecialchars($curso['especialidad_nombre']); ?></span>
            </div>
            
            <div class="curso-info">
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo htmlspecialchars($curso['turno_nombre']); ?></span>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-users"></i>
                    <span><?php echo $curso['estudiantes_activos']; ?> estudiantes</span>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-layer-group"></i>
                    <span><?php echo ucfirst($curso['grado']); ?></span>
                </div>
            </div>
            
            <div class="curso-actions">
                <a href="estudiantes.php?curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-users"></i> Ver Estudiantes
                </a>
                <a href="horarios.php?curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-clock"></i> Horarios
                </a>
                <a href="curso_editar.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($cursos)): ?>
        <div class="no-cursos">
            <i class="fas fa-graduation-cap"></i>
            <p>No hay cursos registrados</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.cursos-section {
    max-width: 1200px;
    margin: 0 auto;
}

.cursos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.curso-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.curso-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.curso-header {
    margin-bottom: 15px;
}

.curso-header h3 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.3em;
}

.curso-especialidad {
    background: #007bff;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85em;
}

.curso-info {
    margin-bottom: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    color: #666;
}

.info-item i {
    width: 20px;
    margin-right: 8px;
    color: #007bff;
}

.curso-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.no-cursos {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    color: #999;
}

.no-cursos i {
    font-size: 3em;
    margin-bottom: 15px;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .cursos-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

</main>
<?php include 'includes/footer.php'; ?> 