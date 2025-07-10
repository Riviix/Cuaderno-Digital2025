<?php
$pageTitle = 'Equipo Directivo - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';
require_once 'config/database.php';
$db = Database::getInstance();

// Solo admin/directivo
if (!($auth->hasRole('admin') || $auth->hasRole('directivo'))) {
    header('Location: index.php');
    exit();
}

// Acciones: nuevo, editar, eliminar
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Procesar formulario de alta/edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $cargo = $_POST['cargo'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $usuario_id = $_POST['usuario_id'] ?: null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $foto = null;

    // Subida de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = 'equipo_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $dest = 'img/' . $filename;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
            $foto = $dest;
        }
    }

    if ($action === 'editar' && $id) {
        // Actualizar
        $params = [$apellido, $nombre, $cargo, $telefono, $email, $usuario_id, $activo, $id];
        $sql = "UPDATE equipo_directivo SET apellido=?, nombre=?, cargo=?, telefono=?, email=?, usuario_id=?, activo=? WHERE id=?";
        $db->query($sql, $params);
        if ($foto) {
            $db->query("UPDATE equipo_directivo SET foto=? WHERE id=?", [$foto, $id]);
        }
        $success = 'Miembro actualizado correctamente.';
    } else {
        // Nuevo
        $params = [$apellido, $nombre, $cargo, $telefono, $email, $foto, $usuario_id, $activo];
        $sql = "INSERT INTO equipo_directivo (apellido, nombre, cargo, telefono, email, foto, usuario_id, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $db->query($sql, $params);
        $success = 'Miembro agregado correctamente.';
    }
    header('Location: equipo.php?success=1');
    exit();
}

// Eliminar
if ($action === 'eliminar' && $id) {
    $db->query("DELETE FROM equipo_directivo WHERE id=?", [$id]);
    header('Location: equipo.php?success=1');
    exit();
}

// Listado
$equipo = $db->fetchAll("SELECT ed.*, u.username, u.rol FROM equipo_directivo ed LEFT JOIN usuarios u ON ed.usuario_id = u.id ORDER BY activo DESC, cargo, apellido, nombre");
$usuarios = $db->fetchAll("SELECT id, username, nombre, apellido, rol FROM usuarios WHERE activo = 1 ORDER BY apellido, nombre");

?>
<section class="equipo-section">
    <div class="section-header">
        <h2>Equipo Directivo</h2>
        <a href="equipo.php?action=nuevo" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Miembro</a>
    </div>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Operación realizada correctamente.</div>
    <?php endif; ?>
    <?php if ($action === 'nuevo' || ($action === 'editar' && $id)):
        $miembro = ['apellido'=>'','nombre'=>'','cargo'=>'','telefono'=>'','email'=>'','foto'=>'','usuario_id'=>'','activo'=>1];
        if ($action === 'editar' && $id) {
            $miembro = $db->fetch("SELECT * FROM equipo_directivo WHERE id=?", [$id]);
        }
    ?>
    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Apellido:</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($miembro['apellido']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($miembro['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Cargo:</label>
                    <input type="text" name="cargo" value="<?php echo htmlspecialchars($miembro['cargo']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($miembro['telefono']); ?>">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($miembro['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Usuario vinculado:</label>
                    <select name="usuario_id">
                        <option value="">Sin usuario</option>
                        <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php if ($miembro['usuario_id']==$u['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($u['apellido'] . ', ' . $u['nombre'] . ' (' . $u['username'] . ' - ' . $u['rol'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foto:</label>
                    <input type="file" name="foto" accept="image/*">
                    <?php if ($miembro['foto']): ?>
                        <img src="<?php echo htmlspecialchars($miembro['foto']); ?>" alt="Foto" style="max-width:80px;max-height:80px;margin-top:5px;">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="activo" value="1" <?php if ($miembro['activo']) echo 'checked'; ?>> Activo</label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                <a href="equipo.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Apellido y Nombre</th>
                        <th>Cargo</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipo as $m): ?>
                    <tr>
                        <td><?php if ($m['foto']): ?><img src="<?php echo htmlspecialchars($m['foto']); ?>" alt="Foto" style="max-width:50px;max-height:50px;"> <?php endif; ?></td>
                        <td><?php echo htmlspecialchars($m['apellido'] . ', ' . $m['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($m['cargo']); ?></td>
                        <td><?php echo htmlspecialchars($m['username']); ?></td>
                        <td><?php echo htmlspecialchars($m['rol']); ?></td>
                        <td><?php echo htmlspecialchars($m['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($m['email']); ?></td>
                        <td><?php echo $m['activo'] ? '<span class=\'status status-active\'>Sí</span>' : '<span class=\'status status-inactive\'>No</span>'; ?></td>
                        <td>
                            <a href="equipo.php?action=editar&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="equipo.php?action=eliminar&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este miembro?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($equipo)): ?>
                    <tr><td colspan="9" class="text-center">No hay miembros registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?> 