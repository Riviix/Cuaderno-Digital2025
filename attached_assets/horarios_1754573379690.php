<?php 
$pageTitle = 'Horarios - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'config/database.php';
$db = Database::getInstance();

$curso_id = $_GET['curso'] ?? '';
$turno = $_GET['turno'] ?? '';

// Procesar formulario de nuevo horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_horario'])) {
    $curso_id = $_POST['curso_id'];
    $materia_id = $_POST['materia_id'];
    $dia_semana = $_POST['dia_semana'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $aula = $_POST['aula'] ?? '';
    $docente = $_POST['docente'] ?? '';

    try {
        // Verificar si ya existe un horario para ese curso, día y hora
        $existe = $db->fetch("
            SELECT id FROM horarios 
            WHERE curso_id = ? AND dia_semana = ? AND 
                  ((hora_inicio <= ? AND hora_fin > ?) OR 
                   (hora_inicio < ? AND hora_fin >= ?) OR
                   (hora_inicio >= ? AND hora_fin <= ?))
        ", [$curso_id, $dia_semana, $hora_inicio, $hora_inicio, $hora_fin, $hora_fin, $hora_inicio, $hora_fin]);
        
        if ($existe) {
            $error_message = "Ya existe un horario para ese curso en el día y horario seleccionado";
        } else {
            $db->query("
                INSERT INTO horarios (curso_id, materia_id, dia_semana, hora_inicio, hora_fin, aula, docente)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$curso_id, $materia_id, $dia_semana, $hora_inicio, $hora_fin, $aula, $docente]);
            
            $success_message = "Horario guardado correctamente";
        }
    } catch (Exception $e) {
        $error_message = "Error al guardar el horario: " . $e->getMessage();
    }
}

// Obtener datos para formularios
$cursos = $db->fetchAll("
    SELECT c.id, c.anio, c.division, esp.nombre as especialidad, t.nombre as turno
    FROM cursos c
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    WHERE c.activo = 1
    ORDER BY c.anio, c.division
");

$materias = $db->fetchAll("
    SELECT id, nombre
    FROM materias
    ORDER BY nombre
");

// Obtener horarios para mostrar
$where_conditions = ["1=1"];
$params = [];

if ($curso_id) {
    $where_conditions[] = "h.curso_id = ?";
    $params[] = $curso_id;
}

if ($turno) {
    $where_conditions[] = "t.nombre = ?";
    $params[] = $turno;
}

$where_clause = implode(" AND ", $where_conditions);

$horarios = $db->fetchAll("
    SELECT h.*, m.nombre as materia_nombre, c.anio, c.division, esp.nombre as especialidad,
           t.nombre as turno_nombre
    FROM horarios h
    LEFT JOIN materias m ON h.materia_id = m.id
    LEFT JOIN cursos c ON h.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    WHERE $where_clause
    ORDER BY c.anio, c.division, h.dia_semana, h.hora_inicio
", $params);

// Organizar horarios por día
$horarios_por_dia = [];
$dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];
$dias_numeros = [1, 2, 3, 4, 5];

foreach ($dias as $index => $dia) {
    $horarios_por_dia[$dia] = [];
}

foreach ($horarios as $horario) {
    $dia_nombre = $dias[$horario['dia_semana'] - 1] ?? 'Desconocido';
    $horarios_por_dia[$dia_nombre][] = $horario;
}

// Estadísticas
$total_horarios = count($horarios);
$cursos_con_horario = $db->fetch("
    SELECT COUNT(DISTINCT curso_id) as total FROM horarios
")['total'];

$materias_activas = $db->fetch("
    SELECT COUNT(*) as total FROM materias
")['total'];
?>

<section class="horarios-section">
    <div class="section-header">
        <h2>Gestión de Horarios</h2>
        <a href="horarios.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Horario
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
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_horarios; ?></h3>
                <p>Total Horarios</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $cursos_con_horario; ?></h3>
                <p>Cursos con Horario</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $materias_activas; ?></h3>
                <p>Materias Activas</p>
            </div>
        </div>
    </div>

    <!-- Formulario de nuevo horario -->
    <?php if (isset($_GET['action']) && $_GET['action'] === 'nuevo'): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Nuevo Horario</h3>
        </div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso_id">Curso:</label>
                    <select name="curso_id" id="curso_id" required>
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" 
                                <?php echo $curso_id == $curso['id'] ? 'selected' : ''; ?>>
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad'] . ' (' . $curso['turno'] . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="materia_id">Materia:</label>
                    <select name="materia_id" id="materia_id" required>
                        <option value="">Seleccionar materia</option>
                        <?php foreach ($materias as $materia): ?>
                        <option value="<?php echo $materia['id']; ?>">
                            <?php echo htmlspecialchars($materia['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dia_semana">Día:</label>
                    <select name="dia_semana" id="dia_semana" required>
                        <option value="">Seleccionar día</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miercoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="hora_inicio">Hora Inicio:</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required>
                </div>
                
                <div class="form-group">
                    <label for="hora_fin">Hora Fin:</label>
                    <input type="time" name="hora_fin" id="hora_fin" required>
                </div>
                
                <div class="form-group">
                    <label for="aula">Aula:</label>
                    <input type="text" name="aula" id="aula" placeholder="Número de aula">
                </div>
                
                <div class="form-group">
                    <label for="docente">Docente:</label>
                    <input type="text" name="docente" id="docente" placeholder="Nombre del docente">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_horario" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Horario
                </button>
                <a href="horarios.php" class="btn btn-secondary">
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
        <form method="GET" class="filters-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso_filter">Curso:</label>
                    <select name="curso" id="curso_filter">
                        <option value="">Todos los cursos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" 
                                <?php echo $curso_id == $curso['id'] ? 'selected' : ''; ?>>
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad'] . ' (' . $curso['turno'] . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="turno_filter">Turno:</label>
                    <select name="turno" id="turno_filter">
                        <option value="">Todos los turnos</option>
                        <option value="Manana" <?php echo $turno === 'Manana' ? 'selected' : ''; ?>>Manana</option>
                        <option value="Tarde" <?php echo $turno === 'Tarde' ? 'selected' : ''; ?>>Tarde</option>
                        <option value="Contraturno" <?php echo $turno === 'Contraturno' ? 'selected' : ''; ?>>Contraturno</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="horarios.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Grilla de horarios -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Horarios</h3>
            <div class="header-actions">
                <a href="horarios.php?action=exportar" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Exportar
                </a>
            </div>
        </div>
        <div class="horarios-grid">
            <?php foreach ($dias as $dia): ?>
            <div class="dia-column">
                <h4 class="dia-header"><?php echo $dia; ?></h4>
                <div class="horarios-dia">
                    <?php if (empty($horarios_por_dia[$dia])): ?>
                        <div class="no-horarios">Sin horarios</div>
                    <?php else: ?>
                        <?php foreach ($horarios_por_dia[$dia] as $horario): ?>
                        <div class="horario-item">
                            <div class="horario-hora">
                                <?php echo substr($horario['hora_inicio'], 0, 5) . ' - ' . substr($horario['hora_fin'], 0, 5); ?>
                            </div>
                            <div class="horario-materia">
                                <strong><?php echo htmlspecialchars($horario['materia_nombre']); ?></strong>
                            </div>
                            <div class="horario-curso">
                                <?php echo $horario['anio'] . '° ' . $horario['division'] . ' - ' . $horario['especialidad']; ?>
                            </div>
                            <?php if ($horario['aula']): ?>
                            <div class="horario-aula">
                                <i class="fas fa-door-open"></i> <?php echo htmlspecialchars($horario['aula']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($horario['docente']): ?>
                            <div class="horario-docente">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($horario['docente']); ?>
                            </div>
                            <?php endif; ?>
                            <div class="horario-actions">
                                <a href="horario_editar.php?id=<?php echo $horario['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="horario_eliminar.php?id=<?php echo $horario['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Está seguro de eliminar este horario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.horarios-section {
    max-width: 1400px;
    margin: 0 auto;
}

.horarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.dia-column {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.dia-header {
    background: #f8f9fa;
    padding: 15px;
    margin: 0;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
    font-size: 1.1em;
    font-weight: 600;
}

.horarios-dia {
    padding: 10px;
    min-height: 200px;
}

.horario-item {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.horario-hora {
    font-weight: 600;
    color: #007bff;
    margin-bottom: 5px;
}

.horario-materia {
    margin-bottom: 5px;
}

.horario-curso {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 5px;
}

.horario-aula, .horario-docente {
    font-size: 0.85em;
    color: #888;
    margin-bottom: 3px;
}

.horario-actions {
    margin-top: 8px;
    display: flex;
    gap: 5px;
}

.no-horarios {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 20px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .horarios-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?> 