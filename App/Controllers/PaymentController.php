<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class PaymentController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('payments');
        }
    }

    public function manage() {
        $paymentModel = new Payment();
        $db = Database::getInstance();
        
        $error = null;
        $success = null;

        // Process payment validation / confirmation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
            if (Auth::role() !== ROLE_SUPER_ADMIN) {
                Auth::requireFeature('payments');
            }
            
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $paymentId = (int)$_POST['payment_id'];
                // Since payments are locked on creation, we can log details or verify
                AuditLog::log($_SESSION['user_id'], "Audited payment ID: {$paymentId}");
                $success = 'Payment verification confirmed.';
            }
        }

        $payments = $paymentModel->filterPayments();
        $stats = $paymentModel->getRevenueStats();

        $this->render('payments/manage', [
            'title' => 'Revenue & Payment Registry',
            'activePage' => 'payments',
            'payments' => $payments,
            'stats' => $stats,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }
}
