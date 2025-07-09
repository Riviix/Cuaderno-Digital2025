<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Logger.php';

// Verificar autenticación
$auth->requireLogin();

// Registrar actividad del usuario
$currentUser = $auth->getCurrentUser();
if ($currentUser) {
    Logger::userActivity($currentUser['id'], 'page_access', [
        'page' => basename($_SERVER['PHP_SELF']),
        'url' => $_SERVER['REQUEST_URI']
    ]);
}

// Generar token CSRF para formularios
$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistema de Gestión Escolar - Cuaderno Digital EEST N°2">
    <meta name="author" content="EEST N°2">
    
    <!-- Headers de seguridad -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <title><?php echo isset($pageTitle) ? Security::escape($pageTitle) : 'Cuaderno Digital EEST N°2'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
          integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="img/logo-escuela.png" as="image">
    
    <style>
        /* Estilos críticos inline para mejorar rendimiento */
        .loading {
            display: none;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-top">
            <div class="logo-section">
                <img src="img/logo-escuela.png" alt="Logo EEST N°2" class="logo">
                <div class="school-info">
                    <h1>Cuaderno Digital</h1>
                    <h2>E.E.S.T. N°2 "Educación y Trabajo"</h2>
                </div>
            </div>
            
            <div class="user-section">
                <div class="user-info">
                    <span class="user-name">
                        <i class="fas fa-user"></i>
                        <?php echo Security::escape($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?>
                    </span>
                    <span class="role">
                        <i class="fas fa-user-tag"></i>
                        <?php echo Security::escape(ucfirst($currentUser['rol'])); ?>
                    </span>
                </div>
                
                <div class="user-actions">
                    <a href="profile.php" class="btn btn-sm btn-secondary" title="Perfil">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-danger logout-btn" title="Cerrar Sesión" 
                       onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <nav class="main-nav">
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Inicio
                </a></li>
                <li><a href="estudiantes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'estudiantes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Estudiantes
                </a></li>
                <li><a href="cursos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'cursos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i> Cursos
                </a></li>
                <li><a href="inasistencias.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'inasistencias.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-times"></i> Inasistencias
                </a></li>
                <li><a href="llamados.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'llamados.php' ? 'active' : ''; ?>">
                    <i class="fas fa-exclamation-triangle"></i> Llamados
                </a></li>
                <li><a href="reportes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reportes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reportes
                </a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo Security::escape($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo Security::escape($success); ?>
        </div>
    <?php endif; ?>
</body>
</html>

