<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\DynamicField;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class AdminController extends Controller {

    public function __construct() {
        Auth::requireRole(ROLE_SUPER_ADMIN);
    }

    public function settings() {
        $settingModel = new Setting();
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                // 1. Process files if uploaded
                $logoPath = $settingModel->get('platform_logo', '');
                if (isset($_FILES['platform_logo']) && $_FILES['platform_logo']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['platform_logo']['tmp_name'];
                    $fileName = $_FILES['platform_logo']['name'];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowed = ['png', 'jpg', 'jpeg', 'svg'];
                    if (in_array($ext, $allowed)) {
                        $destDir = BASE_PATH . '/public/uploads/settings/';
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0777, true);
                        }
                        $logoName = 'logo_' . time() . '.' . $ext;
                        if (move_uploaded_file($fileTmp, $destDir . $logoName)) {
                            $logoPath = 'public/uploads/settings/' . $logoName;
                            $settingModel->set('platform_logo', $logoPath);
                        }
                    } else {
                        $error = 'Invalid logo image type. PNG, JPG, JPEG, SVG only.';
                    }
                }

                $faviconPath = $settingModel->get('platform_favicon', '');
                if (isset($_FILES['platform_favicon']) && $_FILES['platform_favicon']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['platform_favicon']['tmp_name'];
                    $fileName = $_FILES['platform_favicon']['name'];
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowed = ['ico', 'png', 'jpg', 'jpeg'];
                    if (in_array($ext, $allowed)) {
                        $destDir = BASE_PATH . '/public/uploads/settings/';
                        if (!is_dir($destDir)) {
                            mkdir($destDir, 0777, true);
                        }
                        $favName = 'favicon_' . time() . '.' . $ext;
                        if (move_uploaded_file($fileTmp, $destDir . $favName)) {
                            $faviconPath = 'public/uploads/settings/' . $favName;
                            $settingModel->set('platform_favicon', $faviconPath);
                        }
                    } else {
                        $error = 'Invalid favicon image type. ICO, PNG, JPG only.';
                    }
                }

                if (!$error) {
                    $oldCorrectionFee = $settingModel->get('correction_fee', '0.00');
                    $newCorrectionFee = trim($_POST['correction_fee'] ?? '0');

                    // Save Fee configurations
                    $settingModel->set('onboarding_fee', trim($_POST['onboarding_fee'] ?? '0'));
                    $settingModel->set('correction_fee', $newCorrectionFee);
                    $settingModel->set('bank_name', trim($_POST['bank_name'] ?? ''));
                    $settingModel->set('account_name', trim($_POST['account_name'] ?? ''));
                    $settingModel->set('account_number', trim($_POST['account_number'] ?? ''));
                    $settingModel->set('paystack_public_key', trim($_POST['paystack_public_key'] ?? ''));
                    $newSecretKey = trim($_POST['paystack_secret_key'] ?? '');
                    if ($newSecretKey !== '') {
                        $settingModel->set('paystack_secret_key', $newSecretKey);
                    }

                    // Save branding details
                    $settingModel->set('platform_title', trim($_POST['platform_title'] ?? ''));
                    
                    // Save Mission & Vision
                    $settingModel->set('mission', trim($_POST['mission'] ?? ''));
                    $settingModel->set('vision', trim($_POST['vision'] ?? ''));

                    // Save Socials & contacts
                    $settingModel->set('footer_contact_address', trim($_POST['footer_contact_address'] ?? ''));
                    $settingModel->set('footer_contact_phone', trim($_POST['footer_contact_phone'] ?? ''));
                    $settingModel->set('footer_contact_email', trim($_POST['footer_contact_email'] ?? ''));
                    $settingModel->set('social_twitter', trim($_POST['social_twitter'] ?? ''));
                    $settingModel->set('social_facebook', trim($_POST['social_facebook'] ?? ''));
                    $settingModel->set('social_instagram', trim($_POST['social_instagram'] ?? ''));

                    // Save Platform Policies
                    $settingModel->set('privacy_policy', trim($_POST['privacy_policy'] ?? ''));
                    $settingModel->set('terms_conditions', trim($_POST['terms_conditions'] ?? ''));

                    $logMsg = "Updated platform settings. Correction fee set from ₦" . number_format((float)$oldCorrectionFee, 2) . " to ₦" . number_format((float)$newCorrectionFee, 2);
                    AuditLog::log($_SESSION['user_id'], $logMsg);
                    ActivityLog::log("Super Admin: " . $logMsg);

                    $success = 'Platform settings updated successfully.';
                }
            }
        }

        $this->render('admin/settings', [
            'title' => 'Configure Platform Settings',
            'activePage' => 'settings',
            'settings' => $settingModel->getAllSettings(),
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    public function fields() {
        $dfModel = new DynamicField();
        $db = Database::getInstance();
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                if (isset($_POST['action']) && $_POST['action'] === 'delete') {
                    $id = (int)$_POST['field_id'];
                    $dfModel->delete($id);
                    AuditLog::log($_SESSION['user_id'], "Deleted dynamic field ID: {$id}");
                    $success = 'Field permanently removed from the system.';
                } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
                    $id = (int)$_POST['field_id'];
                    $active = isset($_POST['active']) ? (bool)$_POST['active'] : false;
                    $dfModel->setActive($id, $active);
                    $status = $active ? 'restored to forms' : 'removed from forms';
                    AuditLog::log($_SESSION['user_id'], "Dynamic field ID: {$id} {$status}");
                    $success = 'Field status updated successfully.';
                } elseif (isset($_POST['form_action']) && $_POST['form_action'] === 'update') {
                    $id = (int)($_POST['field_id'] ?? 0);
                    $entity = $_POST['entity'] ?? 'vehicle';
                    $fieldName = trim($_POST['field_name'] ?? '');
                    $fieldType = $_POST['field_type'] ?? 'text';
                    $options = trim($_POST['options'] ?? '');
                    $isRequired = isset($_POST['is_required']) ? 1 : 0;

                    if ($id <= 0 || empty($fieldName)) {
                        $error = 'Field selection is invalid or field name cannot be empty.';
                    } else {
                        $dfModel->update($id, [
                            'entity' => $entity,
                            'field_name' => $fieldName,
                            'field_type' => $fieldType,
                            'options' => $options,
                            'is_required' => $isRequired
                        ]);
                        AuditLog::log($_SESSION['user_id'], "Updated dynamic field ID: {$id} ({$fieldName})");
                        $success = 'Dynamic field updated successfully.';
                    }
                } else {
                    $entity = $_POST['entity'] ?? 'vehicle';
                    $fieldName = trim($_POST['field_name'] ?? '');
                    $fieldType = $_POST['field_type'] ?? 'text';
                    $options = trim($_POST['options'] ?? '');
                    $isRequired = isset($_POST['is_required']) ? 1 : 0;

                    if (empty($fieldName)) {
                        $error = 'Field name cannot be empty.';
                    } else {
                        $db->insert('dynamic_fields', [
                            'entity' => $entity,
                            'field_name' => $fieldName,
                            'field_type' => $fieldType,
                            'options' => $options,
                            'is_required' => $isRequired
                        ]);
                        AuditLog::log($_SESSION['user_id'], "Created dynamic field: {$fieldName} for {$entity}");
                        $success = 'Dynamic field added successfully.';
                    }
                }
            }
        }

        $this->render('admin/fields', [
            'title' => 'Form Manager',
            'activePage' => 'fields',
            'vehicleFields' => $dfModel->getFieldsForEntity('vehicle', true),
            'ownerFields' => $dfModel->getFieldsForEntity('owner', true),
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    public function logs() {
        $auditModel = new AuditLog();
        $activityModel = new ActivityLog();

        $this->render('admin/logs', [
            'title' => 'System & Audit Logs',
            'activePage' => 'logs',
            'auditLogs' => $auditModel->getLogs(),
            'activityLogs' => $activityModel->getLogs()
        ]);
    }

    public function profile() {
        $userModel = new \App\Models\User();
        $userId = $_SESSION['user_id'];
        $user = $userModel->find($userId);
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $firstName = trim($_POST['first_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $password = $_POST['password'] ?? '';

                // Uniqueness check for email/phone
                $db = Database::getInstance();
                $existingEmail = $db->fetch("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1", ['email' => $email, 'id' => $userId]);
                $existingPhone = $db->fetch("SELECT id FROM users WHERE phone = :phone AND id != :id LIMIT 1", ['phone' => $phone, 'id' => $userId]);

                if (empty($firstName) || empty($email) || empty($phone)) {
                    $error = 'First name, email, and phone number are required.';
                } elseif ($existingEmail) {
                    $error = 'A user with this email address already exists.';
                } elseif ($existingPhone) {
                    $error = 'A user with this phone number already exists.';
                } else {
                    $dataToUpdate = [
                        'first_name' => $firstName,
                        'email' => $email,
                        'phone' => $phone,
                    ];

                    if (!empty($password)) {
                        $dataToUpdate['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
                    }

                    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['avatar']['tmp_name'];
                        $fileName = $_FILES['avatar']['name'];
                        $fileSize = $_FILES['avatar']['size'];
                        
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));
                        
                        $allowedExtensions = ['jpg', 'jpeg', 'png'];
                        if (in_array($fileExtension, $allowedExtensions) && $fileSize < 3000000) {
                            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                            $uploadDir = BASE_PATH . '/public/uploads/avatars/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                                $dataToUpdate['avatar'] = 'public/uploads/avatars/' . $newFileName;
                            }
                        } else {
                            $error = 'Invalid avatar image type or size (Max 3MB, JPG/PNG only).';
                        }
                    }

                    if (!$error) {
                        $userModel->update($userId, $dataToUpdate);
                        AuditLog::log($userId, 'Updated profile details');
                        $success = 'Profile updated successfully.';
                        $user = $userModel->find($userId);
                    }
                }
            }
        }

        $this->render('admin/profile', [
            'title' => 'My Profile Settings',
            'activePage' => 'profile',
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    public function admins() {
        $db = Database::getInstance();
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $action = $_POST['action'] ?? '';

                // ── CREATE NEW ADMIN ──
                if ($action === 'create') {
                    $firstName = trim($_POST['first_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $role = $_POST['role'] ?? 'REGISTRATION_ADMIN';
                    $features = isset($_POST['features']) ? implode(',', $_POST['features']) : '';

                    $existingEmail = $db->fetch("SELECT id FROM users WHERE email = :email LIMIT 1", ['email' => $email]);
                    $existingPhone = $db->fetch("SELECT id FROM users WHERE phone = :phone LIMIT 1", ['phone' => $phone]);

                    if (empty($email) || empty($phone) || empty($password) || empty($firstName)) {
                        $error = 'First name, email, phone, and password are required.';
                    } elseif ($existingEmail) {
                        $error = 'A user with this email address already exists.';
                    } elseif ($existingPhone) {
                        $error = 'A user with this phone number already exists.';
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $db->insert('users', [
                            'first_name' => $firstName,
                            'email' => $email,
                            'phone' => $phone,
                            'password_hash' => $hash,
                            'role' => $role,
                            'features' => $features,
                            'is_active' => 1
                        ]);
                        AuditLog::log($_SESSION['user_id'], "Registered new admin: {$email} (Role: {$role}, Features: {$features})");
                        ActivityLog::log("Super Admin created new administrator: {$firstName} ({$email}) with role {$role}");
                        $success = 'New administrator registered successfully.';
                    }
                }

                // ── EDIT ADMIN (role, features, status, name, email, phone) ──
                if ($action === 'edit') {
                    $adminId = (int)$_POST['admin_id'];
                    $firstName = trim($_POST['first_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $role = $_POST['role'] ?? 'REGISTRATION_ADMIN';
                    $features = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    if ($adminId === (int)$_SESSION['user_id']) {
                        $error = 'You cannot modify your own roles or permissions from this panel.';
                    } else {
                        // Uniqueness check for email/phone
                        $existingEmail = $db->fetch("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1", ['email' => $email, 'id' => $adminId]);
                        $existingPhone = $db->fetch("SELECT id FROM users WHERE phone = :phone AND id != :id LIMIT 1", ['phone' => $phone, 'id' => $adminId]);

                        if (empty($firstName) || empty($email) || empty($phone)) {
                            $error = 'Name, email, and phone are required fields.';
                        } elseif ($existingEmail) {
                            $error = 'Another user with this email address already exists.';
                        } elseif ($existingPhone) {
                            $error = 'Another user with this phone number already exists.';
                        } else {
                            $oldAdmin = $db->fetch("SELECT * FROM users WHERE id = :id LIMIT 1", ['id' => $adminId]);
                            $db->update('users', [
                                'first_name' => $firstName,
                                'email' => $email,
                                'phone' => $phone,
                                'role' => $role,
                                'features' => $features,
                                'is_active' => $isActive
                            ], 'id = :id', ['id' => $adminId]);

                            $changes = [];
                            if ($oldAdmin && $oldAdmin['role'] !== $role) $changes[] = "Role: {$oldAdmin['role']}→{$role}";
                            if ($oldAdmin && $oldAdmin['features'] !== $features) $changes[] = "Features: {$features}";
                            if ($oldAdmin && (int)$oldAdmin['is_active'] !== $isActive) $changes[] = $isActive ? "Reactivated" : "Blocked";
                            if ($oldAdmin && $oldAdmin['email'] !== $email) $changes[] = "Email changed";
                            if ($oldAdmin && $oldAdmin['phone'] !== $phone) $changes[] = "Phone changed";

                            $changeStr = !empty($changes) ? implode(', ', $changes) : 'No changes';
                            AuditLog::log($_SESSION['user_id'], "Updated admin ID:{$adminId} ({$firstName}) — {$changeStr}");
                            ActivityLog::log("Super Admin updated admin {$firstName} (ID:{$adminId}): {$changeStr}");
                            $success = 'Administrator updated successfully.';
                        }
                    }
                }

                // ── RESET PASSWORD ──
                if ($action === 'reset_password') {
                    $adminId = (int)$_POST['admin_id'];
                    $newPassword = $_POST['new_password'] ?? '';

                    if ($adminId === (int)$_SESSION['user_id']) {
                        $error = 'Use your profile page to change your own password.';
                    } elseif (strlen($newPassword) < 6) {
                        $error = 'Password must be at least 6 characters.';
                    } else {
                        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                        $db->update('users', ['password_hash' => $hash], 'id = :id', ['id' => $adminId]);
                        $admin = $db->fetch("SELECT first_name, email FROM users WHERE id = :id LIMIT 1", ['id' => $adminId]);
                        $adminName = $admin ? $admin['first_name'] : "ID:{$adminId}";
                        AuditLog::log($_SESSION['user_id'], "Reset password for admin {$adminName} (ID:{$adminId})");
                        ActivityLog::log("Super Admin reset password for admin {$adminName}");
                        $success = "Password reset successfully for {$adminName}.";
                    }
                }

                // ── DELETE ADMIN ──
                if ($action === 'delete') {
                    $adminId = (int)$_POST['admin_id'];

                    if ($adminId === (int)$_SESSION['user_id']) {
                        $error = 'You cannot delete your own account.';
                    } else {
                        $admin = $db->fetch("SELECT first_name, email, role FROM users WHERE id = :id LIMIT 1", ['id' => $adminId]);
                        if (!$admin) {
                            $error = 'Administrator not found.';
                        } else {
                            $db->query("DELETE FROM users WHERE id = :id", ['id' => $adminId]);
                            AuditLog::log($_SESSION['user_id'], "Deleted admin: {$admin['first_name']} ({$admin['email']}) — Role was: {$admin['role']}");
                            ActivityLog::log("Super Admin permanently deleted admin: {$admin['first_name']} ({$admin['email']})");
                            $success = "Administrator {$admin['first_name']} has been permanently removed.";
                        }
                    }
                }
            }
        }

        // Fetch all admins
        $admins = $db->fetchAll("SELECT * FROM users WHERE role IN ('SUPER_ADMIN', 'REGISTRATION_ADMIN', 'VERIFICATION_ADMIN') ORDER BY id DESC");

        $this->render('admin/admins', [
            'title' => 'Manage Administrators & Access Controls',
            'activePage' => 'admins',
            'admins' => $admins,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    // Manage beneficiaries (SUPER_ADMIN only)
    public function beneficiaries() {
        $db = Database::getInstance();
        $beneficiaryModel = new \App\Models\Beneficiary();
        $error = null; $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $action = $_POST['action'] ?? '';

                if ($action === 'create') {
                    $firstName = trim($_POST['first_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $roleTitle = trim($_POST['role_title'] ?? 'Officer');
                    $commission = (float)($_POST['commission_percentage'] ?? 0);

                    $existing = $db->fetch("SELECT id FROM users WHERE email = :email LIMIT 1", ['email' => $email]);
                    if (empty($firstName) || empty($email) || empty($phone)) {
                        $error = 'Name, email and phone are required.';
                    } elseif ($existing) {
                        $error = 'A user with this email already exists.';
                    } else {
                        // create user account with random password
                        $password = trim($_POST['password'] ?? '');
                        if (empty($password)) {
                            $tempPass = substr(bin2hex(random_bytes(4)), 0, 8);
                            $password = $tempPass;
                        }
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $userId = $db->insert('users', [
                            'first_name' => $firstName,
                            'email' => $email,
                            'phone' => $phone,
                            'password_hash' => $hash,
                            'role' => 'BENEFICIARY',
                            'is_active' => 1
                        ]);

                        // create beneficiary profile
                        $beneficiaryModel->createProfile($userId, [
                            'role_title' => $roleTitle,
                            'commission_percentage' => $commission
                        ]);

                        // also add to commission_recipients table so they appear on Commission Board
                        $db->insert('commission_recipients', [
                            'user_id' => $userId,
                            'name' => $firstName,
                            'email' => $email,
                            'percentage_share' => $commission,
                            'bank_name' => null,
                            'account_number' => null,
                            'account_name' => null,
                            'total_paid' => 0.00
                        ]);

                        ActivityLog::log("Super Admin created beneficiary: {$firstName} ({$email})");
                        AuditLog::log($_SESSION['user_id'], "Created beneficiary user {$email}");
                        if (!empty($tempPass)) {
                            $success = "Beneficiary registered. Temporary password: {$tempPass} (share securely).";
                        } else {
                            $success = 'Beneficiary registered successfully with the provided password.';
                        }
                    }
                }

                if ($action === 'update') {
                    $userId = (int)$_POST['user_id'];
                    $firstName = trim($_POST['first_name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $roleTitle = trim($_POST['role_title'] ?? 'Officer');
                    $commission = (float)($_POST['commission_percentage'] ?? 0);
                    $password = $_POST['password'] ?? '';

                    $existingEmail = $db->fetch("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1", ['email' => $email, 'id' => $userId]);
                    if (empty($firstName) || empty($email) || empty($phone)) {
                        $error = 'Name, email and phone are required.';
                    } elseif ($existingEmail) {
                        $error = 'Another user with this email already exists.';
                    } else {
                        $pdo = $db->getConnection();
                        try {
                            $pdo->beginTransaction();
                            
                            $userUpdate = [
                                'first_name' => $firstName,
                                'email' => $email,
                                'phone' => $phone
                            ];
                            if (!empty($password)) {
                                $userUpdate['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
                            }
                            $db->update('users', $userUpdate, 'id = :id', ['id' => $userId]);

                            $db->update('beneficiaries', [
                                'role_title' => $roleTitle,
                                'commission_percentage' => $commission
                            ], 'user_id = :uid', ['uid' => $userId]);

                            $db->update('commission_recipients', [
                                'name' => $firstName,
                                'email' => $email,
                                'percentage_share' => $commission
                            ], 'user_id = :uid', ['uid' => $userId]);

                            $pdo->commit();
                            AuditLog::log($_SESSION['user_id'], "Updated beneficiary ID: {$userId} ({$email})");
                            $success = 'Beneficiary updated successfully.';
                        } catch (\Exception $e) {
                            $pdo->rollBack();
                            $error = 'Failed to update beneficiary: ' . $e->getMessage();
                        }
                    }
                }

                if ($action === 'delete') {
                    $userId = (int)($_POST['beneficiary_id'] ?? 0);
                    
                    $pdo = $db->getConnection();
                    try {
                        $pdo->beginTransaction();
                        
                        $profile = $db->fetch("SELECT * FROM beneficiaries WHERE user_id = :uid", ['uid' => $userId]);
                        if ($profile) {
                            $db->query("DELETE FROM beneficiary_earnings WHERE beneficiary_id = :bid", ['bid' => $profile['id']]);
                            $db->query("DELETE FROM beneficiaries WHERE id = :id", ['id' => $profile['id']]);
                        }
                        $db->query("DELETE FROM commission_recipients WHERE user_id = :uid", ['uid' => $userId]);
                        $db->query("DELETE FROM users WHERE id = :uid", ['uid' => $userId]);
                        
                        $pdo->commit();
                        AuditLog::log($_SESSION['user_id'], "Permanently deleted beneficiary user ID: {$userId}");
                        $success = 'Beneficiary permanently deleted from system.';
                    } catch (\Exception $e) {
                        $pdo->rollBack();
                        $error = 'Failed to delete beneficiary: ' . $e->getMessage();
                    }
                }

                if ($action === 'notify') {
                    $userId = (int)$_POST['user_id'];
                    $type = $_POST['type'] ?? 'DASHBOARD';
                    $message = trim($_POST['message'] ?? '');

                    if (empty($message)) {
                        $error = 'Notification message cannot be empty.';
                    } else {
                        $notificationModel = new \App\Models\Notification();
                        $notificationModel->send($userId, $type, $message);
                        
                        $userObj = $db->fetch("SELECT email FROM users WHERE id = :id", ['id' => $userId]);
                        $userEmail = $userObj ? $userObj['email'] : "ID:{$userId}";
                        
                        AuditLog::log($_SESSION['user_id'], "Sent {$type} notification to user {$userEmail}");
                        $success = 'Notification dispatched successfully.';
                    }
                }

                if ($action === 'suspend') {
                    $id = (int)($_POST['beneficiary_id'] ?? 0);
                    $profile = $db->fetch('SELECT * FROM beneficiaries WHERE id = :id', ['id' => $id]);
                    if ($profile) {
                        $beneficiaryModel->suspend($id, true);
                        $success = 'Beneficiary suspended.';
                        AuditLog::log($_SESSION['user_id'], "Suspended beneficiary ID: {$id}");
                    } else {
                        $error = 'Beneficiary not found.';
                    }
                }

                if ($action === 'activate') {
                    $id = (int)($_POST['beneficiary_id'] ?? 0);
                    $profile = $db->fetch('SELECT * FROM beneficiaries WHERE id = :id', ['id' => $id]);
                    if ($profile) {
                        $beneficiaryModel->suspend($id, false);
                        $success = 'Beneficiary reactivated.';
                        AuditLog::log($_SESSION['user_id'], "Reactivated beneficiary ID: {$id}");
                    } else {
                        $error = 'Beneficiary not found.';
                    }
                }
            }
        }

        $beneficiaries = $db->fetchAll('SELECT b.*, u.first_name, u.email, u.phone FROM beneficiaries b JOIN users u ON u.id = b.user_id ORDER BY b.id DESC');
        
        // Fetch account detail requests submitted by beneficiaries from activity_logs
        $requests = $db->fetchAll("
            SELECT al.*, u.first_name, u.email 
            FROM activity_logs al 
            JOIN users u ON u.id = al.performed_by 
            WHERE al.description LIKE 'Beneficiary account details update request%' 
            ORDER BY al.performed_at DESC LIMIT 15
        ");

        $this->render('admin/beneficiaries', [
            'title' => 'Manage Beneficiaries',
            'activePage' => 'beneficiaries',
            'beneficiaries' => $beneficiaries,
            'requests' => $requests,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

}
