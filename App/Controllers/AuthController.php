<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class AuthController extends Controller {
    
    public function login() {
        if (Auth::check()) {
            if (Auth::role() === ROLE_BENEFICIARY) {
                $this->redirect('/beneficiary/dashboard');
            } else {
                $this->redirect('/dashboard');
            }
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF security token.';
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($username) || empty($password)) {
                    $error = 'Please fill in all fields.';
                } else {
                    if (Auth::attempt($username, $password)) {
                        if (Auth::role() === ROLE_BENEFICIARY) {
                            $this->redirect('/beneficiary/dashboard');
                        } else {
                            $this->redirect('/dashboard');
                        }
                    } else {
                        $error = 'Invalid email/phone or password.';
                        // Log failed login attempt
                        ActivityLog::log("Failed login attempt for user input: " . htmlspecialchars($username));
                    }
                }
            }
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('auth/login', [
            'title' => 'Login to NVOTS',
            'error' => $error,
            'csrfToken' => $csrfToken
        ]);
    }

    public function logout() {
        Auth::logout();
        $this->redirect('/auth/login');
    }

    public function forgot() {
        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF security token.';
            } else {
                $email = trim($_POST['email'] ?? '');
                if (empty($email)) {
                    $error = 'Email is required.';
                } else {
                    // Simulating sending password reset link
                    $message = 'If the account exists, a password reset link has been dispatched to your email.';
                    ActivityLog::log("Password reset requested for: " . htmlspecialchars($email));
                }
            }
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('auth/forgot', [
            'title' => 'Forgot Password',
            'message' => $message,
            'error' => $error,
            'csrfToken' => $csrfToken
        ]);
    }

    public function markNotificationRead($id = null) {
        Auth::requireAuth();
        if (!$id || !is_numeric($id)) {
            $this->json(['status' => false, 'message' => 'Invalid ID'], 400);
        }
        $db = Database::getInstance();
        $notif = $db->fetch("SELECT * FROM notifications WHERE id = :id", ['id' => (int)$id]);
        if ($notif && (int)$notif['recipient_id'] === (int)$_SESSION['user_id']) {
            $notifModel = new \App\Models\Notification();
            $notifModel->markAsRead($notif['id']);
            $this->json(['status' => true]);
        }
        $this->json(['status' => false, 'message' => 'Unauthorized or not found'], 403);
    }
}
