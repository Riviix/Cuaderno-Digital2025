<?php 
$pageTitle = 'Notas - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$curso_filter = $_GET['curso'] ?? '';
$estudiante_filter = $_GET['estudiante'] ?? '';
$materia_filter = $_GET['materia'] ?? '';
$trimestre_filter = $_GET['trimestre'] ?? '';
$success_message = '';
$error_message = '';

// Solo admin/directivo pueden modificar notas
$can_manage = ($auth->hasRole('admin') || $auth->hasRole('directivo'));

// Alta de nota
if ($can_manage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_nota'])) {
    try {
        $sql = "INSERT INTO notas (estudiante_id, materia_id, trimestre, nota, observaciones, usuario_id) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $_POST['estudiante_id'],
            $_POST['materia_id'],
            (int)$_POST['trimestre'],
            $_POST['nota'] !== '' ? $_POST['nota'] : null,
            $_POST['observaciones'] ?: null,
            $_SESSION['user_id']
        ];
        $db->query($sql, $params);
        $success_message = "Nota registrada correctamente";
        $action = '';
    } catch (Exception $e) {
        $error_message = "Error al registrar nota: " . $e->getMessage();
    }
}

// Borrado de nota
if ($can_manage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_nota'])) {
    try {
        $db->query("DELETE FROM notas WHERE id = ?", [$_POST['nota_id']]);
        $success_message = "Nota eliminada";
    } catch (Exception $e) {
        $error_message = "Error al eliminar nota: " . $e->getMessage();
    }
}

// Datos para formularios y filtros
$cursos = $db->fetchAll("\n    SELECT c.id, c.anio, c.division, esp.nombre as especialidad\n    FROM cursos c\n    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id\n    WHERE c.activo = 1\n    ORDER BY c.anio, c.division\n");

$estudiantes = $db->fetchAll("\n    SELECT e.id, e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad\n    FROM estudiantes e\n    LEFT JOIN cursos c ON e.curso_id = c.id\n    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id\n    WHERE e.activo = 1\n    ORDER BY e.apellido, e.nombre\n");

$materias = $db->fetchAll("SELECT * FROM materias WHERE activa = 1 ORDER BY nombre");

// Construir filtros
$where = ["1=1"]; $params = [];
if ($curso_filter) { $where[] = "e.curso_id = ?"; $params[] = $curso_filter; }
if ($estudiante_filter) { $where[] = "e.id = ?"; $params[] = $estudiante_filter; }
if ($materia_filter) { $where[] = "n.materia_id = ?"; $params[] = $materia_filter; }
if ($trimestre_filter) { $where[] = "n.trimestre = ?"; $params[] = $trimestre_filter; }
$where_clause = implode(' AND ', $where);

// Obtener notas
$notas = $db->fetchAll("\n    SELECT n.*, e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad, m.nombre as materia\n    FROM notas n\n    JOIN estudiantes e ON n.estudiante_id = e.id\n    LEFT JOIN cursos c ON e.curso_id = c.id\n    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id\n    JOIN materias m ON n.materia_id = m.id\n    WHERE $where_clause\n    ORDER BY c.anio, c.division, e.apellido, e.nombre, m.nombre, n.trimestre\n    LIMIT 300\n", $params);

// Estadísticas simples
$total_notas = count($notas);
?>

<section class="notas-section">
    <div class="section-header">
        <h2>Gestión de Notas</h2>
        <?php if ($can_manage): ?>
        <a href="notas.php?action=nueva" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Nota
        </a>
        <?php endif; ?>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-clipboard-check"></i></div>
            <div class="stat-content">
                <h3><?php echo number_format($total_notas); ?></h3>
                <p>Total de Notas</p>
            </div>
        </div>
    </div>

    <?php if ($can_manage && $action === 'nueva'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Cargar Nueva Nota</h3></div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="estudiante_id">Estudiante *</label>
                    <select name="estudiante_id" id="estudiante_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <option value="<?php echo $est['id']; ?>">
                            <?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']) . ' - ' . ($est['anio'] ? $est['anio'] . '° ' . $est['division'] : 'Sin curso'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="materia_id">Materia *</label>
                    <select name="materia_id" id="materia_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($materias as $mat): ?>
                        <option value="<?php echo $mat['id']; ?>"><?php echo htmlspecialchars($mat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="trimestre">Trimestre *</label>
                    <select name="trimestre" id="trimestre" required>
                        <option value="">Seleccionar</option>
                        <option value="1">1°</option>
                        <option value="2">2°</option>
                        <option value="3">3°</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="nota">Nota (numérica)</label>
                    <input type="number" step="0.01" min="0" max="10" name="nota" id="nota" placeholder="Ej: 7.50">
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <input type="text" name="observaciones" id="observaciones" placeholder="Observaciones (opcional)">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="guardar_nota" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="notas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Filtros</h3></div>
        <form method="GET" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="curso">Curso</label>
                    <select name="curso" id="curso">
                        <option value="">Todos</option>
                        <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id']; ?>" <?php echo $curso_filter == $curso['id'] ? 'selected' : ''; ?>>
                            <?php echo $curso['anio'] . '° ' . $curso['division'] . ' - ' . $curso['especialidad']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estudiante">Estudiante</label>
                    <select name="estudiante" id="estudiante">
                        <option value="">Todos</option>
                        <?php foreach ($estudiantes as $est): ?>
                        <option value="<?php echo $est['id']; ?>" <?php echo $estudiante_filter == $est['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="materia">Materia</label>
                    <select name="materia" id="materia">
                        <option value="">Todas</option>
                        <?php foreach ($materias as $mat): ?>
                        <option value="<?php echo $mat['id']; ?>" <?php echo $materia_filter == $mat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mat['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="trimestre">Trimestre</label>
                    <select name="trimestre" id="trimestre">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $trimestre_filter==='1'?'selected':''; ?>>1°</option>
                        <option value="2" <?php echo $trimestre_filter==='2'?'selected':''; ?>>2°</option>
                        <option value="3" <?php echo $trimestre_filter==='3'?'selected':''; ?>>3°</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                <a href="notas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Listado de Notas (<?php echo number_format($total_notas); ?>)</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Estudiante</th>
                        <th>Materia</th>
                        <th>Trimestre</th>
                        <th>Nota</th>
                        <th>Obs.</th>
                        <?php if ($can_manage): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notas as $n): ?>
                    <tr>
                        <td><?php echo ($n['anio'] ? ($n['anio'] . '° ' . $n['division'] . ' - ' . $n['especialidad']) : 'Sin curso'); ?></td>
                        <td><strong><?php echo htmlspecialchars($n['apellido'] . ', ' . $n['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($n['materia']); ?></td>
                        <td><?php echo $n['trimestre']; ?>°</td>
                        <td><?php echo $n['nota'] !== null ? $n['nota'] : '-'; ?></td>
                        <td><?php echo htmlspecialchars($n['observaciones'] ?? ''); ?></td>
                        <?php if ($can_manage): ?>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Eliminar esta nota?');">
                                <input type="hidden" name="nota_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" name="eliminar_nota" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 