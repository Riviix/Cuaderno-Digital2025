<?php
/**
 * Configuración principal de la aplicación
 * Carga variables de entorno y configura la aplicación
 */

// Cargar variables de entorno
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remover comillas
        if (preg_match('/^"(.+)"$/', $value, $matches)) {
            $value = $matches[1];
        }
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
    return true;
}

// Cargar archivo .env si existe
$envFile = __DIR__ . '/.env';
if (!loadEnv($envFile)) {
    // Si no existe .env, usar valores por defecto
    $_ENV = [
        'DB_HOST' => 'localhost',
        'DB_NAME' => 'cuaderno_digital_eest2',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'APP_NAME' => 'Cuaderno Digital EEST N°2',
        'APP_URL' => 'http://localhost',
        'APP_ENV' => 'development',
        'APP_DEBUG' => 'true',
        'SESSION_SECRET' => 'default-secret-change-in-production',
        'PASSWORD_SALT' => 'default-salt-change-in-production'
    ];
}

// Configuración de la aplicación
define('APP_NAME', $_ENV['APP_NAME']);
define('APP_URL', $_ENV['APP_URL']);
define('APP_ENV', $_ENV['APP_ENV']);
define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');

// Configuración de la base de datos
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

// Configuración de seguridad
define('SESSION_SECRET', $_ENV['SESSION_SECRET']);
define('PASSWORD_SALT', $_ENV['PASSWORD_SALT']);

// Configuración de rutas
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Crear directorios necesarios si no existen
$directories = [LOGS_PATH, UPLOADS_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        try {
            mkdir($dir, 0755, true);
        } catch (Exception $e) {
            // Silenciar errores de creación de directorios
            error_log("No se pudo crear el directorio: $dir - " . $e->getMessage());
        }
    }
}

// Configuración de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Configuración de sesión
try {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
} catch (Exception $e) {
    // Silenciar errores de configuración de sesión
    error_log("Error en configuración de sesión: " . $e->getMessage());
}

// Función para obtener configuración
function config($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Función para validar si estamos en producción
function isProduction() {
    return APP_ENV === 'production';
}

// Función para validar si estamos en desarrollo
function isDevelopment() {
    return APP_ENV === 'development';
} 