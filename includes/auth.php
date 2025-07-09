<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Security.php';

session_start();

/**
 * Clase Auth - Manejo de autenticación y autorización
 * Implementa medidas de seguridad avanzadas
 */
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        // Desactivar rate limiting
        // $clientIP = Security::getClientIP();
        // if (Security::isRateLimited($username, $clientIP)) {
        //     return ['success' => false, 'error' => 'Demasiados intentos fallidos. Intente más tarde.'];
        // }
        $clientIP = Security::getClientIP();
        
        // Sanitizar entrada
        $username = Security::sanitize($username);
        
        // Validar entrada
        $validation = Security::validate(['username' => $username, 'password' => $password], [
            'username' => 'required|min:3|max:50',
            'password' => 'required|min:6'
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'error' => 'Datos de entrada inválidos'];
        }
        
        $sql = "SELECT * FROM usuarios WHERE username = ? AND activo = 1";
        $user = $this->db->fetch($sql, [$username]);

        if ($user && Security::verifyPassword($password, $user['password'])) {
            // Regenerar ID de sesión para prevenir session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $clientIP;
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Registrar login exitoso
            $this->logSuccessfulLogin($user['id'], $clientIP);
            
            return ['success' => true, 'user' => $user];
        } else {
            // Registrar intento fallido
            Security::logFailedAttempt($username, $clientIP);
            return ['success' => false, 'error' => 'Usuario o contraseña incorrectos'];
        }
    }

    public function logout() {
        // Registrar logout
        if ($this->isLoggedIn()) {
            $this->logLogout($_SESSION['user_id']);
        }
        
        Security::clearSession();
        header('Location: login.php');
        exit();
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Verificar si la sesión no ha expirado (8 horas)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 28800) {
            $this->logout();
            return false;
        }
        
        // Verificar si la IP no ha cambiado (opcional, puede ser muy restrictivo)
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== Security::getClientIP()) {
            // Solo en producción
            if (isProduction()) {
                $this->logout();
                return false;
            }
        }
        
        return true;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['rol'] === $role;
    }

    public function requireRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: unauthorized.php');
            exit();
        }
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'nombre' => $_SESSION['nombre'],
                'apellido' => $_SESSION['apellido'],
                'rol' => $_SESSION['rol']
            ];
        }
        return null;
    }
    
    /**
     * Registrar login exitoso
     */
    private function logSuccessfulLogin($userId, $ip) {
        $logFile = LOGS_PATH . '/successful_logins.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] Successful login for user ID: $userId from IP: $ip\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Registrar logout
     */
    private function logLogout($userId) {
        $logFile = LOGS_PATH . '/logouts.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] Logout for user ID: $userId\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Cambiar contraseña de usuario
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Validar nueva contraseña
        $validation = Security::validate(['password' => $newPassword], [
            'password' => 'required|min:8'
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'error' => 'La nueva contraseña debe tener al menos 8 caracteres'];
        }
        
        // Obtener usuario actual
        $user = $this->db->fetch("SELECT * FROM usuarios WHERE id = ?", [$userId]);
        if (!$user) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }
        
        // Verificar contraseña actual
        if (!Security::verifyPassword($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Contraseña actual incorrecta'];
        }
        
        // Generar nuevo hash
        $newHash = Security::hashPassword($newPassword);
        
        // Actualizar contraseña
        $sql = "UPDATE usuarios SET password = ?, fecha_modificacion = NOW() WHERE id = ?";
        $this->db->query($sql, [$newHash, $userId]);
        
        return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
    }
}

$auth = new Auth();
?> 