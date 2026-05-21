<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    // Attempt login
    public static function login($email, $password) {
    // Permanent admin credentials
    if ($email === 'dawoodhashmi@gmail.com' && $password === 'admin@123') {
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Admin';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'admin';
        return true;
    }
    // Existing hardcoded admin credentials (fallback)
    if ($email === 'dawoodhashmi2006@gmail.com' && $password === 'admin@123') {
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Admin';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'admin';
        return true;
    }
    // Check database users
    $user = JsonDB::findUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        // Permanent admin credentials
        if ($email === 'dawoodhashmi@gmail.com' && $password === 'admin@123') {
            $_SESSION['user_id'] = 0;
            $_SESSION['user_name'] = 'Admin';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            return true;
        }
        // Existing hardcoded admin credentials (fallback)
        if ($email === 'dawoodhashmi2006@gmail.com' && $password === 'admin@123') {
            $_SESSION['user_id'] = 0;
            $_SESSION['user_name'] = 'Admin';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            return true;
        }
        // Check database users
        $user = JsonDB::findUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    }

    // Register user
    public static function register($username, $email, $password) {
        $existing = JsonDB::findUserByEmail($email);
        if ($existing) {
            return "Email is already registered.";
        }
        $user = JsonDB::createUser($username, $email, $password, 'user');
        if ($user) {
            // Auto login after registration
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return "Registration failed. Please try again.";
    }

    // Logout
    public static function logout() {
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

    // Check if logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Get current user info
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) return null;
        // Return user data without any side effects or output
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    // Check if current user is admin
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            // If we are currently in an admin/ file, redirect to ../login.php instead of login.php
            if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
                header("Location: ../login.php");
            } else {
                header("Location: login.php");
            }
            exit;
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header("Location: ../index.php");
            exit;
        }
    }
}

