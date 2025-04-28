<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

function login($email, $password, $remember = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            if ($remember) {
                // Generate remember token
                $token = bin2hex(random_bytes(32));
                $expiry = time() + REMEMBER_ME_DURATION;
                
                // Store token in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
                $stmt->execute([$token, $expiry, $user['id']]);
                
                // Set cookie
                setcookie('remember_token', $token, $expiry, '/', '', true, true);
            }
            
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function logout() {
    global $pdo;
    
    if (isset($_SESSION['user_id'])) {
        // Clear remember token
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, token_expiry = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    
    // Clear session
    session_unset();
    session_destroy();
    
    // Clear remember cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

function checkAuth() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Check remember token
    if (isset($_COOKIE['remember_token'])) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE remember_token = ? AND token_expiry > ?");
            $stmt->execute([$_COOKIE['remember_token'], time()]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                return true;
            }
        } catch(PDOException $e) {
            error_log("Remember token check error: " . $e->getMessage());
        }
    }
    
    return false;
}

function requireAuth() {
    if (!checkAuth()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireRole($role) {
    requireAuth();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: ' . SITE_URL . '/unauthorized.php');
        exit();
    }
}

function verifyLogin($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
?> 