<?php 
$pageTitle = 'Cursos - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';

// Solo admin y directivo pueden crear cursos
if ($action === 'nuevo' && !($auth->hasRole('admin') || $auth->hasRole('directivo'))) {
    header('Location: cursos.php?error=unauthorized');
    exit();
}

// Procesar formulario de nuevo curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_curso'])) {
    try {
        $sql = "INSERT INTO cursos (anio, division, turno_id, especialidad_id, grado) VALUES (?, ?, ?, ?, ?)";
        $grado = ($_POST['anio'] <= 3) ? 'inferior' : 'superior';
        
        $params = [
            $_POST['anio'],
            $_POST['division'],
            $_POST['turno_id'],
            $_POST['especialidad_id'],
            $grado
        ];
        
        $db->query($sql, $params);
        $success_message = "Curso creado correctamente";
        $action = '';
    } catch (Exception $e) {
        $error_message = "Error al crear curso: " . $e->getMessage();
    }
}

// Filtros
$turno_filter = $_GET['turno'] ?? '';
$especialidad_filter = $_GET['especialidad'] ?? '';

// Construir consulta con filtros
$where_conditions = ["c.activo = 1"];
$params = [];

if ($turno_filter) {
    $where_conditions[] = "c.turno_id = ?";
    $params[] = $turno_filter;
}

if ($especialidad_filter) {
    $where_conditions[] = "c.especialidad_id = ?";
    $params[] = $especialidad_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener cursos
$cursos = $db->fetchAll("
    SELECT c.*, t.nombre as turno, esp.nombre as especialidad,
           COUNT(e.id) as cantidad_estudiantes
    FROM cursos c
    LEFT JOIN turnos t ON c.turno_id = t.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN estudiantes e ON c.id = e.curso_id AND e.activo = 1
    WHERE $where_clause
    GROUP BY c.id
    ORDER BY c.anio, c.division
", $params);

// Obtener datos para formularios y filtros
$turnos = $db->fetchAll("SELECT * FROM turnos ORDER BY id");
$especialidades = $db->fetchAll("SELECT * FROM especialidades WHERE activa = 1 ORDER BY nombre");

// Estadísticas
$total_cursos = count($cursos);
$total_estudiantes = array_sum(array_column($cursos, 'cantidad_estudiantes'));
$cursos_sin_estudiantes = count(array_filter($cursos, function($curso) {
    return $curso['cantidad_estudiantes'] == 0;
}));
?>

<section class="cursos-section">
    <div class="section-header">
        <h2>Gestión de Cursos</h2>
        <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
        <a href="cursos.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Curso
        </a>
        <?php endif; ?>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            No tienes permisos para crear cursos.
        </div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_cursos); ?></h3>
                <p>Total Cursos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_estudiantes); ?></h3>
                <p>Total Estudiantes</p>
            </div>
        </div>
        
        <?php if ($cursos_sin_estudiantes > 0): ?>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($cursos_sin_estudiantes); ?></h3>
                <p>Cursos Sin Estudiantes</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario nuevo curso -->
    <?php if ($action === 'nuevo'): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Crear Nuevo Curso</h3>
        </div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="anio">Año: *</label>
                    <select name="anio" id="anio" required>
                        <option value="">Seleccionar año</option>
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?>°</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="division">División: *</label>
                    <select name="division" id="division" required>
                        <option value="">Seleccionar división</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="turno_id">Turno: *</label>
                    <select name="turno_id" id="turno_id" required>
                        <option value="">Seleccionar turno</option>
                        <?php foreach ($turnos as $turno): ?>
                        <option value="<?php echo $turno['id']; ?>">
                            <?php echo htmlspecialchars($turno['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="especialidad_id">Especialidad: *</label>
                    <select name="especialidad_id" id="especialidad_id" required>
                        <option value="">Seleccionar especialidad</option>
                        <?php foreach ($especialidades as $especialidad): ?>
                        <option value="<?php echo $especialidad['id']; ?>">
                            <?php echo htmlspecialchars($especialidad['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_curso" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Curso
                </button>
                <a href="cursos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <form method="GET" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="turno">Turno:</label>
                    <select name="turno" id="turno">
                        <option value="">Todos los turnos</option>
                        <?php foreach ($turnos as $turno): ?>
                        <option value="<?php echo $turno['id']; ?>" 
                                <?php echo $turno_filter == $turno['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($turno['nombre']); ?>
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
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="cursos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de cursos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cursos Registrados (<?php echo number_format($total_cursos); ?>)</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Especialidad</th>
                        <th>Turno</th>
                        <th>Grado</th>
                        <th>Estudiantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cursos)): ?>
                        <?php foreach ($cursos as $curso): ?>
                        <tr>
                            <td>
                                <strong><?php echo $curso['anio'] . '° ' . $curso['division']; ?></strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($curso['especialidad']); ?>
                            </td>
                            <td>
                                <i class="fas fa-clock"></i> 
                                <?php echo htmlspecialchars($curso['turno']); ?>
                            </td>
                            <td>
                                <span class="status <?php echo $curso['grado'] === 'inferior' ? 'status-success' : 'status-warning'; ?>">
                                    <?php echo ucfirst($curso['grado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($curso['cantidad_estudiantes'] > 0): ?>
                                    <a href="estudiantes.php?curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-users"></i> <?php echo $curso['cantidad_estudiantes']; ?> estudiante<?php echo $curso['cantidad_estudiantes'] != 1 ? 's' : ''; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="status status-warning">Sin estudiantes</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="horarios.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-success" title="Ver horarios">
                                    <i class="fas fa-clock"></i>
                                </a>
                                <a href="estudiantes.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="Ver estudiantes">
                                    <i class="fas fa-users"></i>
                                </a>
                                <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
                                <a href="notas.php?curso=<?php echo $curso['id']; ?>" 
                                   class="btn btn-sm btn-secondary" title="Ver notas del curso">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 2rem; color: var(--secondary-color);">
                                <i class="fas fa-graduation-cap" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <br>No se encontraron cursos con los criterios especificados
                                <br><small>Prueba modificando los filtros de búsqueda</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vista por turnos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Vista por Turnos</h3>
        </div>
        <div class="card-body">
            <?php
            $cursos_por_turno = [];
            foreach ($cursos as $curso) {
                $cursos_por_turno[$curso['turno']][] = $curso;
            }
            ?>
            
            <div class="turnos-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <?php foreach ($cursos_por_turno as $turno => $cursos_turno): ?>
                <div class="turno-section">
                    <h4 style="color: var(--primary-color); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-clock"></i>
                        <?php echo htmlspecialchars($turno); ?>
                        <span class="status status-primary" style="font-size: 0.75rem;">
                            <?php echo count($cursos_turno); ?> curso<?php echo count($cursos_turno) != 1 ? 's' : ''; ?>
                        </span>
                    </h4>
                    
                    <?php foreach ($cursos_turno as $curso): ?>
                    <div class="curso-item" style="padding: 1rem; border: 1px solid var(--medium-gray); border-radius: var(--border-radius); margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo $curso['anio'] . '° ' . $curso['division']; ?></strong>
                            <br><small><?php echo htmlspecialchars($curso['especialidad']); ?></small>
                        </div>
                        <div class="text-right">
                            <span class="status status-success">
                                <?php echo $curso['cantidad_estudiantes']; ?> est.
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
