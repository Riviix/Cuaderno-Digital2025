<?php
/**
 * Script de Instalación - Cuaderno Digital EEST N°2
 * Este script configura automáticamente el sistema
 */

// Verificar si ya está instalado
if (file_exists('config/.env') && !isset($_GET['force'])) {
    die('El sistema ya está instalado. Si deseas reinstalar, agrega ?force=1 a la URL.');
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? 'cuaderno_digital_eest2';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    $appUrl = $_POST['app_url'] ?? 'http://localhost';
    $adminUser = $_POST['admin_user'] ?? 'admin';
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminName = $_POST['admin_name'] ?? 'Administrador';
    $adminEmail = $_POST['admin_email'] ?? 'admin@eest2.edu.ar';
    
    try {
        // Paso 1: Probar conexión a base de datos
        if ($step == 1) {
            $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear base de datos si no existe
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");
            
            // Importar esquema
            $schema = file_get_contents('database/schema.sql');
            $pdo->exec($schema);
            
            $step = 2;
            $success = 'Base de datos configurada correctamente.';
        }
        
        // Paso 2: Crear archivo de configuración
        elseif ($step == 2) {
            // Generar claves de seguridad
            $sessionSecret = bin2hex(random_bytes(32));
            $passwordSalt = bin2hex(random_bytes(16));
            
            $envContent = "# Configuración de la base de datos\n";
            $envContent .= "DB_HOST=$dbHost\n";
            $envContent .= "DB_NAME=$dbName\n";
            $envContent .= "DB_USER=$dbUser\n";
            $envContent .= "DB_PASS=$dbPass\n\n";
            $envContent .= "# Configuración de la aplicación\n";
            $envContent .= "APP_NAME=\"Cuaderno Digital EEST N°2\"\n";
            $envContent .= "APP_URL=$appUrl\n";
            $envContent .= "APP_ENV=production\n";
            $envContent .= "APP_DEBUG=false\n\n";
            $envContent .= "# Configuración de seguridad\n";
            $envContent .= "SESSION_SECRET=$sessionSecret\n";
            $envContent .= "PASSWORD_SALT=$passwordSalt\n\n";
            $envContent .= "# Configuración de logs\n";
            $envContent .= "LOG_LEVEL=info\n";
            $envContent .= "LOG_FILE=logs/app.log\n";
            
            file_put_contents('config/.env', $envContent);
            
            $step = 3;
            $success = 'Archivo de configuración creado correctamente.';
        }
        
        // Paso 3: Crear usuario administrador
        elseif ($step == 3) {
            require_once 'config/config.php';
            require_once 'includes/Security.php';
            
            $db = Database::getInstance();
            
            // Crear usuario administrador
            $hashedPassword = Security::hashPassword($adminPass);
            $sql = "INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, 'admin', 1, NOW())";
            $db->query($sql, [$adminUser, $hashedPassword, $adminName, 'Sistema', $adminEmail]);
            
            // Crear directorios necesarios
            $directories = ['logs', 'uploads'];
            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            
            $step = 4;
            $success = 'Usuario administrador creado correctamente.';
        }
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Cuaderno Digital EEST N°2</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .install-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
        }
        
        .step.active {
            background: #3498db;
            color: white;
        }
        
        .step.completed {
            background: #27ae60;
            color: white;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .requirement {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .requirement:last-child {
            border-bottom: none;
        }
        
        .status {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status.ok {
            background: #d4edda;
            color: #155724;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> Instalación del Sistema</h1>
            <p>Cuaderno Digital EEST N°2</p>
        </div>
        
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'completed' : 'active'; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? 'completed' : ($step == 2 ? 'active' : ''); ?>">2</div>
            <div class="step <?php echo $step >= 3 ? 'completed' : ($step == 3 ? 'active' : ''); ?>">3</div>
            <div class="step <?php echo $step >= 4 ? 'completed' : ($step == 4 ? 'active' : ''); ?>">4</div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Paso 1: Verificar requisitos -->
        <?php if ($step == 1): ?>
            <h2>Paso 1: Verificar Requisitos del Sistema</h2>
            
            <div class="requirements">
                <?php
                $requirements = [
                    'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
                    'Extensión PDO' => extension_loaded('pdo'),
                    'Extensión PDO MySQL' => extension_loaded('pdo_mysql'),
                    'Extensión OpenSSL' => extension_loaded('openssl'),
                    'Directorio config/ escribible' => is_writable('config/'),
                    'Directorio logs/ escribible' => is_writable('logs/') || is_writable('.'),
                    'Archivo database/schema.sql existe' => file_exists('database/schema.sql')
                ];
                
                $allOk = true;
                foreach ($requirements as $requirement => $ok) {
                    if (!$ok) $allOk = false;
                    ?>
                    <div class="requirement">
                        <span><?php echo $requirement; ?></span>
                        <span class="status <?php echo $ok ? 'ok' : 'error'; ?>">
                            <?php echo $ok ? 'OK' : 'ERROR'; ?>
                        </span>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <?php if ($allOk): ?>
                <form method="POST">
                    <h2>Paso 2: Configuración de Base de Datos</h2>
                    
                    <div class="form-group">
                        <label for="db_host">Host de Base de Datos:</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Nombre de Base de Datos:</label>
                        <input type="text" id="db_name" name="db_name" value="cuaderno_digital_eest2" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Usuario de Base de Datos:</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Contraseña de Base de Datos:</label>
                        <input type="password" id="db_pass" name="db_pass">
                    </div>
                    
                    <div class="form-group">
                        <label for="app_url">URL de la Aplicación:</label>
                        <input type="url" id="app_url" name="app_url" value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn">Continuar</button>
                </form>
            <?php else: ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> No se cumplen todos los requisitos del sistema. 
                    Por favor, corrige los errores antes de continuar.
                </div>
            <?php endif; ?>
            
        <!-- Paso 2: Configurar aplicación -->
        <?php elseif ($step == 2): ?>
            <h2>Paso 3: Configuración de la Aplicación</h2>
            
            <form method="POST">
                <input type="hidden" name="db_host" value="<?php echo htmlspecialchars($_POST['db_host']); ?>">
                <input type="hidden" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name']); ?>">
                <input type="hidden" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user']); ?>">
                <input type="hidden" name="db_pass" value="<?php echo htmlspecialchars($_POST['db_pass']); ?>">
                <input type="hidden" name="app_url" value="<?php echo htmlspecialchars($_POST['app_url']); ?>">
                
                <div class="form-group">
                    <label for="admin_user">Usuario Administrador:</label>
                    <input type="text" id="admin_user" name="admin_user" value="admin" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass">Contraseña Administrador:</label>
                    <input type="password" id="admin_pass" name="admin_pass" required minlength="8">
                    <small>Mínimo 8 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_name">Nombre del Administrador:</label>
                    <input type="text" id="admin_name" name="admin_name" value="Administrador" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Email del Administrador:</label>
                    <input type="email" id="admin_email" name="admin_email" value="admin@eest2.edu.ar" required>
                </div>
                
                <button type="submit" class="btn">Crear Usuario Administrador</button>
            </form>
            
        <!-- Paso 3: Finalizar instalación -->
        <?php elseif ($step == 3): ?>
            <h2>Paso 4: Finalizar Instalación</h2>
            
            <form method="POST">
                <input type="hidden" name="db_host" value="<?php echo htmlspecialchars($_POST['db_host']); ?>">
                <input type="hidden" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name']); ?>">
                <input type="hidden" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user']); ?>">
                <input type="hidden" name="db_pass" value="<?php echo htmlspecialchars($_POST['db_pass']); ?>">
                <input type="hidden" name="app_url" value="<?php echo htmlspecialchars($_POST['app_url']); ?>">
                <input type="hidden" name="admin_user" value="<?php echo htmlspecialchars($_POST['admin_user']); ?>">
                <input type="hidden" name="admin_pass" value="<?php echo htmlspecialchars($_POST['admin_pass']); ?>">
                <input type="hidden" name="admin_name" value="<?php echo htmlspecialchars($_POST['admin_name']); ?>">
                <input type="hidden" name="admin_email" value="<?php echo htmlspecialchars($_POST['admin_email']); ?>">
                
                <div class="alert alert-success">
                    <strong>¡Instalación Completada!</strong><br>
                    El sistema ha sido configurado correctamente.
                </div>
                
                <div class="requirements">
                    <h3>Información de Acceso:</h3>
                    <div class="requirement">
                        <span>URL del Sistema:</span>
                        <span><?php echo htmlspecialchars($_POST['app_url']); ?></span>
                    </div>
                    <div class="requirement">
                        <span>Usuario:</span>
                        <span><?php echo htmlspecialchars($_POST['admin_user']); ?></span>
                    </div>
                    <div class="requirement">
                        <span>Contraseña:</span>
                        <span>La que configuraste anteriormente</span>
                    </div>
                </div>
                
                <div class="alert alert-error">
                    <strong>Importante:</strong>
                    <ul>
                        <li>Elimina este archivo (install.php) por seguridad</li>
                        <li>Cambia la contraseña del administrador después del primer login</li>
                        <li>Configura un backup automático de la base de datos</li>
                        <li>Revisa la configuración de seguridad en config/.env</li>
                    </ul>
                </div>
                
                <a href="login.php" class="btn">Ir al Login</a>
            </form>
            
        <?php endif; ?>
    </div>
    
    <script>
        // Validar contraseña
        document.getElementById('admin_pass')?.addEventListener('input', function() {
            const password = this.value;
            const minLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /\d/.test(password);
            
            if (!minLength || !hasUpper || !hasLower || !hasNumber) {
                this.setCustomValidity('La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números.');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 