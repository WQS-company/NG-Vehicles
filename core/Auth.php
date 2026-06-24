<?php
namespace Core;

class Auth {
    // Check if a user is logged in
    public static function check() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // Get current logged-in user
    public static function user() {
        if (!self::check()) {
            return null;
        }
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $_SESSION['user_id']]);
    }

    // Get current user role
    public static function role() {
        return $_SESSION['user_role'] ?? null;
    }

    // Force authentication
    public static function requireAuth() {
        if (!self::check()) {
            header("Location: " . BASE_URL . '/auth/login');
            exit;
        }
    }

    // Check if a user has access to a specific feature
    public static function hasFeature($feature) {
        if (!self::check()) {
            return false;
        }
        $user = self::user();
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'SUPER_ADMIN') {
            return true; // Super admin always has access to all features
        }
        if (empty($user['features'])) {
            return false;
        }
        $features = explode(',', $user['features']);
        return in_array($feature, $features);
    }

    // Force specific feature access
    public static function requireFeature($feature) {
        self::requireAuth();
        if (!self::hasFeature($feature)) {
            // Log access violation
            $db = Database::getInstance();
            $db->insert('activity_logs', [
                'description' => 'Feature Access Denied violation on page: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown') . ' (Feature: ' . $feature . ')',
                'performed_by' => $_SESSION['user_id'] ?? null
            ]);
            
            http_response_code(403);
            die("Unauthorized Access: You do not have permissions to use this feature (" . htmlspecialchars($feature) . ").");
        }
    }

    // Force specific role permissions
    public static function requireRole($allowedRoles) {
        self::requireAuth();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        $userRole = self::role();
        if (!in_array($userRole, $allowedRoles)) {
            // Log access violation
            $db = Database::getInstance();
            $db->insert('activity_logs', [
                'description' => 'Access Denied violation on page: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'),
                'performed_by' => $_SESSION['user_id'] ?? null
            ]);
            
            http_response_code(403);
            die("Unauthorized Access: You do not have permissions to view this resource.");
        }
    }

    // Handle login attempt
    public static function attempt($usernameOrEmailOrPhone, $password) {
        $db = Database::getInstance();
        
                // Find user by email or phone
        $sql = "SELECT * FROM users WHERE (email = :email OR phone = :phone) LIMIT 1";
        $user = $db->fetch($sql, [
            'email' => $usernameOrEmailOrPhone,
            'phone' => $usernameOrEmailOrPhone
        ]);

        if ($user && $user['is_active']) {
            if (password_verify($password, $user['password_hash'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Update last login
                $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
                
                // Log Audit
                $db->insert('audit_logs', [
                    'user_id' => $user['id'],
                    'action' => 'User logged in successfully',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]);

                return true;
            }
        }
        
        return false;
    }

    // Logout user
    public static function logout() {
        if (self::check()) {
            $db = Database::getInstance();
            $db->insert('audit_logs', [
                'user_id' => $_SESSION['user_id'],
                'action' => 'User logged out',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        }
        
        // Destroy sessions
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
}
