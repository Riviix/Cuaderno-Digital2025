<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/Security.php';

// Solo permitir peticiones AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Acceso denegado');
}

// Verificar token CSRF
$input = json_decode(file_get_contents('php://input'), true);
if (!Security::validateCSRFToken($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['valid' => false, 'error' => 'Token CSRF inválido']);
    exit;
}

// Verificar si el usuario está autenticado
$response = ['valid' => false];

if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
    $response = [
        'valid' => true,
        'user' => [
            'id' => $currentUser['id'],
            'username' => $currentUser['username'],
            'nombre' => $currentUser['nombre'],
            'apellido' => $currentUser['apellido'],
            'rol' => $currentUser['rol']
        ],
        'session_time' => $_SESSION['login_time'] ?? 0
    ];
}

// Enviar respuesta JSON
header('Content-Type: application/json');
echo json_encode($response); 