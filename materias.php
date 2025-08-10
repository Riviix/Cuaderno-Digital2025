<?php 
$pageTitle = 'Materias - Cuaderno Digital E.E.S.T N°2';
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

// Crear materia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_materia'])) {
    try {
        $db->query("INSERT INTO materias (nombre, especialidad_id, es_taller, activa) VALUES (?, ?, ?, 1)", [
            $_POST['nombre'],
            $_POST['especialidad_id'] ?: null,
            isset($_POST['es_taller']) ? 1 : 0
        ]);
        $success_message = 'Materia creada';
        $action = '';
    } catch (Exception $e) {
        $error_message = 'Error al crear materia: ' . $e->getMessage();
    }
}

// Eliminar (desactivar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desactivar_materia'])) {
    try {
        $db->query("UPDATE materias SET activa = 0 WHERE id = ?", [$_POST['materia_id']]);
        $success_message = 'Materia desactivada';
    } catch (Exception $e) {
        $error_message = 'Error al desactivar: ' . $e->getMessage();
    }
}

$especialidades = $db->fetchAll("SELECT * FROM especialidades WHERE activa = 1 ORDER BY nombre");
$materias = $db->fetchAll("SELECT m.*, e.nombre as especialidad FROM materias m LEFT JOIN especialidades e ON e.id = m.especialidad_id WHERE m.activa = 1 ORDER BY m.nombre");
?>

<section class="materias-section">
    <div class="section-header">
        <h2>Materias</h2>
        <a href="materias.php?action=nueva" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Materia</a>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>

    <?php if ($action === 'nueva'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Crear Materia</h3></div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="especialidad_id">Especialidad</label>
                    <select id="especialidad_id" name="especialidad_id">
                        <option value="">Sin especialidad</option>
                        <?php foreach ($especialidades as $esp): ?>
                        <option value="<?php echo $esp['id']; ?>"><?php echo htmlspecialchars($esp['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="es_taller"> Es taller</label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="guardar_materia" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="materias.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
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
                        <th>Nombre</th>
                        <th>Especialidad</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materias as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($m['especialidad'] ?? '-'); ?></td>
                        <td><?php echo $m['es_taller'] ? '<span class="status status-warning">Taller</span>' : 'Materia'; ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Desactivar materia?');">
                                <input type="hidden" name="materia_id" value="<?php echo $m['id']; ?>">
                                <button type="submit" name="desactivar_materia" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Desactivar</button>
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