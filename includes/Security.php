<?php
/**
 * Clase Security - Manejo de seguridad y validaciones
 */
class Security {
    
    /**
     * Sanitizar entrada de datos
     */
    public static function sanitize($input, $type = 'string') {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validar entrada de datos
     */
    public static function validate($input, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && (empty($value) && $value !== '0')) {
                $errors[$field] = "El campo $field es requerido";
                continue;
            }
            
            if (!empty($value)) {
                if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "El campo $field debe ser un email válido";
                }
                
                if (strpos($rule, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $rule, $matches);
                    $min = (int)$matches[1];
                    if (strlen($value) < $min) {
                        $errors[$field] = "El campo $field debe tener al menos $min caracteres";
                    }
                }
                
                if (strpos($rule, 'max:') !== false) {
                    preg_match('/max:(\d+)/', $rule, $matches);
                    $max = (int)$matches[1];
                    if (strlen($value) > $max) {
                        $errors[$field] = "El campo $field debe tener máximo $max caracteres";
                    }
                }
                
                if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                    $errors[$field] = "El campo $field debe ser numérico";
                }
                
                if (strpos($rule, 'date') !== false && !strtotime($value)) {
                    $errors[$field] = "El campo $field debe ser una fecha válida";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar token CSRF
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generar hash seguro para contraseñas
     */
    public static function hashPassword($password) {
        return password_hash($password . PASSWORD_SALT, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password . PASSWORD_SALT, $hash);
    }
    
    /**
     * Generar token de recuperación de contraseña
     */
    public static function generateResetToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Limpiar datos de sesión sensibles
     */
    public static function clearSession() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Registrar intento de acceso fallido
     */
    public static function logFailedAttempt($username, $ip) {
        $logFile = LOGS_PATH . '/failed_logins.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] Failed login attempt for user: $username from IP: $ip\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Verificar si hay demasiados intentos fallidos
     */
    public static function isRateLimited($username, $ip, $maxAttempts = 5, $timeWindow = 900) {
        $logFile = LOGS_PATH . '/failed_logins.log';
        if (!file_exists($logFile)) {
            return false;
        }
        
        $logs = file($logFile, FILE_IGNORE_NEW_LINES);
        $recentAttempts = 0;
        $cutoffTime = time() - $timeWindow;
        
        foreach ($logs as $log) {
            if (preg_match('/\[(.+)\] Failed login attempt for user: (.+) from IP: (.+)/', $log, $matches)) {
                $logTime = strtotime($matches[1]);
                $logUser = $matches[2];
                $logIp = $matches[3];
                
                if ($logTime > $cutoffTime && ($logUser === $username || $logIp === $ip)) {
                    $recentAttempts++;
                }
            }
        }
        
        return $recentAttempts >= $maxAttempts;
    }
    
    /**
     * Validar y sanitizar archivos subidos
     */
    public static function validateUploadedFile($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir el archivo';
            return $errors;
        }
        
        if ($file['size'] > $maxSize) {
            $errors[] = 'El archivo es demasiado grande';
        }
        
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Tipo de archivo no permitido';
            }
        }
        
        return $errors;
    }
    
    /**
     * Generar nombre seguro para archivos
     */
    public static function generateSafeFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        return $safeName;
    }
    
    /**
     * Escapar salida HTML
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar IP
     */
    public static function isValidIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Obtener IP real del cliente (mejorada para proxies)
     */
    public static function getClientIP() {
        $ip = null;
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'REMOTE_ADDR'
        ];
        foreach ($headers as $key) {
            if (!empty($_SERVER[$key])) {
                // X-Forwarded-For puede contener varias IP separadas por coma
                $ipList = explode(',', $_SERVER[$key]);
                foreach ($ipList as $ipItem) {
                    $ipItem = trim($ipItem);
                    if (self::isValidIP($ipItem) && $ipItem !== '127.0.0.1' && $ipItem !== '::1') {
                        return $ipItem;
                    }
                }
            }
        }
        // Fallback
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
} 