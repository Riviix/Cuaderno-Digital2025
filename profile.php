<?php
require_once 'includes/auto_seed.php';

$pageTitle = 'Mi Perfil - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php';

require_once 'config/database.php';
$db = Database::getInstance();

$currentUser = $auth->getCurrentUser();
$error = '';
$success = '';

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Error de seguridad. Intente nuevamente.';
    } else {
        switch ($_POST['action']) {
            case 'change_password':
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if ($newPassword !== $confirmPassword) {
                    $error = 'Las contraseñas nuevas no coinciden.';
                } else {
                    $result = $auth->changePassword($currentUser['id'], $currentPassword, $newPassword);
                    if ($result['success']) {
                        $success = $result['message'];
                        Logger::userActivity($currentUser['id'], 'password_changed');
                    } else {
                        $error = $result['error'];
                    }
                }
                break;
        }
    }
}

// Obtener información adicional del usuario
$userInfo = $db->fetch("SELECT * FROM usuarios WHERE id = ?", [$currentUser['id']]);
?>
<main class="main-content">
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
        <p>Gestiona tu información personal y configuración de seguridad</p>
    </div>

    <div class="profile-grid grid-2">
        <!-- Información del perfil -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Información Personal
                </h3>
            </div>
            <div class="card-body">
                <div class="profile-info">
                    <div class="info-group">
                        <label>Usuario:</label>
                        <span><?php echo Security::escape($currentUser['username']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Nombre:</label>
                        <span><?php echo Security::escape($currentUser['nombre']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Apellido:</label>
                        <span><?php echo Security::escape($currentUser['apellido']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Rol:</label>
                        <span class="badge badge-<?php echo $currentUser['rol'] === 'admin' ? 'danger' : ($currentUser['rol'] === 'directivo' ? 'warning' : 'info'); ?>">
                            <?php echo Security::escape(ucfirst($currentUser['rol'])); ?>
                        </span>
                    </div>
                    <div class="info-group">
                        <label>Último acceso:</label>
                        <span><?php echo isset($_SESSION['login_time']) ? date('d/m/Y H:i:s', $_SESSION['login_time']) : 'N/A'; ?></span>
                    </div>
                    <div class="info-group">
                        <label>IP de acceso:</label>
                        <span><?php echo Security::escape($_SESSION['ip_address'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cambio de contraseña -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lock"></i> Cambiar Contraseña
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" class="form" onsubmit="return App.validateForm(this);">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrfToken); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Contraseña Actual:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" id="new_password" name="new_password" required 
                               minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                               title="Debe contener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números">
                        <small class="form-text">
                            La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Actividad reciente -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Actividad Reciente
            </h3>
        </div>
        <div class="card-body">
            <div class="activity-list">
                <?php
                // Obtener actividad reciente del usuario (últimos 10 registros)
                $activities = $db->fetchAll("
                    SELECT 'login' as type, fecha_creacion as fecha, 'Inicio de sesión' as descripcion
                    FROM logins 
                    WHERE usuario_id = ? 
                    UNION ALL
                    SELECT 'logout' as type, fecha_creacion as fecha, 'Cierre de sesión' as descripcion
                    FROM logouts 
                    WHERE usuario_id = ?
                    ORDER BY fecha DESC 
                    LIMIT 10
                ", [$currentUser['id'], $currentUser['id']]);
                
                if (empty($activities)): ?>
                    <p class="text-muted">No hay actividad reciente para mostrar.</p>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo $activity['type'] === 'login' ? 'sign-in-alt' : 'sign-out-alt'; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-description">
                                    <?php echo Security::escape($activity['descripcion']); ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('d/m/Y H:i:s', strtotime($activity['fecha'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Configuración de seguridad -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shield-alt"></i> Configuración de Seguridad
            </h3>
        </div>
        <div class="card-body">
            <div class="security-settings">
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Sesión Activa</h4>
                        <p>Tu sesión actual está activa y segura.</p>
                    </div>
                    <div class="setting-status">
                        <span class="badge badge-success">Activa</span>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Verificación de IP</h4>
                        <p>Tu sesión está vinculada a tu dirección IP actual.</p>
                    </div>
                    <div class="setting-status">
                        <span class="badge badge-info"><?php echo Security::escape($_SESSION['ip_address'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Timeout de Sesión</h4>
                        <p>Tu sesión expirará automáticamente después de 8 horas de inactividad.</p>
                    </div>
                    <div class="setting-status">
                        <span class="badge badge-warning">8 horas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-grid {
    gap: 2rem;
    margin-bottom: 2rem;
}

.profile-info .info-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.profile-info .info-group:last-child {
    border-bottom: none;
}

.profile-info .info-group label {
    font-weight: 600;
    color: #555;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e3f2fd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #1976d2;
}

.activity-content {
    flex: 1;
}

.activity-description {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.875rem;
    color: #666;
}

.security-settings .setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.security-settings .setting-item:last-child {
    border-bottom: none;
}

.setting-info h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.setting-info p {
    margin: 0;
    color: #666;
    font-size: 0.875rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

.badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.badge-danger {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
</main>
<?php include 'includes/footer.php'; ?> 