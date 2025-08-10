<?php 
$pageTitle = 'Talleres - Cuaderno Digital E.E.S.T N°2';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_taller'])) {
    try {
        $db->query("INSERT INTO talleres (nombre, especialidad_id, descripcion, activo) VALUES (?, ?, ?, 1)", [
            $_POST['nombre'],
            $_POST['especialidad_id'] ?: null,
            $_POST['descripcion'] ?: null
        ]);
        $success_message = 'Taller creado';
        $action = '';
    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desactivar_taller'])) {
    try {
        $db->query("UPDATE talleres SET activo = 0 WHERE id = ?", [$_POST['taller_id']]);
        $success_message = 'Taller desactivado';
    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}

$especialidades = $db->fetchAll("SELECT * FROM especialidades WHERE activa = 1 ORDER BY nombre");
$talleres = $db->fetchAll("SELECT t.*, e.nombre as especialidad FROM talleres t LEFT JOIN especialidades e ON e.id = t.especialidad_id WHERE t.activo = 1 ORDER BY t.nombre");
?>

<section class="talleres-section">
    <div class="section-header">
        <h2>Talleres</h2>
        <a href="talleres.php?action=nuevo" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Taller</a>
    </div>

    <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>

    <?php if ($action === 'nuevo'): ?>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Crear Taller</h3></div>
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
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="guardar_taller" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="talleres.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($talleres as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($t['especialidad'] ?? '-'); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Desactivar taller?');">
                                <input type="hidden" name="taller_id" value="<?php echo $t['id']; ?>">
                                <button type="submit" name="desactivar_taller" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Desactivar</button>
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