<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Vehicle;
use App\Models\Owner;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class CorrectionController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        // Index, vehicle, and owner actions check feature inside, requests/verify are super admin only.
    }

    public function index() {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('correction');
        }

        $db = Database::getInstance();
        $vehicles = $db->fetchAll("SELECT * FROM vehicles ORDER BY id DESC");
        $owners = $db->fetchAll("SELECT * FROM owners ORDER BY id DESC");

        $this->render('correction/index', [
            'title' => 'Data Correction Center',
            'activePage' => 'correction',
            'vehicles' => $vehicles,
            'owners' => $owners
        ]);
    }

    public function vehicle($id) {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('correction');
        }

        $db = Database::getInstance();
        $vehicleModel = new Vehicle();
        $settingModel = new Setting();
        
        $vehicleId = (int)$id;
        $vehicle = $vehicleModel->find($vehicleId);
        if (!$vehicle) {
            die("Vehicle not found.");
        }

        // Get current active or historical correction requests for this vehicle
        $latestRequest = $db->fetch("
            SELECT * FROM correction_requests 
            WHERE entity_type = 'vehicle' AND entity_id = :vehicle_id 
            ORDER BY id DESC LIMIT 1
        ", ['vehicle_id' => $vehicleId]);

        $correctionFee = $settingModel->get('correction_fee', '0.00');
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $action = $_POST['action'] ?? '';

                if ($action === 'submit_request') {
                    // Path A: Submit new payment verification request
                    if ($latestRequest && $latestRequest['status'] === 'PENDING') {
                        $error = 'There is already a pending correction request for this vehicle.';
                    } elseif ($latestRequest && $latestRequest['status'] === 'VERIFIED' && !$latestRequest['is_corrected']) {
                        $error = 'This vehicle already has a verified payment. Please perform the correction below.';
                    } else {
                        $paymentMethod = $_POST['payment_method'] ?? 'CASH';
                        $receiptNumber = trim($_POST['receipt_number'] ?? '');
                        
                        // Process Receipt Proof file upload
                        $receiptFilePath = null;
                        if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                            $fileTmpPath = $_FILES['receipt_file']['tmp_name'];
                            $fileName = $_FILES['receipt_file']['name'];
                            $fileSize = $_FILES['receipt_file']['size'];
                            
                            $fileNameCmps = explode(".", $fileName);
                            $fileExtension = strtolower(end($fileNameCmps));
                            
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                            if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                                $uploadFileDir = BASE_PATH . '/public/uploads/receipts/';
                                
                                if (!is_dir($uploadFileDir)) {
                                    mkdir($uploadFileDir, 0777, true);
                                }
                                
                                $dest_path = $uploadFileDir . $newFileName;
                                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                    $receiptFilePath = 'public/uploads/receipts/' . $newFileName;
                                }
                            } else {
                                $error = 'Invalid receipt file type or size exceeded (Max 5MB, JPG/PNG/PDF only).';
                            }
                        }

                        if (!$error) {
                            $db->insert('correction_requests', [
                                'entity_type' => 'vehicle',
                                'entity_id' => $vehicleId,
                                'requested_by' => $_SESSION['user_id'],
                                'amount' => (float)$correctionFee,
                                'payment_method' => $paymentMethod,
                                'receipt_number' => !empty($receiptNumber) ? $receiptNumber : 'CORR-' . strtoupper(bin2hex(random_bytes(4))),
                                'receipt_file' => $receiptFilePath,
                                'status' => 'PENDING'
                            ]);

                            AuditLog::log($_SESSION['user_id'], "Submitted correction request for vehicle ID: {$vehicleId} (Fee: ₦{$correctionFee})");
                            ActivityLog::log("Correction request pending verification for vehicle ID: {$vehicleId} (Plate: {$vehicle['plate_number']})");
                            
                            $success = 'Data correction request submitted successfully. Awaiting Super Admin payment verification.';
                            
                            // Reload request
                            $latestRequest = $db->fetch("SELECT * FROM correction_requests WHERE entity_type = 'vehicle' AND entity_id = :vehicle_id ORDER BY id DESC LIMIT 1", ['vehicle_id' => $vehicleId]);
                        }
                    }
                } elseif ($action === 'apply_correction') {
                    // Path C: Apply correction (Request must be VERIFIED and not corrected yet)
                    if (!$latestRequest || $latestRequest['status'] !== 'VERIFIED' || $latestRequest['is_corrected']) {
                        $error = 'You do not have a verified correction request for this vehicle. Please submit a request first.';
                    } else {
                        $engineNumber = strtoupper(trim($_POST['engine_number'] ?? ''));
                        $chassisNumber = strtoupper(trim($_POST['chassis_number'] ?? ''));
                        $plateNumber = strtoupper(trim($_POST['plate_number'] ?? ''));
                        
                        $manufacturer = trim($_POST['manufacturer'] ?? '');
                        $model = trim($_POST['model'] ?? '');
                        $year = (int)($_POST['year'] ?? 0);
                        $color = trim($_POST['color'] ?? '');
                        $fuelType = trim($_POST['fuel_type'] ?? '');
                        $transmission = trim($_POST['transmission'] ?? '');
                        $category = trim($_POST['category'] ?? '');
                        $class = trim($_POST['class'] ?? '');

                        // Validate uniqueness
                        $existingPlate = $db->fetch("SELECT id FROM vehicles WHERE plate_number = :plate AND id != :id LIMIT 1", [
                            'plate' => $plateNumber,
                            'id' => $vehicleId
                        ]);

                        if (empty($engineNumber) || empty($chassisNumber) || empty($plateNumber)) {
                            $error = 'Plate number, engine number, and chassis number are required.';
                        } elseif ($existingPlate) {
                            $error = 'A vehicle with this plate number is already registered.';
                        } else {
                            $pdo = $db->getConnection();
                            try {
                                $pdo->beginTransaction();

                                // 1. Update vehicle data
                                $db->update('vehicles', [
                                    'engine_number' => $engineNumber,
                                    'chassis_number' => $chassisNumber,
                                    'plate_number' => $plateNumber,
                                    'manufacturer' => $manufacturer,
                                    'model' => $model,
                                    'year' => $year,
                                    'color' => $color,
                                    'fuel_type' => $fuelType,
                                    'transmission' => $transmission,
                                    'category' => $category,
                                    'class' => $class
                                ], 'id = :id', ['id' => $vehicleId]);

                                // 2. Mark request as completed
                                $db->update('correction_requests', [
                                    'is_corrected' => 1,
                                    'corrected_at' => date('Y-m-d H:i:s')
                                ], 'id = :req_id', ['req_id' => $latestRequest['id']]);

                                $pdo->commit();

                                AuditLog::log($_SESSION['user_id'], "Corrected vehicle details for ID: {$vehicleId} (Request ID: {$latestRequest['id']})");
                                ActivityLog::log("Vehicle info corrected (ID: {$vehicleId}, Plate: {$plateNumber}) by Admin: " . $_SESSION['user_id']);
                                
                                $success = 'Vehicle details corrected successfully.';
                                // Reload vehicle and request
                                $vehicle = $vehicleModel->find($vehicleId);
                                $latestRequest = $db->fetch("SELECT * FROM correction_requests WHERE entity_type = 'vehicle' AND entity_id = :vehicle_id ORDER BY id DESC LIMIT 1", ['vehicle_id' => $vehicleId]);
                            } catch (\Exception $e) {
                                $pdo->rollBack();
                                $error = 'Database operation failed: ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        $this->render('correction/vehicle', [
            'title' => 'Correct Vehicle Information',
            'activePage' => 'correction',
            'vehicle' => $vehicle,
            'latestRequest' => $latestRequest,
            'correctionFee' => $correctionFee,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    public function owner($id) {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('correction');
        }

        $db = Database::getInstance();
        $ownerModel = new Owner();
        $settingModel = new Setting();

        $ownerId = (int)$id;
        $owner = $ownerModel->find($ownerId);
        if (!$owner) {
            die("Owner not found.");
        }

        // Get current active or historical correction requests for this owner
        $latestRequest = $db->fetch("
            SELECT * FROM correction_requests 
            WHERE entity_type = 'owner' AND entity_id = :owner_id 
            ORDER BY id DESC LIMIT 1
        ", ['owner_id' => $ownerId]);

        $correctionFee = $settingModel->get('correction_fee', '0.00');
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $action = $_POST['action'] ?? '';

                if ($action === 'submit_request') {
                    // Path A: Submit new payment verification request
                    if ($latestRequest && $latestRequest['status'] === 'PENDING') {
                        $error = 'There is already a pending correction request for this owner.';
                    } elseif ($latestRequest && $latestRequest['status'] === 'VERIFIED' && !$latestRequest['is_corrected']) {
                        $error = 'This owner already has a verified payment. Please perform the correction below.';
                    } else {
                        $paymentMethod = $_POST['payment_method'] ?? 'CASH';
                        $receiptNumber = trim($_POST['receipt_number'] ?? '');
                        
                        // Process Receipt Proof file upload
                        $receiptFilePath = null;
                        if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                            $fileTmpPath = $_FILES['receipt_file']['tmp_name'];
                            $fileName = $_FILES['receipt_file']['name'];
                            $fileSize = $_FILES['receipt_file']['size'];
                            
                            $fileNameCmps = explode(".", $fileName);
                            $fileExtension = strtolower(end($fileNameCmps));
                            
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                            if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                                $uploadFileDir = BASE_PATH . '/public/uploads/receipts/';
                                
                                if (!is_dir($uploadFileDir)) {
                                    mkdir($uploadFileDir, 0777, true);
                                }
                                
                                $dest_path = $uploadFileDir . $newFileName;
                                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                    $receiptFilePath = 'public/uploads/receipts/' . $newFileName;
                                }
                            } else {
                                $error = 'Invalid receipt file type or size exceeded (Max 5MB, JPG/PNG/PDF only).';
                            }
                        }

                        if (!$error) {
                            $db->insert('correction_requests', [
                                'entity_type' => 'owner',
                                'entity_id' => $ownerId,
                                'requested_by' => $_SESSION['user_id'],
                                'amount' => (float)$correctionFee,
                                'payment_method' => $paymentMethod,
                                'receipt_number' => !empty($receiptNumber) ? $receiptNumber : 'CORR-' . strtoupper(bin2hex(random_bytes(4))),
                                'receipt_file' => $receiptFilePath,
                                'status' => 'PENDING'
                            ]);

                            AuditLog::log($_SESSION['user_id'], "Submitted correction request for owner ID: {$ownerId} (Fee: ₦{$correctionFee})");
                            ActivityLog::log("Correction request pending verification for owner ID: {$ownerId} (Name: {$owner['full_name']})");
                            
                            $success = 'Data correction request submitted successfully. Awaiting Super Admin payment verification.';
                            
                            // Reload request
                            $latestRequest = $db->fetch("SELECT * FROM correction_requests WHERE entity_type = 'owner' AND entity_id = :owner_id ORDER BY id DESC LIMIT 1", ['owner_id' => $ownerId]);
                        }
                    }
                } elseif ($action === 'apply_correction') {
                    // Path C: Apply correction (Request must be VERIFIED and not corrected yet)
                    if (!$latestRequest || $latestRequest['status'] !== 'VERIFIED' || $latestRequest['is_corrected']) {
                        $error = 'You do not have a verified correction request for this owner. Please submit a request first.';
                    } else {
                        $fullName = trim($_POST['full_name'] ?? '');
                        $phone = trim($_POST['phone'] ?? '');
                        $email = trim($_POST['email'] ?? '');
                        $dob = trim($_POST['date_of_birth'] ?? '');
                        $gender = $_POST['gender'] ?? 'Male';
                        $nationality = trim($_POST['nationality'] ?? 'Nigeria');
                        $occupation = trim($_POST['occupation'] ?? '');
                        $nin = trim($_POST['nin'] ?? '');
                        $bvn = trim($_POST['bvn'] ?? '');
                        $address = trim($_POST['address'] ?? '');
                        $state = trim($_POST['state'] ?? '');
                        $lga = trim($_POST['lga'] ?? '');

                        // Validate uniqueness
                        $existingPhone = $db->fetch("SELECT id FROM owners WHERE phone = :phone AND id != :id LIMIT 1", ['phone' => $phone, 'id' => $ownerId]);
                        $existingEmail = $db->fetch("SELECT id FROM owners WHERE email = :email AND id != :id LIMIT 1", ['email' => $email, 'id' => $ownerId]);

                        if (empty($fullName) || empty($phone) || empty($email) || empty($nin)) {
                            $error = 'Full Name, Phone, Email, and NIN are required.';
                        } elseif ($existingPhone) {
                            $error = 'Another owner profile with this phone number already exists.';
                        } elseif ($existingEmail) {
                            $error = 'Another owner profile with this email address already exists.';
                        } else {
                            $pdo = $db->getConnection();
                            try {
                                $pdo->beginTransaction();

                                // 1. Update owner data
                                $db->update('owners', [
                                    'full_name' => $fullName,
                                    'phone' => $phone,
                                    'email' => $email,
                                    'date_of_birth' => $dob,
                                    'gender' => $gender,
                                    'nationality' => $nationality,
                                    'occupation' => $occupation,
                                    'nin' => $nin,
                                    'bvn' => $bvn,
                                    'address' => $address,
                                    'state' => $state,
                                    'lga' => $lga
                                ], 'id = :id', ['id' => $ownerId]);

                                // 2. Mark request as completed
                                $db->update('correction_requests', [
                                    'is_corrected' => 1,
                                    'corrected_at' => date('Y-m-d H:i:s')
                                ], 'id = :req_id', ['req_id' => $latestRequest['id']]);

                                $pdo->commit();

                                AuditLog::log($_SESSION['user_id'], "Corrected owner details for ID: {$ownerId} (Request ID: {$latestRequest['id']})");
                                ActivityLog::log("Owner profile details corrected (ID: {$ownerId}, Name: {$fullName}) by Admin: " . $_SESSION['user_id']);

                                $success = 'Owner profile details corrected successfully.';
                                // Reload owner and request
                                $owner = $ownerModel->find($ownerId);
                                $latestRequest = $db->fetch("SELECT * FROM correction_requests WHERE entity_type = 'owner' AND entity_id = :owner_id ORDER BY id DESC LIMIT 1", ['owner_id' => $ownerId]);
                            } catch (\Exception $e) {
                                $pdo->rollBack();
                                $error = 'Database operation failed: ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        $this->render('correction/owner', [
            'title' => 'Correct Owner Information',
            'activePage' => 'correction',
            'owner' => $owner,
            'latestRequest' => $latestRequest,
            'correctionFee' => $correctionFee,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    public function requests() {
        Auth::requireRole(ROLE_SUPER_ADMIN);
        $db = Database::getInstance();

        $requests = $db->fetchAll("
            SELECT cr.*, 
                   u.first_name as requester_name, u.email as requester_email,
                   v.plate_number as vehicle_plate, v.manufacturer as vehicle_make, v.model as vehicle_model,
                   o.full_name as owner_name, o.phone as owner_phone
            FROM correction_requests cr
            JOIN users u ON u.id = cr.requested_by
            LEFT JOIN vehicles v ON cr.entity_type = 'vehicle' AND v.id = cr.entity_id
            LEFT JOIN owners o ON cr.entity_type = 'owner' AND o.id = cr.entity_id
            ORDER BY cr.id DESC
        ");

        foreach ($requests as &$r) {
            if ($r['entity_type'] === 'vehicle') {
                $r['target_identifier'] = $r['vehicle_plate'] ?: 'No Plate';
                $r['target_detail'] = ($r['vehicle_make'] && $r['vehicle_model']) ? $r['vehicle_make'] . ' ' . $r['vehicle_model'] : 'Vehicle ID: ' . $r['entity_id'];
            } else {
                $r['target_identifier'] = $r['owner_name'] ?: 'Unknown Owner';
                $r['target_detail'] = $r['owner_phone'] ? 'Phone: ' . $r['owner_phone'] : 'Owner ID: ' . $r['entity_id'];
            }
        }
        unset($r);

        $this->render('correction/requests', [
            'title' => 'Correction Requests Manager',
            'activePage' => 'correction_requests',
            'requests' => $requests,
            'error' => $_SESSION['corr_error'] ?? null,
            'success' => $_SESSION['corr_success'] ?? null,
            'csrfToken' => $this->generateCsrfToken()
        ]);

        unset($_SESSION['corr_error'], $_SESSION['corr_success']);
    }

    public function verify($id) {
        Auth::requireRole(ROLE_SUPER_ADMIN);
        $db = Database::getInstance();

        $reqId = (int)$id;
        $request = $db->fetch("SELECT * FROM correction_requests WHERE id = :id", ['id' => $reqId]);
        if (!$request) {
            $_SESSION['corr_error'] = 'Correction request not found.';
            $this->redirect('correction/requests');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $_SESSION['corr_error'] = 'Invalid CSRF token.';
                $this->redirect('correction/requests');
            }

            $decision = $_POST['decision'] ?? '';
            if ($decision === 'approve') {
                $pdo = $db->getConnection();
                try {
                    $pdo->beginTransaction();

                    // 1. Mark request as verified
                    $db->update('correction_requests', [
                        'status' => 'VERIFIED',
                        'verified_by' => $_SESSION['user_id'],
                        'verified_at' => date('Y-m-d H:i:s')
                    ], 'id = :id', ['id' => $reqId]);

                    // 2. Record payment in platforms payments table
                    // Determine vehicle_id and owner_id to associate with this payment
                    $vehicleId = 0;
                    $ownerId = 0;

                    if ($request['entity_type'] === 'vehicle') {
                        $vehicleId = (int)$request['entity_id'];
                        // Find current owner of the vehicle
                        $currentOwner = $db->fetch("
                            SELECT owner_id FROM ownership_history 
                            WHERE vehicle_id = :vehicle_id 
                            ORDER BY created_at DESC LIMIT 1
                        ", ['vehicle_id' => $vehicleId]);
                        
                        $ownerId = $currentOwner ? (int)$currentOwner['owner_id'] : 1; // Fallback to 1
                    } else {
                        $ownerId = (int)$request['entity_id'];
                        // Find any linked vehicle for this owner
                        $linkedVehicle = $db->fetch("
                            SELECT vehicle_id FROM ownership_history 
                            WHERE owner_id = :owner_id 
                            ORDER BY created_at DESC LIMIT 1
                        ", ['owner_id' => $ownerId]);
                        
                        if ($linkedVehicle) {
                            $vehicleId = (int)$linkedVehicle['vehicle_id'];
                        } else {
                            $anyVehicle = $db->fetch("SELECT id FROM vehicles LIMIT 1");
                            $vehicleId = $anyVehicle ? (int)$anyVehicle['id'] : 1; // Fallback
                        }
                    }

                    $db->insert('payments', [
                        'vehicle_id' => $vehicleId,
                        'owner_id' => $ownerId,
                        'amount' => $request['amount'],
                        'payment_method' => $request['payment_method'],
                        'collected_by' => $request['requested_by'], // The admin who collected/uploaded it
                        'receipt_number' => $request['receipt_number'],
                        'receipt_file' => $request['receipt_file'],
                        'payment_date' => date('Y-m-d')
                    ]);

                    $pdo->commit();

                    $logMsg = "Approved correction payment for request #" . $reqId . " (Fee: ₦" . number_format($request['amount'], 2) . ", Receipt: " . $request['receipt_number'] . ")";
                    AuditLog::log($_SESSION['user_id'], $logMsg);
                    ActivityLog::log("Super Admin: " . $logMsg);

                    $_SESSION['corr_success'] = 'Correction payment verified and correction window unlocked successfully.';
                } catch (\Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['corr_error'] = 'Database transaction failed: ' . $e->getMessage();
                }
            } elseif ($decision === 'reject') {
                $db->update('correction_requests', [
                    'status' => 'REJECTED',
                    'verified_by' => $_SESSION['user_id'],
                    'verified_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $reqId]);

                $logMsg = "Rejected correction payment proof for request #" . $reqId;
                AuditLog::log($_SESSION['user_id'], $logMsg);
                ActivityLog::log("Super Admin: " . $logMsg);

                $_SESSION['corr_success'] = 'Correction request payment proof has been rejected.';
            }
        }

        $this->redirect('correction/requests');
    }
}
