<?php 
$pageTitle = 'Horarios - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$curso_filter = $_GET['curso'] ?? '';
$success_message = '';
$error_message = '';

// Solo admin y directivo pueden crear horarios
if ($action === 'nuevo' && !($auth->hasRole('admin') || $auth->hasRole('directivo'))) {
    header('Location: horarios.php?error=unauthorized');
    exit();
}

// Procesar formulario de nuevo horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_horario'])) {
    try {
        $sql = "INSERT INTO horarios (curso_id, materia_id, dia_semana, hora_inicio, hora_fin, aula, docente, es_contraturno) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_POST['curso_id'],
            $_POST['materia_id'],
            $_POST['dia_semana'],
            $_POST['hora_inicio'],
            $_POST['hora_fin'],
            $_POST['aula'] ?: null,
            $_POST['docente'] ?: null,
            isset($_POST['es_contraturno']) ? 1 : 0
        ];
        
        $db->query($sql, $params);
        $success_message = "Horario registrado correctamente";
        $action = '';
    } catch (Exception $e) {
        $error_message = "Error al registrar horario: " . $e->getMessage();
    }
}

// Construir consulta con filtros
$where_conditions = ["1=1"];
$params = [];

if ($curso_filter) {
    $where_conditions[] = "h.curso_id = ?";
    $params[] = $curso_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener horarios
$horarios = $db->fetchAll("
    SELECT h.*, c.anio, c.division, esp.nombre as especialidad, t.nombre as turno,
           m.nombre as materia, m.es_taller
    FROM horarios h
    JOIN cursos c ON h.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    LEFT JOIN materias m ON h.materia_id = m.id
    WHERE $where_clause AND c.activo = 1
    ORDER BY c.anio, c.division, h.dia_semana, h.hora_inicio
", $params);

// Obtener datos para formularios
$cursos = $db->fetchAll("
    SELECT c.id, c.anio, c.division, esp.nombre as especialidad, t.nombre as turno
    FROM cursos c
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    WHERE c.activo = 1
    ORDER BY c.anio, c.division
");

$materias = $db->fetchAll("SELECT * FROM materias ORDER BY nombre");

// Estadísticas
$total_horarios = count($horarios);
$contraturno_count = count(array_filter($horarios, function($h) { return $h['es_contraturno']; }));

$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes', 
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];
?>

<section class="horarios-section">
    <div class="section-header">
        <h2>Gestión de Horarios</h2>
        <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
        <a href="horarios.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Horario
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
            No tienes permisos para crear horarios.
        </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_horarios); ?></h3>
                <p>Total Horarios</p>
            </div>
        </div>
        
        <?php if ($contraturno_count > 0): ?>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-moon"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($contraturno_count); ?></h3>
                <p>Contraturno</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario nuevo horario -->
    <?php if ($action === 'nuevo'): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Nuevo Horario</h3>
        </div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso_id">Curso: *</label>
                    <select name="curso_id" id="curso_id" required>
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>">
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad'] . ' (' . $curso['turno'] . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="materia_id">Materia: *</label>
                    <select name="materia_id" id="materia_id" required>
                        <option value="">Seleccionar materia</option>
                        <?php foreach ($materias as $materia): ?>
                        <option value="<?php echo $materia['id']; ?>">
                            <?php echo htmlspecialchars($materia['nombre']); ?>
                            <?php echo $materia['es_taller'] ? ' (Taller)' : ''; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dia_semana">Día de la Semana: *</label>
                    <select name="dia_semana" id="dia_semana" required>
                        <option value="">Seleccionar día</option>
                        <?php foreach ($dias_semana as $num => $nombre): ?>
                        <option value="<?php echo $num; ?>"><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="hora_inicio">Hora de Inicio: *</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required>
                </div>
                
                <div class="form-group">
                    <label for="hora_fin">Hora de Fin: *</label>
                    <input type="time" name="hora_fin" id="hora_fin" required>
                </div>
                
                <div class="form-group">
                    <label for="aula">Aula:</label>
                    <input type="text" name="aula" id="aula" maxlength="50" placeholder="Ej: Aula 1, Lab. Informática">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="docente">Docente:</label>
                    <input type="text" name="docente" id="docente" maxlength="100" placeholder="Nombre del profesor">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="es_contraturno" id="es_contraturno">
                        Es Contraturno
                    </label>
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
        <form method="GET" class="form-container">
            <div class="form-row">
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

    <!-- Vista de horarios -->
    <?php if (!empty($horarios)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Horarios Registrados (<?php echo number_format($total_horarios); ?>)</h3>
        </div>
        
        <!-- Vista por curso -->
        <?php
        $horarios_por_curso = [];
        foreach ($horarios as $horario) {
            $curso_key = $horario['anio'] . '° ' . $horario['division'] . ' - ' . $horario['especialidad'];
            $horarios_por_curso[$curso_key][] = $horario;
        }
        ?>
        
        <div class="card-body">
            <?php foreach ($horarios_por_curso as $curso_nombre => $horarios_curso): ?>
            <div class="curso-horarios" style="margin-bottom: 2rem;">
                <h4 style="color: var(--primary-color); margin-bottom: 1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                    <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($curso_nombre); ?>
                    <span class="status status-primary" style="float: right; font-size: 0.75rem;">
                        <?php echo $horarios_curso[0]['turno']; ?>
                    </span>
                </h4>
                
                <!-- Tabla de horarios semanal -->
                <div class="horario-semanal" style="overflow-x: auto;">
                    <table class="table" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Horario</th>
                                <?php foreach ($dias_semana as $dia): ?>
                                <th><?php echo $dia; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Agrupar por hora de inicio
                            $horarios_por_hora = [];
                            foreach ($horarios_curso as $h) {
                                $hora_key = $h['hora_inicio'] . '-' . $h['hora_fin'];
                                $horarios_por_hora[$hora_key][$h['dia_semana']] = $h;
                            }
                            
                            foreach ($horarios_por_hora as $hora_key => $horarios_hora):
                                list($hora_inicio, $hora_fin) = explode('-', $hora_key);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('H:i', strtotime($hora_inicio)); ?></strong><br>
                                    <small><?php echo date('H:i', strtotime($hora_fin)); ?></small>
                                </td>
                                <?php foreach ($dias_semana as $dia_num => $dia_nombre): ?>
                                <td>
                                    <?php if (isset($horarios_hora[$dia_num])): 
                                        $h = $horarios_hora[$dia_num];
                                    ?>
                                        <div class="materia-cell" style="padding: 0.5rem; border-left: 3px solid var(--primary-color); background: var(--light-gray);">
                                            <strong><?php echo htmlspecialchars($h['materia']); ?></strong>
                                            <?php if ($h['es_taller']): ?>
                                                <span class="status status-warning" style="font-size: 0.7rem;">Taller</span>
                                            <?php endif; ?>
                                            <?php if ($h['es_contraturno']): ?>
                                                <span class="status status-danger" style="font-size: 0.7rem;">Contraturno</span>
                                            <?php endif; ?>
                                            <?php if ($h['aula']): ?>
                                                <br><small><i class="fas fa-door-open"></i> <?php echo htmlspecialchars($h['aula']); ?></small>
                                            <?php endif; ?>
                                            <?php if ($h['docente']): ?>
                                                <br><small><i class="fas fa-user"></i> <?php echo htmlspecialchars($h['docente']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Lista detallada -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista Detallada</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Materia</th>
                        <th>Día</th>
                        <th>Horario</th>
                        <th>Aula</th>
                        <th>Docente</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $horario): ?>
                    <tr>
                        <td>
                            <strong><?php echo $horario['anio'] . '° ' . $horario['division']; ?></strong>
                            <br><small><?php echo htmlspecialchars($horario['especialidad']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($horario['materia']); ?>
                            <?php if ($horario['es_taller']): ?>
                                <br><span class="status status-warning">Taller</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="fas fa-calendar-day"></i>
                            <?php echo $dias_semana[$horario['dia_semana']]; ?>
                        </td>
                        <td>
                            <strong><?php echo date('H:i', strtotime($horario['hora_inicio'])); ?></strong>
                            -
                            <strong><?php echo date('H:i', strtotime($horario['hora_fin'])); ?></strong>
                        </td>
                        <td>
                            <?php if ($horario['aula']): ?>
                                <i class="fas fa-door-open"></i> <?php echo htmlspecialchars($horario['aula']); ?>
                            <?php else: ?>
                                <span class="status status-warning">No asignada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($horario['docente']): ?>
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($horario['docente']); ?>
                            <?php else: ?>
                                <span class="status status-warning">No asignado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($horario['es_contraturno']): ?>
                                <span class="status status-danger">Contraturno</span>
                            <?php else: ?>
                                <span class="status status-success">Regular</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Estado vacío -->
    <div class="card">
        <div class="card-body text-center" style="padding: 3rem;">
            <i class="fas fa-clock" style="font-size: 4rem; color: var(--secondary-color); opacity: 0.3; margin-bottom: 1rem;"></i>
            <h3 style="color: var(--secondary-color); margin-bottom: 0.5rem;">No hay horarios registrados</h3>
            <p style="color: var(--secondary-color); margin-bottom: 2rem;">
                <?php if ($curso_filter): ?>
                    No se encontraron horarios para el curso seleccionado
                <?php else: ?>
                    Comienza registrando el primer horario de clases
                <?php endif; ?>
            </p>
            <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
            <a href="horarios.php?action=nuevo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Registrar Primer Horario
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<style>
.horario-semanal .materia-cell {
    min-height: 60px;
    font-size: 0.8rem;
    line-height: 1.3;
}

.horario-semanal td {
    vertical-align: top;
    padding: 0.5rem;
}

.horario-semanal th {
    text-align: center;
    background: var(--primary-color);
    color: white;
    font-weight: 600;
}

@media (max-width: 768px) {
    .horario-semanal {
        font-size: 0.75rem;
    }
    
    .materia-cell {
        min-height: 40px !important;
        padding: 0.25rem !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
