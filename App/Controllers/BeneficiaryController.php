<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\User;
use App\Models\Beneficiary;

class BeneficiaryController extends Controller {

    // Public login for beneficiaries
    public function login() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (empty($email) || empty($password)) {
                $error = 'Email and password are required.';
            } else {
                if (\Core\Auth::attempt($email, $password)) {
                    // ensure role
                    if (\Core\Auth::role() !== 'BENEFICIARY') {
                        \Core\Auth::logout();
                        $error = 'Access denied for this account.';
                    } else {
                        $this->redirect('beneficiary/dashboard');
                    }
                } else {
                    $error = 'Invalid credentials or account inactive.';
                }
            }
        }

        $this->render('beneficiary/login', [
            'title' => 'Beneficiary Login',
            'error' => $error,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    // Dashboard for beneficiary
    public function dashboard() {
        Auth::requireRole(['BENEFICIARY', 'SUPER_ADMIN']);
        $user = Auth::user();
        $db = Database::getInstance();
        $beneficiaryModel = new Beneficiary();
        $profile = $beneficiaryModel->findByUserId($user['id']);
        $earnings = 0;
        $recent = [];
        
        // Find matching commission recipient
        $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
        if (!$recipient && $user['email']) {
            $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE email = :email LIMIT 1", ['email' => $user['email']]);
            if ($recipient) {
                $db->update('commission_recipients', ['user_id' => $user['id']], 'id = :id', ['id' => $recipient['id']]);
                $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
            }
        }

        if ($profile) {
            $earnings = $beneficiaryModel->getEarnings($profile['id']);
            $recent = $db->fetchAll('SELECT * FROM beneficiary_earnings WHERE beneficiary_id = :bid ORDER BY id DESC LIMIT 5', ['bid' => $profile['id']]);
        }

        // Also fetch payouts from commission_payout_items to show in the recent list
        $payouts = [];
        if ($recipient) {
            $payouts = $db->fetchAll("
                SELECT pi.*, p.title as payout_title, p.created_at as payout_date 
                FROM commission_payout_items pi 
                JOIN commission_payouts p ON p.id = pi.payout_id 
                WHERE pi.recipient_id = :rid 
                ORDER BY pi.id DESC LIMIT 5
            ", ['rid' => $recipient['id']]);
        }

        $this->render('beneficiary/dashboard', [
            'title' => 'Beneficiary Dashboard',
            'user' => $user,
            'profile' => $profile,
            'recipient' => $recipient,
            'earnings' => $earnings,
            'recent' => $recent,
            'payouts' => $payouts,
            'activePage' => 'beneficiary_dashboard'
        ]);
    }

    // Payroll view for beneficiary
    public function payroll() {
        Auth::requireRole(['BENEFICIARY', 'SUPER_ADMIN']);
        $user = Auth::user();
        $db = Database::getInstance();
        $beneficiaryModel = new Beneficiary();
        
        $profile = $beneficiaryModel->findByUserId($user['id']);
        
        // Find matching commission recipient
        $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
        if (!$recipient && $user['email']) {
            $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE email = :email LIMIT 1", ['email' => $user['email']]);
            if ($recipient) {
                $db->update('commission_recipients', ['user_id' => $user['id']], 'id = :id', ['id' => $recipient['id']]);
                $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
            }
        }

        $payouts = [];
        if ($recipient) {
            $payouts = $db->fetchAll("
                SELECT pi.*, p.title as payout_title, p.created_at as payout_date 
                FROM commission_payout_items pi 
                JOIN commission_payouts p ON p.id = pi.payout_id 
                WHERE pi.recipient_id = :rid 
                ORDER BY pi.id DESC
            ", ['rid' => $recipient['id']]);
        }

        // Also fetch general earnings from beneficiary_earnings if any exist
        $earningsList = [];
        if ($profile) {
            $earningsList = $db->fetchAll('SELECT * FROM beneficiary_earnings WHERE beneficiary_id = :bid ORDER BY id DESC', ['bid' => $profile['id']]);
        }

        $this->render('beneficiary/payroll', [
            'title' => 'My Commission Payroll',
            'user' => $user,
            'profile' => $profile,
            'recipient' => $recipient,
            'payouts' => $payouts,
            'earningsList' => $earningsList,
            'activePage' => 'beneficiary_payroll'
        ]);
    }

    // Profile update with avatar upload and direct bank detail changes
    public function profile() {
        Auth::requireRole(['BENEFICIARY', 'SUPER_ADMIN']);
        $user = Auth::user();
        $db = Database::getInstance();
        $beneficiaryModel = new Beneficiary();
        $profile = $beneficiaryModel->findByUserId($user['id']);
        
        // Find matching commission recipient
        $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
        if (!$recipient && $user['email']) {
            $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE email = :email LIMIT 1", ['email' => $user['email']]);
            if ($recipient) {
                $db->update('commission_recipients', ['user_id' => $user['id']], 'id = :id', ['id' => $recipient['id']]);
                $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
            }
        }

        $error = null; $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                // Allow profile image upload
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['avatar']['tmp_name'];
                    $fileName = $_FILES['avatar']['name'];
                    $fileSize = $_FILES['avatar']['size'];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png'];
                    if (in_array($ext, $allowed) && $fileSize < 3000000) {
                        $newName = md5(time() . $fileName) . '.' . $ext;
                        $dest = BASE_PATH . '/public/uploads/beneficiaries/';
                        if (!is_dir($dest)) mkdir($dest, 0777, true);
                        if (move_uploaded_file($fileTmpPath, $dest . $newName)) {
                            $db->update('users', ['avatar' => 'public/uploads/beneficiaries/' . $newName], 'id = :id', ['id' => $user['id']]);
                            $success = 'Avatar updated successfully.';
                            $user = Auth::user(); // refresh session user reference if cached
                        }
                    } else {
                        $error = 'Invalid avatar file or too large (max 3MB).';
                    }
                }

                // Direct bank detail updates
                $bankName = trim($_POST['bank_name'] ?? '');
                $bankCode = trim($_POST['bank_code'] ?? '');
                $accountNumber = trim($_POST['account_number'] ?? '');
                $accountName = trim($_POST['account_name'] ?? '');

                if ($bankName === 'other') {
                    $bankName = trim($_POST['custom_bank_name'] ?? '');
                    $bankCode = 'custom';
                }

                if (!empty($bankName) && !empty($accountNumber)) {
                    // Update beneficiaries table
                    $db->update('beneficiaries', [
                        'bank_name' => $bankName,
                        'account_name' => $accountName,
                        'account_number' => $accountNumber
                    ], 'user_id = :uid', ['uid' => $user['id']]);

                    // Check if recipient exists
                    $existRecipient = $db->fetch("SELECT id FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
                    if (!$existRecipient) {
                        $db->insert('commission_recipients', [
                            'user_id' => $user['id'],
                            'name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''),
                            'email' => $user['email'],
                            'bank_name' => $bankName,
                            'bank_code' => $bankCode,
                            'account_number' => $accountNumber,
                            'account_name' => $accountName,
                            'percentage_share' => $profile['commission_percentage'] ?? 0.00,
                            'total_paid' => 0.00
                        ]);
                    } else {
                        $db->update('commission_recipients', [
                            'bank_name' => $bankName,
                            'bank_code' => $bankCode,
                            'account_number' => $accountNumber,
                            'account_name' => $accountName
                        ], 'user_id = :uid', ['uid' => $user['id']]);
                    }
                    $success = ($success ? $success . ' ' : '') . 'Payout bank details updated successfully.';
                    
                    // Reload profile and recipient
                    $profile = $beneficiaryModel->findByUserId($user['id']);
                    $recipient = $db->fetch("SELECT * FROM commission_recipients WHERE user_id = :uid", ['uid' => $user['id']]);
                }

                // Request other account details update: store request to activity_logs for admin review
                if (!empty($_POST['request_change']) && trim($_POST['request_change']) !== '') {
                    $note = trim($_POST['request_change']);
                    $db->insert('activity_logs', [
                        'description' => 'Beneficiary account details update request from user_id:' . $user['id'] . ' — ' . $note,
                        'performed_by' => $user['id']
                    ]);
                    $success = ($success ? $success . ' ' : '') . 'Account details update request submitted to admin.';
                }
            }
        }

        $this->render('beneficiary/profile', [
            'title' => 'My Beneficiary Profile',
            'user' => $user,
            'profile' => $profile,
            'recipient' => $recipient,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }
}
