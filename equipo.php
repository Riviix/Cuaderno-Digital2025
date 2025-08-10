<?php 
$pageTitle = 'Equipo Directivo - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

// Solo admin y directivo pueden acceder
if (!($auth->hasRole('admin') || $auth->hasRole('directivo'))) {
    header('Location: index.php?error=unauthorized');
    exit();
}

require_once 'config/database.php';
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$success_message = '';
$error_message = '';

// Procesar formulario de nuevo miembro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_miembro'])) {
    try {
        $sql = "INSERT INTO equipo_directivo (apellido, nombre, cargo, telefono, email, foto) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_POST['apellido'],
            $_POST['nombre'],
            $_POST['cargo'],
            $_POST['telefono'] ?: null,
            $_POST['email'] ?: null,
            $_POST['foto'] ?: null
        ];
        
        $db->query($sql, $params);
        $success_message = "Miembro del equipo directivo registrado correctamente";
        $action = '';
    } catch (Exception $e) {
        $error_message = "Error al registrar miembro: " . $e->getMessage();
    }
}

// Obtener equipo directivo
$equipo = $db->fetchAll("
    SELECT * FROM equipo_directivo 
    WHERE activo = 1 
    ORDER BY 
        CASE cargo 
            WHEN 'Director' THEN 1
            WHEN 'Vicedirector' THEN 2
            WHEN 'Secretario' THEN 3
            WHEN 'Prosecretario' THEN 4
            WHEN 'Regente' THEN 5
            WHEN 'Jefe de Taller' THEN 6
            ELSE 7
        END,
        apellido, nombre
");

$total_miembros = count($equipo);

$cargos_predefinidos = [
    'Director',
    'Vicedirector', 
    'Secretario',
    'Prosecretario',
    'Regente',
    'Jefe de Taller',
    'Coordinador de Área',
    'Bibliotecario',
    'Preceptor',
    'Otro'
];
?>

<section class="equipo-section">
    <div class="section-header">
        <h2>Equipo Directivo</h2>
        <a href="equipo.php?action=nuevo" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Miembro
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

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_miembros); ?></h3>
                <p>Total Miembros</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count(array_unique(array_column($equipo, 'cargo'))); ?></h3>
                <p>Cargos Diferentes</p>
            </div>
        </div>
    </div>

    <!-- Formulario nuevo miembro -->
    <?php if ($action === 'nuevo'): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Agregar Miembro del Equipo Directivo</h3>
        </div>
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="apellido">Apellido: *</label>
                    <input type="text" name="apellido" id="apellido" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre: *</label>
                    <input type="text" name="nombre" id="nombre" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="cargo">Cargo: *</label>
                    <select name="cargo" id="cargo" required>
                        <option value="">Seleccionar cargo</option>
                        <?php foreach ($cargos_predefinidos as $cargo): ?>
                        <option value="<?php echo $cargo; ?>"><?php echo $cargo; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" name="telefono" id="telefono" maxlength="20" 
                           placeholder="Ej: 223-1234567">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" maxlength="100" 
                           placeholder="Ej: director@eest2.edu.ar">
                </div>
                
                <div class="form-group">
                    <label for="foto">URL Foto:</label>
                    <input type="url" name="foto" id="foto" maxlength="255" 
                           placeholder="https://ejemplo.com/foto.jpg">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_miembro" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Miembro
                </button>
                <a href="equipo.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Lista del equipo -->
    <?php if (!empty($equipo)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Miembros del Equipo Directivo (<?php echo number_format($total_miembros); ?>)</h3>
        </div>
        <div class="card-body">
            <div class="equipo-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
                <?php foreach ($equipo as $miembro): ?>
                <div class="miembro-card" style="border: 1px solid var(--medium-gray); border-radius: var(--border-radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); transition: transform 0.2s;">
                    <div class="miembro-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div class="miembro-foto" style="flex-shrink: 0;">
                            <?php if ($miembro['foto']): ?>
                                <img src="<?php echo htmlspecialchars($miembro['foto']); ?>" 
                                     alt="Foto de <?php echo htmlspecialchars($miembro['nombre']); ?>"
                                     style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color);">
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="miembro-info">
                            <h4 style="margin-bottom: 0.25rem; color: var(--text-color);">
                                <?php echo htmlspecialchars($miembro['apellido'] . ', ' . $miembro['nombre']); ?>
                            </h4>
                            <span class="status status-primary" style="font-size: 0.875rem;">
                                <?php echo htmlspecialchars($miembro['cargo']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="miembro-contacto" style="border-top: 1px solid var(--medium-gray); padding-top: 1rem;">
                        <?php if ($miembro['telefono']): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-size: 0.875rem;">
                            <i class="fas fa-phone" style="color: var(--secondary-color);"></i>
                            <span><?php echo htmlspecialchars($miembro['telefono']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($miembro['email']): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                            <i class="fas fa-envelope" style="color: var(--secondary-color);"></i>
                            <a href="mailto:<?php echo htmlspecialchars($miembro['email']); ?>" 
                               style="color: var(--primary-color); text-decoration: none;">
                                <?php echo htmlspecialchars($miembro['email']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$miembro['telefono'] && !$miembro['email']): ?>
                        <small style="color: var(--secondary-color); font-style: italic;">
                            No hay información de contacto registrada
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Vista de tabla -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Vista de Lista</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Apellido y Nombre</th>
                        <th>Cargo</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipo as $miembro): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <?php if ($miembro['foto']): ?>
                                    <img src="<?php echo htmlspecialchars($miembro['foto']); ?>" 
                                         alt="Foto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($miembro['apellido'] . ', ' . $miembro['nombre']); ?></strong>
                            </div>
                        </td>
                        <td>
                            <span class="status status-primary">
                                <?php echo htmlspecialchars($miembro['cargo']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($miembro['telefono']): ?>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($miembro['telefono']); ?>
                            <?php else: ?>
                                <span style="color: var(--secondary-color);">No registrado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($miembro['email']): ?>
                                <a href="mailto:<?php echo htmlspecialchars($miembro['email']); ?>" 
                                   style="color: var(--primary-color);">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($miembro['email']); ?>
                                </a>
                            <?php else: ?>
                                <span style="color: var(--secondary-color);">No registrado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-success">Activo</span>
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
            <i class="fas fa-users" style="font-size: 4rem; color: var(--secondary-color); opacity: 0.3; margin-bottom: 1rem;"></i>
            <h3 style="color: var(--secondary-color); margin-bottom: 0.5rem;">No hay miembros registrados</h3>
            <p style="color: var(--secondary-color); margin-bottom: 2rem;">
                Comienza agregando los miembros del equipo directivo de la institución
            </p>
            <a href="equipo.php?action=nuevo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Primer Miembro
            </a>
        </div>
    </div>
    <?php endif; ?>
</section>

<style>
.miembro-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

@media (max-width: 768px) {
    .equipo-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
