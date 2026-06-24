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

class DocumentController extends Controller {

    public function __construct() {
        Auth::requireAuth();
    }

    /**
     * Generate a professional ownership document for a vehicle.
     * URL: /document/ownership/{vehicleId}
     */
    public function ownership($vehicleId) {
        $vehicleId = (int) $vehicleId;
        $db = Database::getInstance();
        $vehicleModel = new Vehicle();

        $vehicle = $vehicleModel->find($vehicleId);
        if (!$vehicle) {
            http_response_code(404);
            echo 'Vehicle not found.';
            exit;
        }

        // Fetch the complete ownership history with full owner details
        $ownershipHistory = $db->fetchAll("
            SELECT 
                oh.id AS history_id,
                oh.purchase_date,
                oh.purchase_amount,
                oh.market_name,
                oh.seller_name,
                oh.seller_phone,
                oh.witness_name,
                oh.middleman_name,
                oh.created_at AS history_created_at,
                o.id AS owner_id,
                o.full_name,
                o.phone,
                o.email,
                o.date_of_birth,
                o.gender,
                o.nationality,
                o.occupation,
                o.nin,
                o.bvn,
                o.address,
                o.state,
                o.lga,
                o.passport_photo_path,
                o.signature_path
            FROM ownership_history oh
            JOIN owners o ON oh.owner_id = o.id
            WHERE oh.vehicle_id = :vehicle_id
            ORDER BY oh.purchase_date ASC, oh.created_at ASC
        ", ['vehicle_id' => $vehicleId]);

        // Get verification status
        $verification = $db->fetch("
            SELECT status, verified_at 
            FROM verification_records 
            WHERE vehicle_id = :vid 
            ORDER BY created_at DESC 
            LIMIT 1
        ", ['vid' => $vehicleId]);

        // Issuing officer
        $user = Auth::user();
        $issuerName = !empty($user['first_name']) ? $user['first_name'] : explode('@', $user['email'])[0];

        // Generate document reference number
        $docRef = 'NVOTS-OWN-' . strtoupper(dechex($vehicleId)) . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Log the document generation
        AuditLog::log($_SESSION['user_id'], "Generated ownership document for Vehicle ID: {$vehicleId} (Plate: {$vehicle['plate_number']}). Ref: {$docRef}");
        ActivityLog::log("Ownership document generated for plate {$vehicle['plate_number']}. Ref: {$docRef}");

        // Render without the main layout — standalone printable document
        $viewFile = BASE_PATH . '/app/views/documents/ownership.php';
        if (!file_exists($viewFile)) {
            die("View 'documents/ownership' not found.");
        }

        // Pass data to the view
        $data = [
            'vehicle'          => $vehicle,
            'ownershipHistory' => $ownershipHistory,
            'verification'     => $verification,
            'issuerName'       => $issuerName,
            'issuerEmail'      => $user['email'],
            'docRef'           => $docRef,
            'generatedAt'      => date('F j, Y \a\t g:i A')
        ];
        extract($data);
        include $viewFile;
        exit;
    }
    /**
     * Generate Proof of Ownership Certificate
     */
    public function proof_of_ownership($vehicleId) {
        $this->renderDocument($vehicleId, 'proof_of_ownership', 'Proof of Ownership Certificate');
    }

    /**
     * Generate Vehicle License
     */
    public function vehicle_license($vehicleId) {
        $this->renderDocument($vehicleId, 'vehicle_license', 'Vehicle License');
    }

    /**
     * Generate Certificate of Insurance
     */
    public function certificate_of_insurance($vehicleId) {
        $this->renderDocument($vehicleId, 'certificate_of_insurance', 'Certificate of Insurance');
    }

    /**
     * Helper to render document templates with required data
     */
    private function renderDocument($vehicleId, $templateName, $docTitle) {
        $vehicleId = (int) $vehicleId;
        $db = Database::getInstance();
        $vehicleModel = new Vehicle();
        $settingModel = new Setting();

        $vehicle = $vehicleModel->find($vehicleId);
        if (!$vehicle) {
            http_response_code(404);
            echo 'Vehicle not found.';
            exit;
        }

        // Get Current Owner
        $owner = null;
        if (!empty($vehicle['current_owner_id'])) {
            $owner = $db->fetch("SELECT * FROM owners WHERE id = :id", ['id' => $vehicle['current_owner_id']]);
        }

        $settings = $settingModel->getAllSettings();

        // For Insurance
        $insurance = null;
        if ($templateName === 'certificate_of_insurance') {
            $insurance = $db->fetch("SELECT * FROM insurance_policies WHERE vehicle_id = :vid ORDER BY created_at DESC LIMIT 1", ['vid' => $vehicleId]);
        }

        // Issuing officer
        $user = Auth::user();
        $issuerName = !empty($user['first_name']) ? $user['first_name'] : explode('@', $user['email'])[0];

        // Generate document reference number
        $docRef = 'NVOTS-' . strtoupper(substr(md5($templateName), 0, 3)) . '-' . strtoupper(dechex($vehicleId)) . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

        AuditLog::log($_SESSION['user_id'], "Generated {$docTitle} for Vehicle ID: {$vehicleId}. Ref: {$docRef}");
        ActivityLog::log("{$docTitle} generated for plate {$vehicle['plate_number']}. Ref: {$docRef}");

        $viewFile = BASE_PATH . '/app/views/documents/' . $templateName . '.php';
        if (!file_exists($viewFile)) {
            die("View 'documents/{$templateName}' not found.");
        }

        $data = [
            'vehicle'      => $vehicle,
            'owner'        => $owner,
            'settings'     => $settings,
            'insurance'    => $insurance,
            'issuerName'   => $issuerName,
            'issuerEmail'  => $user['email'] ?? '',
            'docRef'       => $docRef,
            'generatedAt'  => date('F j, Y \a\t g:i A')
        ];
        extract($data);
        include $viewFile;
        exit;
    }
}
