<?php
namespace Core;

class Controller {
    // Render a view file with data
    protected function render($view, $data = []) {
        // Extract data elements into local variables
        extract($data);

        // Define main content path
        $viewFile = BASE_PATH . '/app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View '{$view}' not found at: {$viewFile}");
        }

        // Output buffer template
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Include layout wrapper
        include BASE_PATH . '/app/views/layout/main.php';
    }

    // Direct redirection utility
    protected function redirect($url) {
        header("Location: " . BASE_URL . '/' . ltrim($url, '/'));
        exit;
    }

    // JSON response wrapper
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // CSRF Protection Helpers
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
