<?php
require_once(__DIR__ . '/../includes/Usuario.php');
require_once(__DIR__ . '/../config/database.php');
 
session_start();

function checkAuthentication() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        // Usuário não está logado, redirecionar para login
        $current_path = $_SERVER['REQUEST_URI'];
        if (strpos($current_path, '/auth/') === false) {
            header('Location: auth/login.php');
        } else {
            header('Location: login.php');
        }
        exit();
    }
    
    // Verificar se usuário ainda está ativo no banco
    if (isset($_SESSION['user_id'])) {
        require_once(__DIR__ . '/../config/database.php');
        require_once(__DIR__ . '/../includes/Usuario.php');
        
        $database = new Database();
        $db = $database->getConnection();
        $usuario = new Usuario($db);
        $usuario->id = $_SESSION['user_id'];
        
        if (!$usuario->readOne() || !$usuario->ativo) {
            // Usuário foi desativado, fazer logout
            session_destroy();
            header('Location: auth/login.php');
            exit();
        }
    }
}

function checkAdminAccess() {
    checkAuthentication();
    
    if (!isset($_SESSION['user_perfil']) || $_SESSION['user_perfil'] !== 'admin') {
        // Usuário não é admin, redirecionar para dashboard
        header('Location: ../index.php');
        exit();
    }
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Usuário';
}



function getUserPerfil() {
    return $_SESSION['user_perfil'] ?? 'usuario';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function isAdmin() {
    return getUserPerfil() === 'admin';
}

function getLoginTime() {
    return $_SESSION['login_time'] ?? time();
}
?>