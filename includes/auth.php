<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM usuarios WHERE username = ? AND activo = 1";
        $user = $this->db->fetch($sql, [$username]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['logged_in'] = true;
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
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
}

$auth = new Auth();
?> 