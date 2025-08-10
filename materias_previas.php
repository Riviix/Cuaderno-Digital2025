<?php 
$pageTitle = 'Materias Previas - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

if (!($auth->hasRole('admin') || $auth->hasRole('directivo'))) {
    header('Location: index.php?error=unauthorized');
    exit();
}

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_previa'])) {
    try {
        $db->query("INSERT INTO materias_previas (estudiante_id, materia_id, anio_previo, estado, observaciones) VALUES (?, ?, ?, ?, ?)", [
            $_POST['estudiante_id'],
            $_POST['materia_id'],
            $_POST['anio_previo'],
            $_POST['estado'],
            $_POST['observaciones'] ?: null
        ]);
        $success_message = 'Materia previa registrada';
        $action = '';
    } catch (Exception $e) {
        $error_message = 'Error al registrar: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_previa'])) {
    try {
        $db->query("DELETE FROM materias_previas WHERE id = ?", [$_POST['previa_id']]);
        $success_message = 'Registro eliminado';
    } catch (Exception $e) {
        $error_message = 'Error al eliminar: ' . $e->getMessage();
    }
}

$estudiantes = $db->fetchAll("\n    SELECT e.id, e.apellido, e.nombre, c.anio, c.division\n    FROM estudiantes e\n    LEFT JOIN cursos c ON e.curso_id = c.id\n    WHERE e.activo = 1\n    ORDER BY e.apellido, e.nombre\n");
$materias = $db->fetchAll("SELECT * FROM materias WHERE activa = 1 ORDER BY nombre");

$previas = $db->fetchAll("\n    SELECT p.*, e.apellido, e.nombre, m.nombre as materia\n    FROM materias_previas p\n    JOIN estudiantes e ON e.id = p.estudiante_id\n    JOIN materias m ON m.id = p.materia_id\n    ORDER BY e.apellido, e.nombre, p.anio_previo DESC\n");
?>

<section class="materias-previas-section">
    <div class="section-header">
        <h2>Materias Previas</h2>
        <a href="materias_previas.php?action=nueva" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Previa</a>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>

    <?php if ($action === 'nueva'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Registrar Materia Previa</h3></div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="estudiante_id">Estudiante *</label>
                    <select id="estudiante_id" name="estudiante_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($estudiantes as $e): ?>
                        <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['apellido'] . ', ' . $e['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="materia_id">Materia *</label>
                    <select id="materia_id" name="materia_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($materias as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="anio_previo">Año</label>
                    <input type="number" min="1" max="7" id="anio_previo" name="anio_previo" value="1" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="regularizada">Regularizada</option>
                        <option value="aprobada">Aprobada</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <input type="text" id="observaciones" name="observaciones" placeholder="Opcional">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="guardar_previa" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="materias_previas.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Listado</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Materia</th>
                        <th>Año</th>
                        <th>Estado</th>
                        <th>Obs.</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($previas as $p): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($p['apellido'] . ', ' . $p['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($p['materia']); ?></td>
                        <td><?php echo $p['anio_previo']; ?>°</td>
                        <td><?php echo ucfirst($p['estado']); ?></td>
                        <td><?php echo htmlspecialchars($p['observaciones'] ?? ''); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Eliminar registro?');">
                                <input type="hidden" name="previa_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="eliminar_previa" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 