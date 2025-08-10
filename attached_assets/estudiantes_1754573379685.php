<?php 
$pageTitle = 'Estudiantes - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'config/database.php';
$db = Database::getInstance();

// Filtros
$curso_filter = $_GET['curso'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';
$especialidad_filter = $_GET['especialidad'] ?? '';

// Construir consulta con filtros
$where_conditions = ["e.activo = 1"];
$params = [];

if ($curso_filter) {
    $where_conditions[] = "e.curso_id = ?";
    $params[] = $curso_filter;
}

if ($busqueda) {
    $where_conditions[] = "(e.apellido LIKE ? OR e.nombre LIKE ? OR e.dni LIKE ?)";
    $search_term = "%$busqueda%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($especialidad_filter) {
    $where_conditions[] = "c.especialidad_id = ?";
    $params[] = $especialidad_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener estudiantes
$estudiantes = $db->fetchAll("
    SELECT e.*, c.anio, c.division, c.grado, esp.nombre as especialidad_nombre,
           t.nombre as turno_nombre,
           COUNT(mp.id) as total_previas,
           COUNT(la.id) as total_llamados
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    LEFT JOIN materias_previas mp ON e.id = mp.estudiante_id AND mp.estado = 'pendiente'
    LEFT JOIN llamados_atencion la ON e.id = la.estudiante_id AND la.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    WHERE $where_clause
    GROUP BY e.id
    ORDER BY e.apellido, e.nombre
    LIMIT 100
", $params);

// Obtener datos para filtros
$cursos = $db->fetchAll("
    SELECT c.id, c.anio, c.division, esp.nombre as especialidad
    FROM cursos c
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE c.activo = 1
    ORDER BY c.anio, c.division
");

$especialidades = $db->fetchAll("SELECT * FROM especialidades WHERE activa = 1 ORDER BY nombre");
?>

<section class="estudiantes-section">
    <div class="section-header">
        <h2>Gestión de Estudiantes</h2>
        <a href="estudiantes.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Estudiante
        </a>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="filters-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="busqueda">Buscar:</label>
                    <input type="text" name="busqueda" id="busqueda" 
                           value="<?php echo htmlspecialchars($busqueda); ?>" 
                           placeholder="Apellido, nombre o DNI">
                </div>
                
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso">
                        <option value="">Todos los cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" 
                                <?php echo $curso_filter == $curso['id'] ? 'selected' : ''; ?>>
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="especialidad">Especialidad:</label>
                    <select name="especialidad" id="especialidad">
                        <option value="">Todas las especialidades</option>
                        <?php foreach ($especialidades as $especialidad): ?>
                        <option value="<?php echo $especialidad['id']; ?>" 
                                <?php echo $especialidad_filter == $especialidad['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="estudiantes.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de estudiantes -->
    <div class="estudiantes-grid">
        <?php foreach ($estudiantes as $estudiante): ?>
        <div class="estudiante-card">
            <div class="estudiante-photo">
                <?php if ($estudiante['foto']): ?>
                    <img src="<?php echo htmlspecialchars($estudiante['foto']); ?>" alt="Foto del estudiante">
                <?php else: ?>
                    <div class="photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="estudiante-info">
                <h3><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></h3>
                <p class="estudiante-dni">DNI: <?php echo htmlspecialchars($estudiante['dni']); ?></p>
                <p class="estudiante-curso">
                    <?php echo $estudiante['anio'] . '° ' . $estudiante['division']; ?> - 
                    <?php echo htmlspecialchars($estudiante['especialidad_nombre']); ?>
                </p>
                <p class="estudiante-turno"><?php echo htmlspecialchars($estudiante['turno_nombre']); ?></p>
            </div>
            
            <div class="estudiante-stats">
                <div class="stat-item">
                    <i class="fas fa-calendar-times"></i>
                    <span><?php echo $estudiante['total_faltas']; ?> faltas (30 días)</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo $estudiante['total_previas']; ?> previas</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $estudiante['total_llamados']; ?> llamados (30 días)</span>
                </div>
            </div>
            
            <div class="estudiante-actions">
                <a href="estudiante_ficha.php?id=<?php echo $estudiante['id']; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> Ver Ficha
                </a>
                <a href="llamados.php?estudiante=<?php echo $estudiante['id']; ?>" class="btn btn-danger btn-sm">
                    <i class="fas fa-exclamation-triangle"></i> Llamados
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($estudiantes)): ?>
        <div class="no-data">
            <i class="fas fa-search"></i>
            <h3>No se encontraron estudiantes</h3>
            <p>Intenta ajustar los filtros o agregar un nuevo estudiante.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.estudiantes-section {
    max-width: 1200px;
    margin: 0 auto;
}

.estudiantes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.estudiante-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: var(--shadow);
    transition: transform 0.3s;
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 20px;
}

.estudiante-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.estudiante-photo {
    text-align: center;
}

.estudiante-photo img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-color);
}

.photo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--light-color);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--border-color);
    margin: 0 auto;
}

.photo-placeholder i {
    font-size: 24px;
    color: var(--gray-color);
}

.estudiante-info {
    grid-column: 2;
}

.estudiante-info h3 {
    font-size: 18px;
    color: var(--primary-color);
    margin-bottom: 8px;
    font-weight: 600;
}

.estudiante-dni {
    color: var(--gray-color);
    font-size: 14px;
    margin-bottom: 5px;
}

.estudiante-curso {
    color: var(--dark-color);
    font-weight: 500;
    margin-bottom: 5px;
}

.estudiante-turno {
    color: var(--gray-color);
    font-size: 14px;
    margin-bottom: 15px;
}

.estudiante-stats {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
    padding: 15px;
    background: var(--light-color);
    border-radius: 8px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--dark-color);
}

.stat-item i {
    color: var(--primary-color);
    width: 16px;
}

.estudiante-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .estudiantes-grid {
        grid-template-columns: 1fr;
    }
    
    .estudiante-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .estudiante-info {
        grid-column: 1;
    }
    
    .estudiante-stats {
        grid-template-columns: 1fr;
    }
    
    .estudiante-actions {
        flex-direction: column;
    }
}
</style>

<?php include 'includes/footer.php'; ?> 