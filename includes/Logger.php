<?php
/**
 * Clase Logger - Manejo de logs de la aplicación
 */
class Logger {
    
    private static $logLevels = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7
    ];
    
    /**
     * Escribir log
     */
    public static function log($level, $message, $context = []) {
        $configLevel = config('LOG_LEVEL', 'info');
        
        if (self::$logLevels[$level] > self::$logLevels[$configLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr\n";
        
        $logFile = LOGS_PATH . '/app.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log de emergencia
     */
    public static function emergency($message, $context = []) {
        self::log('emergency', $message, $context);
    }
    
    /**
     * Log de alerta
     */
    public static function alert($message, $context = []) {
        self::log('alert', $message, $context);
    }
    
    /**
     * Log crítico
     */
    public static function critical($message, $context = []) {
        self::log('critical', $message, $context);
    }
    
    /**
     * Log de error
     */
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }
    
    /**
     * Log de advertencia
     */
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }
    
    /**
     * Log de noticia
     */
    public static function notice($message, $context = []) {
        self::log('notice', $message, $context);
    }
    
    /**
     * Log de información
     */
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }
    
    /**
     * Log de debug
     */
    public static function debug($message, $context = []) {
        self::log('debug', $message, $context);
    }
    
    /**
     * Log de actividad de usuario
     */
    public static function userActivity($userId, $action, $details = []) {
        $context = array_merge([
            'user_id' => $userId,
            'action' => $action,
            'ip' => Security::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ], $details);
        
        self::info("User activity: $action", $context);
    }
    
    /**
     * Log de error de base de datos
     */
    public static function databaseError($error, $query = '', $params = []) {
        $context = [
            'query' => $query,
            'params' => $params,
            'error' => $error
        ];
        
        self::error('Database error occurred', $context);
    }
    
    /**
     * Log de intento de acceso no autorizado
     */
    public static function unauthorizedAccess($resource, $details = []) {
        $context = array_merge([
            'resource' => $resource,
            'ip' => Security::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ], $details);
        
        self::warning('Unauthorized access attempt', $context);
    }
    
    /**
     * Limpiar logs antiguos
     */
    public static function cleanOldLogs($days = 30) {
        $logFiles = [
            LOGS_PATH . '/app.log',
            LOGS_PATH . '/failed_logins.log',
            LOGS_PATH . '/successful_logins.log',
            LOGS_PATH . '/logouts.log'
        ];
        
        foreach ($logFiles as $logFile) {
            if (file_exists($logFile)) {
                $lines = file($logFile, FILE_IGNORE_NEW_LINES);
                $cutoffTime = time() - ($days * 24 * 60 * 60);
                $newLines = [];
                
                foreach ($lines as $line) {
                    if (preg_match('/\[(.+?)\]/', $line, $matches)) {
                        $logTime = strtotime($matches[1]);
                        if ($logTime > $cutoffTime) {
                            $newLines[] = $line;
                        }
                    }
                }
                
                file_put_contents($logFile, implode("\n", $newLines) . "\n");
            }
        }
    }
} 