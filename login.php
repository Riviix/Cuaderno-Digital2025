<?php
/**
 * Página de login principal
 * Redirige al sistema apropiado según la configuración
 */

// Verificar si la base de datos está configurada
$dbConfigured = false;
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    $dbConfigured = true;
} catch (Exception $e) {
    $dbConfigured = false;
}

// Si la base de datos no está configurada, mostrar página de configuración
if (!$dbConfigured) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Cuaderno Digital EEST N°2</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f4f4f4;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 50px auto;
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                text-align: center;
            }
            .warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                color: #856404;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .btn {
                display: inline-block;
                background: #007bff;
                color: white;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 5px;
                margin: 10px;
                font-weight: bold;
            }
            .btn:hover {
                background: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Cuaderno Digital EEST N°2</h1>
            <div class="warning">
                <strong>⚠️ Base de datos no configurada</strong><br>
                El sistema de login requiere que la base de datos esté configurada.
            </div>
            <p>Por favor, configura la base de datos primero:</p>
            <a href="setup_dev.php" class="btn">Configurar Sistema</a>
            <a href="index.php" class="btn">Volver al Inicio</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Si la base de datos está configurada, continuar con el sistema principal
require_once 'includes/auth.php';

$error = '';
$success = '';

// Generar token CSRF
$csrfToken = Security::generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Error de seguridad. Intente nuevamente.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            header('Location: index.php');
            exit();
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cuaderno Digital E.E.S.T N°2</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="img/logo-escuela.png" alt="Logo EEST N°2" class="logo">
                <h1>Cuaderno Digital</h1>
                <h2>E.E.S.T. N°2 "Educación y Trabajo"</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo Security::escape($csrfToken); ?>">
                
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo Security::escape($_POST['username'] ?? ''); ?>"
                           autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required 
                           autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>
            
            <div class="login-footer">
                <p>Sistema de Gestión Escolar</p>
            </div>
        </div>
    </div>
</body>
</html> 