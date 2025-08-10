<?php 
$pageTitle = 'Estudiantes - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';

// Procesar formulario de nuevo estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_estudiante'])) {
    try {
        $sql = "INSERT INTO estudiantes (dni, apellido, nombre, fecha_nacimiento, grupo_sanguineo, 
                obra_social, domicilio, telefono_fijo, telefono_celular, email, curso_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_POST['dni'],
            $_POST['apellido'],
            $_POST['nombre'],
            $_POST['fecha_nacimiento'] ?: null,
            $_POST['grupo_sanguineo'] ?: null,
            $_POST['obra_social'] ?: null,
            $_POST['domicilio'] ?: null,
            $_POST['telefono_fijo'] ?: null,
            $_POST['telefono_celular'] ?: null,
            $_POST['email'] ?: null,
            $_POST['curso_id'] ?: null
        ];
        
        $db->query($sql, $params);
        $success_message = "Estudiante registrado correctamente";
        $action = ''; // Limpiar acción para ocultar formulario
    } catch (Exception $e) {
        $error_message = "Error al registrar estudiante: " . $e->getMessage();
    }
}

// Filtros
$curso_filter = $_GET['curso'] ?? '';
$search = $_GET['search'] ?? '';

// Construir consulta con filtros
$where_conditions = ["e.activo = 1"];
$params = [];

if ($curso_filter) {
    $where_conditions[] = "e.curso_id = ?";
    $params[] = $curso_filter;
}

if ($search) {
    $where_conditions[] = "(e.apellido LIKE ? OR e.nombre LIKE ? OR e.dni LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener estudiantes
$estudiantes = $db->fetchAll("
    SELECT e.*, c.anio, c.division, esp.nombre as especialidad, t.nombre as turno
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    WHERE $where_clause
    ORDER BY e.apellido, e.nombre
    LIMIT 100
", $params);

// Obtener cursos para formulario y filtros
$cursos = $db->fetchAll("
    SELECT c.id, c.anio, c.division, esp.nombre as especialidad, t.nombre as turno
    FROM cursos c
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    WHERE c.activo = 1
    ORDER BY c.anio, c.division
");

// Estadísticas
$total_estudiantes = $db->fetch("
    SELECT COUNT(*) as total FROM estudiantes e
    WHERE $where_clause
", $params)['total'];

$estudiantes_sin_curso = $db->fetch("
    SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1 AND curso_id IS NULL
")['total'];
?>

<section class="estudiantes-section">
    <div class="section-header">
        <h2>Gestión de Estudiantes</h2>
        <a href="estudiantes.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Estudiante
        </a>
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

    <!-- Estadísticas rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_estudiantes); ?></h3>
                <p>Total Estudiantes</p>
            </div>
        </div>
        
        <?php if ($estudiantes_sin_curso > 0): ?>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($estudiantes_sin_curso); ?></h3>
                <p>Sin Curso Asignado</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario nuevo estudiante -->
    <?php if ($action === 'nuevo'): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Nuevo Estudiante</h3>
        </div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="dni">DNI: *</label>
                    <input type="text" name="dni" id="dni" required maxlength="20" 
                           placeholder="Ej: 12345678">
                </div>
                
                <div class="form-group">
                    <label for="apellido">Apellido: *</label>
                    <input type="text" name="apellido" id="apellido" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre: *</label>
                    <input type="text" name="nombre" id="nombre" required maxlength="100">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento">
                </div>
                
                <div class="form-group">
                    <label for="grupo_sanguineo">Grupo Sanguíneo:</label>
                    <select name="grupo_sanguineo" id="grupo_sanguineo">
                        <option value="">Seleccionar</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="obra_social">Obra Social:</label>
                    <input type="text" name="obra_social" id="obra_social" maxlength="100">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telefono_fijo">Teléfono Fijo:</label>
                    <input type="tel" name="telefono_fijo" id="telefono_fijo" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="telefono_celular">Teléfono Celular:</label>
                    <input type="tel" name="telefono_celular" id="telefono_celular" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" maxlength="100">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="domicilio">Domicilio:</label>
                    <textarea name="domicilio" id="domicilio" placeholder="Dirección completa"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="curso_id">Curso:</label>
                    <select name="curso_id" id="curso_id">
                        <option value="">Asignar después</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>">
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad'] . ' (' . $curso['turno'] . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_estudiante" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Estudiante
                </button>
                <a href="estudiantes.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <form method="GET" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Buscar:</label>
                    <input type="text" name="search" id="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Estudiantes Registrados (<?php echo number_format($total_estudiantes); ?>)</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Apellido y Nombre</th>
                        <th>Curso</th>
                        <th>Turno</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($estudiantes)): ?>
                        <?php foreach ($estudiantes as $estudiante): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($estudiante['dni']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></strong>
                                <?php if ($estudiante['fecha_nacimiento']): ?>
                                <br><small>
                                    <i class="fas fa-birthday-cake"></i>
                                    <?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?>
                                    (<?php echo floor((time() - strtotime($estudiante['fecha_nacimiento'])) / (365.25 * 24 * 3600)); ?> años)
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($estudiante['anio']): ?>
                                    <span class="status status-success">
                                        <?php echo $estudiante['anio'] . '° ' . $estudiante['division']; ?>
                                    </span>
                                    <br><small><?php echo htmlspecialchars($estudiante['especialidad']); ?></small>
                                <?php else: ?>
                                    <span class="status status-warning">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($estudiante['turno']): ?>
                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($estudiante['turno']); ?>
                                <?php else: ?>
                                    <span class="status status-warning">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($estudiante['telefono_celular']): ?>
                                    <i class="fas fa-mobile-alt"></i> <?php echo htmlspecialchars($estudiante['telefono_celular']); ?>
                                <?php elseif ($estudiante['telefono_fijo']): ?>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($estudiante['telefono_fijo']); ?>
                                <?php else: ?>
                                    <span class="status status-warning">Sin teléfono</span>
                                <?php endif; ?>
                                <?php if ($estudiante['email']): ?>
                                    <br><small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($estudiante['email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="estudiante_ficha.php?id=<?php echo $estudiante['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="Ver ficha completa">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
                                <a href="notas.php?estudiante=<?php echo $estudiante['id']; ?>" 
                                   class="btn btn-sm btn-secondary" title="Ver notas">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>
                                <?php endif; ?>
                                <a href="llamados.php?estudiante=<?php echo $estudiante['id']; ?>" 
                                   class="btn btn-sm btn-danger" title="Ver llamados">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 2rem; color: var(--secondary-color);">
                                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <br>No se encontraron estudiantes con los criterios especificados
                                <br><small>Prueba modificando los filtros de búsqueda</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
