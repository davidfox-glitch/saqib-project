<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    // Permanent admin credentials
    private static $permanentAdminEmail = 'dawoodhashmi@gmail.com';
    private static $permanentAdminPassword = 'admin@123';

    // Attempt login
    public static function login($email, $password) {
        // Check permanent admin first
        if ($email === self::$permanentAdminEmail && $password === self::$permanentAdminPassword) {
            $_SESSION['user_id'] = 0;
            $_SESSION['user_name'] = 'Admin';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            return true;
        }
        // Fallback hard‑coded admin (kept for backward compatibility)
        if ($email === 'dawoodhashmi2006@gmail.com' && $password === 'admin@123') {
            $_SESSION['user_id'] = 0;
            $_SESSION['user_name'] = 'Admin';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            return true;
        }
        // Look up regular users in the JSON database
        $user = JsonDB::findUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            // If the email happens to be the permanent admin, upgrade role
            $role = ($email === self::$permanentAdminEmail) ? 'admin' : $user['role'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $role;
            return true;
        }
        return false;
    }

    // Register a new user
    public static function register($username, $email, $password) {
        $existing = JsonDB::findUserByEmail($email);
        if ($existing) {
            return "Email is already registered.";
        }
        $user = JsonDB::createUser($username, $email, $password, 'user');
        if ($user) {
            // Auto‑login after successful registration
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return "Registration failed. Please try again.";
    }

    // Logout current user
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

    // Check login status
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Get current user data without side effects
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    // Verify admin role
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    // Force login before accessing a protected page
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            // Preserve relative admin path handling
            if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
                header("Location: ../login.php");
            } else {
                header("Location: login.php");
            }
            exit;
        }
    }

    // Force admin role before accessing an admin page
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header("Location: ../index.php");
            exit;
        }
    }
}
?>
