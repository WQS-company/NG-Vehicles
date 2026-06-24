<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Vehicle;
use App\Models\Verification;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class VerificationController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('verification');
        }
    }

    public function manage() {
        $db = Database::getInstance();
        $verModel = new Verification();
        $vehicleModel = new Vehicle();
        
        $error = null;
        $success = null;

        // Perform audit/verification actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if (Auth::role() !== ROLE_SUPER_ADMIN) {
                Auth::requireFeature('verification');
            }
            
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $recordId = (int)$_POST['record_id'];
                $status = $_POST['status'] ?? 'PENDING';
                $notes = trim($_POST['notes'] ?? '');

                if ($status !== 'APPROVED' && $status !== 'REJECTED') {
                    $error = 'Invalid verification status selected.';
                } else {
                    $db->update('verification_records', [
                        'status' => $status,
                        'notes' => $notes,
                        'verified_at' => date('Y-m-d H:i:s'),
                        'verifier_id' => $_SESSION['user_id']
                    ], 'id = :id', ['id' => $recordId]);

                    $record = $verModel->find($recordId);
                    $vehicle = $vehicleModel->find($record['vehicle_id']);

                    AuditLog::log($_SESSION['user_id'], "Audited vehicle verification status ID {$recordId} to {$status}");
                    ActivityLog::log("Vehicle verification status update: Plate {$vehicle['plate_number']} changed to {$status}");
                    
                    $success = 'Vehicle audit status updated successfully.';
                    $verifiedVehicleId = $vehicle['id'];
                }
            }
        }

        // Fetch all verification requests
        $sql = "SELECT vr.*, v.plate_number, v.vin, v.manufacturer, v.model, u.email as verifier_email 
                FROM verification_records vr 
                JOIN vehicles v ON vr.vehicle_id = v.id 
                LEFT JOIN users u ON vr.verifier_id = u.id 
                ORDER BY vr.created_at DESC";
        $records = $db->fetchAll($sql);

        // Fetch vehicles for initiating new verification audits
        $vehicles = $vehicleModel->all();

        // Handle creating a verification request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_audit'])) {
            if (Auth::role() !== ROLE_SUPER_ADMIN) {
                Auth::requireFeature('verification');
            }
            
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $vehicleId = (int)$_POST['vehicle_id'];
                $type = $_POST['verification_type'] ?? 'VEHICLE';

                if (!$vehicleId) {
                    $error = 'Please select a vehicle to audit.';
                } else {
                    $db->insert('verification_records', [
                        'vehicle_id' => $vehicleId,
                        'verifier_id' => $_SESSION['user_id'],
                        'verification_type' => $type,
                        'status' => 'PENDING',
                        'notes' => 'New verification audit requested.'
                    ]);
                    $success = 'New verification request initiated successfully.';
                    $this->redirect('/verification/manage');
                }
            }
        }

        $this->render('verifications/manage', [
            'title' => 'Verification & Auditing Center',
            'activePage' => 'verification',
            'records' => $records,
            'vehicles' => $vehicles,
            'error' => $error,
            'success' => $success,
            'verifiedVehicleId' => $verifiedVehicleId ?? null,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    public function certificate($recordId) {
        $db = Database::getInstance();
        $recordId = (int)$recordId;

        // Fetch full certificate details
        $sql = "SELECT vr.*, v.plate_number, v.vin, v.engine_number, v.chassis_number, v.manufacturer, v.model, v.year, v.color,
                       u.email as verifier_email
                FROM verification_records vr 
                JOIN vehicles v ON vr.vehicle_id = v.id 
                LEFT JOIN users u ON vr.verifier_id = u.id 
                WHERE vr.id = :id";
        
        $certificate = $db->fetch($sql, ['id' => $recordId]);

        if (!$certificate || $certificate['status'] !== 'APPROVED') {
            die("Error: No approved verification certificate matching ID {$recordId} found.");
        }

        // Fetch full ownership history from first to current
        $historySql = "SELECT oh.*, o.full_name as owner_name, o.phone as owner_phone, o.nin as owner_nin, o.passport_photo_path
                       FROM ownership_history oh
                       JOIN owners o ON oh.owner_id = o.id
                       WHERE oh.vehicle_id = :vid
                       ORDER BY oh.purchase_date ASC, oh.created_at ASC";
        $ownershipHistory = $db->fetchAll($historySql, ['vid' => $certificate['vehicle_id']]);
        
        $currentOwner = !empty($ownershipHistory) ? end($ownershipHistory) : null;
        $certificate = array_merge($certificate, $currentOwner ?? []);

        // Generate QR code link targeting the trace / check page
        $verificationUrl = BASE_URL . "/search?q=" . urlencode($certificate['vin']);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verificationUrl);

        // Verification Number Generation
        $verificationNumber = "VER-" . str_pad($certificate['id'], 6, '0', STR_PAD_LEFT) . "-" . date('Y');

        $this->render('verifications/certificate', [
            'title' => 'Verification Certificate',
            'cert' => $certificate,
            'ownershipHistory' => $ownershipHistory,
            'qrCodeUrl' => $qrCodeUrl,
            'verificationNumber' => $verificationNumber
        ]);
    }
}
