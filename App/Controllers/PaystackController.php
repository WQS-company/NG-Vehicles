<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Setting;
use App\Models\DynamicField;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class PaystackController extends Controller {

    protected $db;
    protected $settingModel;
    protected $dynamicFieldModel;

    public function __construct() {
        Auth::requireAuth();
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('registration');
        }

        $this->db = Database::getInstance();
        $this->settingModel = new Setting();
        $this->dynamicFieldModel = new DynamicField();
    }

    public function initialize() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['status' => false, 'message' => 'Invalid CSRF token.']);
            return;
        }

        $secretKey = trim($this->settingModel->get('paystack_secret_key', ''));
        $publicKey = trim($this->settingModel->get('paystack_public_key', ''));
        if (empty($secretKey) || empty($publicKey)) {
            $this->json(['status' => false, 'message' => 'Paystack is not configured. Please ask the Super Admin to add Paystack keys in Settings.']);
            return;
        }

        $paymentMethod = trim($_POST['payment_method'] ?? '');
        if ($paymentMethod !== 'PAYSTACK') {
            $this->json(['status' => false, 'message' => 'Paystack initialization must be requested for Paystack transactions.']);
            return;
        }

        $amount = (float)($_POST['amount'] ?? 0);
        if ($amount <= 0) {
            $this->json(['status' => false, 'message' => 'Invalid payment amount.']);
            return;
        }

        $ownerEmail = trim($_POST['owner_email'] ?? '');
        if (empty($ownerEmail)) {
            $ownerEmail = trim($_POST['owner_email'] ?? '');
        }
        if (empty($ownerEmail)) {
            $ownerEmail = 'no-reply@nvots.gov.ng';
        }

        $reference = 'PSK-' . strtoupper(bin2hex(random_bytes(8)));
        $callbackUrl = rtrim(BASE_URL, '/') . '/paystack/verify';

        $sessionData = [
            'reference' => $reference,
            'amount' => $amount,
            'owner_email' => $ownerEmail,
            'payload' => $this->sanitizeRegistrationData($_POST),
            'created_at' => time()
        ];

        $tmpFiles = [];
        if (!empty($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
            $tmpFiles['vehicle_image_path'] = $this->storeTemporaryUpload('vehicle_image', ['jpg', 'jpeg', 'png'], 5000000);
        }
        if (!empty($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
            $tmpFiles['receipt_file_path'] = $this->storeTemporaryUpload('receipt_file', ['jpg', 'jpeg', 'png', 'pdf'], 5000000);
        }

        $sessionData['tmp_files'] = $tmpFiles;
        $_SESSION['pending_paystack_registration'] = $sessionData;

        $payload = [
            'email' => $ownerEmail,
            'amount' => (int)round($amount * 100),
            'currency' => 'NGN',
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'payment_method' => 'PAYSTACK',
                'context' => 'vehicle_onboarding'
            ]
        ];

        $response = $this->callPaystackApi('/transaction/initialize', $payload, $secretKey);
        if (empty($response['status']) || empty($response['data']['authorization_url'])) {
            $message = $response['message'] ?? 'Unable to initialize Paystack payment. Please verify your configuration.';
            $this->json(['status' => false, 'message' => $message]);
            return;
        }

        $_SESSION['pending_paystack_registration']['paystack_authorization'] = $response['data'];

        $this->json([
            'status' => true,
            'authorization_url' => $response['data']['authorization_url'],
            'reference' => $reference
        ]);
    }

    public function verify() {
        $reference = trim($_GET['reference'] ?? '');
        if (empty($reference)) {
            $this->renderVerificationResponse(false, 'Missing Paystack reference.');
            return;
        }

        $secretKey = trim($this->settingModel->get('paystack_secret_key', ''));
        if (empty($secretKey)) {
            $this->renderVerificationResponse(false, 'Paystack secret key is not configured.');
            return;
        }

        $response = $this->callPaystackApi('/transaction/verify/' . urlencode($reference), [], $secretKey, 'GET');
        if (empty($response['status']) || empty($response['data'])) {
            $message = $response['message'] ?? 'Unable to verify Paystack transaction.';
            $this->renderVerificationResponse(false, $message);
            return;
        }

        $transaction = $response['data'];
        if (($transaction['status'] ?? '') !== 'success') {
            $this->renderVerificationResponse(false, 'Paystack transaction failed or was not completed.');
            return;
        }

        $pending = $_SESSION['pending_paystack_registration'] ?? null;
        if (empty($pending) || ($pending['reference'] ?? '') !== $reference) {
            $this->renderVerificationResponse(false, 'No pending registration found for this Paystack transaction.');
            return;
        }

        if (((int)round($pending['amount'] * 100)) !== (int)$transaction['amount']) {
            $this->renderVerificationResponse(false, 'Paystack payment amount does not match the pending registration amount.');
            return;
        }

        $save = $this->finalizeRegistration($pending['payload'], $pending['tmp_files'] ?? [], $transaction);
        if (!$save['status']) {
            $this->renderVerificationResponse(false, $save['message']);
            return;
        }

        unset($_SESSION['pending_paystack_registration']);
        $_SESSION['paystack_success_message'] = 'Paystack payment was verified successfully. Vehicle registration has been completed.';
        header('Location: ' . rtrim(BASE_URL, '/') . '/vehicles/register');
        exit;
    }

    protected function renderVerificationResponse(bool $success, string $message) {
        if ($success) {
            $content = '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
        } else {
            $content = '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
        }
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Paystack Verification</title></head><body style="font-family:system-ui, sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">';
        echo '<div style="max-width:600px;width:100%;padding:24px;border-radius:16px;background:#111827;border:1px solid rgba(148,163,184,0.15);">';
        echo $content;
        echo '<p style="margin-top:16px;">Return to the <a href="' . htmlspecialchars(rtrim(BASE_URL, '/') . '/vehicles/register') . '" style="color:#38bdf8;">vehicle registration page</a>.</p>';
        echo '</div></body></html>';
    }

    protected function callPaystackApi(string $endpoint, array $payload, string $secret, string $method = 'POST'): array {
        $url = 'https://api.paystack.co' . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $secret,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['status' => false, 'message' => 'Paystack request failed: ' . $error];
        }

        $decoded = json_decode($raw, true);
        if ($decoded === null) {
            return ['status' => false, 'message' => 'Invalid Paystack response.'];
        }

        return $decoded;
    }

    protected function sanitizeRegistrationData(array $post): array {
        unset($post['csrf_token'], $post['submit']);
        return $post;
    }

    protected function storeTemporaryUpload(string $fieldName, array $allowedExtensions, int $maxSizeBytes): ?string {
        if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $fileTmpPath = $_FILES[$fieldName]['tmp_name'];
        $fileName = basename($_FILES[$fieldName]['name']);
        $fileSize = (int)$_FILES[$fieldName]['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions, true) || $fileSize > $maxSizeBytes) {
            return null;
        }

        $tmpDir = BASE_PATH . '/tmp/paystack/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpName = 'paystack_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $fileExtension;
        $destination = $tmpDir . $tmpName;

        if (move_uploaded_file($fileTmpPath, $destination)) {
            return $destination;
        }

        return null;
    }

    protected function finalizeRegistration(array $payload, array $tmpFiles, array $transaction): array {
        $vin = strtoupper(trim($payload['vin'] ?? ''));
        $engineNumber = strtoupper(trim($payload['engine_number'] ?? ''));
        $chassisNumber = strtoupper(trim($payload['chassis_number'] ?? ''));
        $plateNumber = strtoupper(trim($payload['plate_number'] ?? ''));
        $manufacturer = trim($payload['manufacturer'] ?? '');
        $model = trim($payload['model'] ?? '');
        $year = (int)($payload['year'] ?? 0);
        $color = trim($payload['color'] ?? '');
        $fuelType = trim($payload['fuel_type'] ?? '');
        $transmission = trim($payload['transmission'] ?? '');
        $category = trim($payload['category'] ?? '');
        $class = trim($payload['class'] ?? '');
        $ownerId = (int)($payload['owner_id'] ?? 0);
        $paymentAmount = (float)($payload['amount'] ?? 0);
        $receiptNumber = trim($payload['receipt_number'] ?? '');

        if (empty($vin) || empty($engineNumber) || empty($chassisNumber) || empty($plateNumber) || !$ownerId) {
            return ['status' => false, 'message' => 'Missing required registration fields.'];
        }

        $existingVin = $this->db->fetch('SELECT id FROM vehicles WHERE vin = :vin LIMIT 1', ['vin' => $vin]);
        $existingPlate = $this->db->fetch('SELECT id FROM vehicles WHERE plate_number = :plate LIMIT 1', ['plate' => $plateNumber]);
        $existingEngine = $this->db->fetch('SELECT id FROM vehicles WHERE engine_number = :engine LIMIT 1', ['engine' => $engineNumber]);
        $existingChassis = $this->db->fetch('SELECT id FROM vehicles WHERE chassis_number = :chassis LIMIT 1', ['chassis' => $chassisNumber]);

        if ($existingVin || $existingPlate || $existingEngine || $existingChassis) {
            return ['status' => false, 'message' => 'Duplicate vehicle identifier detected.'];
        }

        $registrationMeta = [];
        $fields = [
            'country_of_origin', 'country_of_manufacture', 'ship_year', 'importer_name', 'importer_company', 'importer_address', 'importer_email', 'importer_tel',
            'clearing_agent_name', 'clearing_agent_company', 'clearing_agent_address', 'clearing_agent_email', 'clearing_agent_tel',
            'foreign_office_name', 'foreign_office_company', 'foreign_office_address', 'foreign_office_email', 'foreign_office_tel',
            'port_name', 'port_company', 'port_address', 'port_email', 'port_tel', 'ship_departure_port', 'ship_departure_date', 'ship_landing_date',
            'custom_papers_status', 'purchase_date', 'purchase_amount', 'means_of_identification', 'insurance_cover', 'number_plate_state', 'number_plate_lga',
            'agent_name', 'agent_address', 'agent_tel', 'agent_email', 'tax_number',
            'vehicle_particulars_number', 'vehicle_particulars_purchase_date', 'vehicle_particulars_amount', 'vehicle_particulars_expiry_date',
            'pol_clearance_name', 'pol_clearance_rank', 'pol_clearance_office_address', 'pol_clearance_local_govt', 'pol_clearance_state', 'pol_clearance_tel', 'pol_clearance_email',
            'dl_name', 'dl_rank', 'dl_address', 'dl_tel', 'dl_email'
        ];

        foreach ($fields as $field) {
            $registrationMeta[$field] = isset($payload[$field]) ? $payload[$field] : '';
        }

        for ($i = 1; $i <= 3; $i++) {
            $registrationMeta["custom_officer_{$i}_name"] = trim($payload["custom_officer_{$i}_name"] ?? '');
            $registrationMeta["custom_officer_{$i}_rank"] = trim($payload["custom_officer_{$i}_rank"] ?? '');
            $registrationMeta["custom_officer_{$i}_address"] = trim($payload["custom_officer_{$i}_address"] ?? '');
            $registrationMeta["custom_officer_{$i}_tel"] = trim($payload["custom_officer_{$i}_tel"] ?? '');
            $registrationMeta["custom_officer_{$i}_email"] = trim($payload["custom_officer_{$i}_email"] ?? '');
            $registrationMeta["police_officer_{$i}_name"] = trim($payload["police_officer_{$i}_name"] ?? '');
            $registrationMeta["police_officer_{$i}_rank"] = trim($payload["police_officer_{$i}_rank"] ?? '');
            $registrationMeta["police_officer_{$i}_address"] = trim($payload["police_officer_{$i}_address"] ?? '');
            $registrationMeta["police_officer_{$i}_tel"] = trim($payload["police_officer_{$i}_tel"] ?? '');
            $registrationMeta["police_officer_{$i}_email"] = trim($payload["police_officer_{$i}_email"] ?? '');
            $registrationMeta["dss_officer_{$i}_name"] = trim($payload["dss_officer_{$i}_name"] ?? '');
            $registrationMeta["dss_officer_{$i}_rank"] = trim($payload["dss_officer_{$i}_rank"] ?? '');
            $registrationMeta["dss_officer_{$i}_address"] = trim($payload["dss_officer_{$i}_address"] ?? '');
            $registrationMeta["dss_officer_{$i}_tel"] = trim($payload["dss_officer_{$i}_tel"] ?? '');
            $registrationMeta["dss_officer_{$i}_email"] = trim($payload["dss_officer_{$i}_email"] ?? '');
            $registrationMeta["nia_officer_{$i}_name"] = trim($payload["nia_officer_{$i}_name"] ?? '');
            $registrationMeta["nia_officer_{$i}_rank"] = trim($payload["nia_officer_{$i}_rank"] ?? '');
            $registrationMeta["nia_officer_{$i}_address"] = trim($payload["nia_officer_{$i}_address"] ?? '');
            $registrationMeta["nia_officer_{$i}_tel"] = trim($payload["nia_officer_{$i}_tel"] ?? '');
            $registrationMeta["nia_officer_{$i}_email"] = trim($payload["nia_officer_{$i}_email"] ?? '');
        }

        $paymentReceiptPath = null;
        if (!empty($tmpFiles['receipt_file_path']) && file_exists($tmpFiles['receipt_file_path'])) {
            $paymentReceiptPath = $this->moveFileToPublic($tmpFiles['receipt_file_path'], BASE_PATH . '/public/uploads/receipts/', 'receipt_');
        }

        $vehicleImagePath = null;
        if (!empty($tmpFiles['vehicle_image_path']) && file_exists($tmpFiles['vehicle_image_path'])) {
            $vehicleImagePath = $this->moveFileToPublic($tmpFiles['vehicle_image_path'], BASE_PATH . '/public/uploads/vehicles/', 'vehicle_');
        }

        $pdo = $this->db->getConnection();
        try {
            $pdo->beginTransaction();

            $vehicleId = $this->db->insert('vehicles', [
                'vin' => $vin,
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
                'class' => $class,
                'image_path' => $vehicleImagePath,
                'custom_fields' => json_encode($registrationMeta)
            ]);

            $this->db->insert('ownership_history', [
                'vehicle_id' => $vehicleId,
                'owner_id' => $ownerId,
                'purchase_date' => date('Y-m-d'),
                'purchase_amount' => $paymentAmount,
                'market_name' => 'Initial Registry Onboarding',
                'seller_name' => 'FGN',
                'seller_phone' => '08000000000',
                'witness_name' => 'System Registry',
                'middleman_name' => 'Admin'
            ]);

            $receiptNumber = !empty($receiptNumber) ? $receiptNumber : ('PSK-' . strtoupper(bin2hex(random_bytes(4))));
            $this->db->insert('payments', [
                'vehicle_id' => $vehicleId,
                'owner_id' => $ownerId,
                'amount' => $paymentAmount,
                'payment_method' => 'PAYSTACK',
                'collected_by' => $_SESSION['user_id'],
                'receipt_number' => $receiptNumber,
                'receipt_file' => $paymentReceiptPath,
                'payment_date' => date('Y-m-d'),
                'paystack_reference' => $transaction['reference'] ?? null
            ]);

            $this->db->insert('verification_records', [
                'vehicle_id' => $vehicleId,
                'verifier_id' => $_SESSION['user_id'],
                'verification_type' => 'VEHICLE',
                'status' => 'PENDING',
                'notes' => 'Awaiting Paystack payment audit.'
            ]);

            $pdo->commit();
            AuditLog::log($_SESSION['user_id'], "Completed vehicle registration via Paystack. VIN: {$vin}, Plate: {$plateNumber}");
            ActivityLog::log("Paystack registration completed for vehicle {$plateNumber}");

            return ['status' => true, 'message' => 'Registration complete.'];
        } catch (\Exception $e) {
            $pdo->rollBack();
            return ['status' => false, 'message' => 'Unable to complete registration: ' . $e->getMessage()];
        }
    }

    protected function moveFileToPublic(string $source, string $destinationDir, string $prefix): ?string {
        if (!file_exists($source)) {
            return null;
        }

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $name = $prefix . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $target = rtrim($destinationDir, '/') . '/' . $name;

        if (rename($source, $target)) {
            return str_replace('\\', '/', substr($target, strlen(BASE_PATH) + 1));
        }

        return null;
    }
}
