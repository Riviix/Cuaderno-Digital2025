<?php
require_once 'includes/auth.php';
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Cuaderno Digital E.E.S.T N°2'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-top">
            <div class="logo-section">
                <img src="img/logo-escuela.png" alt="Logo EEST N°2" class="logo" style="width:70px;height:70px;border-radius:50%;background:#fff;object-fit:contain;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-right:16px;">
                <div class="school-info">
                    <h1>Cuaderno Digital</h1>
                    <h2>E.E.S.T. N°2 "Educación y Trabajo"</h2>
                </div>
            </div>
            <button class="hamburger" id="hamburger-menu" aria-label="Abrir menú" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="user-section">
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></span>
                    <span class="role">(<?php echo ucfirst($currentUser['rol']); ?>)</span>
                </div>
                <a href="logout.php" class="logout-btn" title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        
        <nav class="main-nav" id="main-nav">
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="cursos.php" class="nav-link"><i class="fas fa-graduation-cap"></i> Cursos</a></li>
                <li><a href="estudiantes.php" class="nav-link"><i class="fas fa-users"></i> Estudiantes</a></li>
                <li><a href="horarios.php" class="nav-link"><i class="fas fa-clock"></i> Horarios</a></li>
               <!--<li><a href="inasistencias.php" class="nav-link"><i class="fas fa-calendar-times"></i> Inasistencias</a></li> -->
                <li><a href="llamados.php" class="nav-link"><i class="fas fa-exclamation-triangle"></i> Llamados</a></li>
                <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
                <li><a href="equipo.php" class="nav-link"><i class="fas fa-user-tie"></i> Equipo Directivo</a></li>
                <li><a href="reportes.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var hamburger = document.getElementById('hamburger-menu');
        var nav = document.getElementById('main-nav');
        if (hamburger && nav) {
            hamburger.addEventListener('click', function(e) {
                e.stopPropagation();
                nav.classList.toggle('open');
                hamburger.setAttribute('aria-expanded', nav.classList.contains('open') ? 'true' : 'false');
            });
            // Cerrar menú al hacer click fuera o en un enlace
            document.addEventListener('click', function(e) {
                if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                    nav.classList.remove('open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
                if (e.target.classList.contains('nav-link')) {
                    nav.classList.remove('open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
    </script>
</body>
</html>

