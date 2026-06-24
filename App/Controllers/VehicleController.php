<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Vehicle;
use App\Models\Owner;
use App\Models\DynamicField;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class VehicleController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        // Allow 'list' action for all authenticated users
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($requestUri, '/'));
        $action = $segments[count($segments) - 1] ?? '';
        if ($action !== 'list' && $action !== 'view' && Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('registration');
        }
    }

    /**
     * Display all registered vehicles in a professional DataTable.
     * GET /vehicle/list
     */
    public function list() {
        $db = Database::getInstance();

        // Fetch all vehicles with their current owner (latest ownership record)
        $sql = "SELECT v.*, 
                       o.full_name AS owner_name, 
                       o.phone AS owner_phone,
                       o.state AS owner_state,
                       vr.status AS verification_status
                FROM vehicles v
                LEFT JOIN (
                    SELECT oh1.vehicle_id, oh1.owner_id
                    FROM ownership_history oh1
                    INNER JOIN (
                        SELECT vehicle_id, MAX(id) AS max_id
                        FROM ownership_history
                        GROUP BY vehicle_id
                    ) oh2 ON oh1.id = oh2.max_id
                ) latest_oh ON latest_oh.vehicle_id = v.id
                LEFT JOIN owners o ON o.id = latest_oh.owner_id
                LEFT JOIN (
                    SELECT vr1.vehicle_id, vr1.status
                    FROM verification_records vr1
                    INNER JOIN (
                        SELECT vehicle_id, MAX(id) AS max_id
                        FROM verification_records
                        GROUP BY vehicle_id
                    ) vr2 ON vr1.id = vr2.max_id
                ) vr ON vr.vehicle_id = v.id
                ORDER BY v.created_at DESC";

        $vehicles = $db->fetchAll($sql);

        // Parse custom_fields for state/LGA info
        foreach ($vehicles as &$vehicle) {
            $custom = [];
            if (!empty($vehicle['custom_fields'])) {
                $custom = json_decode($vehicle['custom_fields'], true) ?: [];
            }
            $vehicle['reg_state'] = $custom['number_plate_state'] ?? '';
            $vehicle['reg_lga'] = $custom['number_plate_lga'] ?? '';
        }
        unset($vehicle);

        $totalVehicles = count($vehicles);

        $this->render('vehicles/list', [
            'title'         => 'All Registered Vehicles — Nigeria',
            'activePage'    => 'vehicles',
            'vehicles'      => $vehicles,
            'totalVehicles' => $totalVehicles
        ]);
    }

    /**
     * Display full details of a single vehicle.
     * GET /vehicle/view/{id}
     */
    public function view($id = null) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            echo 'Vehicle not found.';
            exit;
        }

        $db = Database::getInstance();

        // Fetch the vehicle
        $vehicle = $db->fetch("SELECT * FROM vehicles WHERE id = :id", ['id' => (int)$id]);
        if (!$vehicle) {
            http_response_code(404);
            echo 'Vehicle not found.';
            exit;
        }

        // Parse custom fields
        $customFields = [];
        if (!empty($vehicle['custom_fields'])) {
            $customFields = json_decode($vehicle['custom_fields'], true) ?: [];
        }

        // Fetch ALL ownership history (ordered newest first)
        $ownershipHistory = $db->fetchAll(
            "SELECT oh.*, o.full_name, o.phone, o.email AS owner_email, o.nin, o.bvn, 
                    o.address AS owner_address, o.state AS owner_state, o.lga AS owner_lga,
                    o.nationality, o.occupation, o.gender, o.date_of_birth,
                    o.passport_photo_path, o.signature_path
             FROM ownership_history oh
             LEFT JOIN owners o ON o.id = oh.owner_id
             WHERE oh.vehicle_id = :vid
             ORDER BY oh.id DESC",
            ['vid' => $vehicle['id']]
        );

        // Current owner is the latest record
        $currentOwner = !empty($ownershipHistory) ? $ownershipHistory[0] : null;
        // Previous owners are all remaining records
        $previousOwners = count($ownershipHistory) > 1 ? array_slice($ownershipHistory, 1) : [];

        // Fetch all transfers
        $transfers = $db->fetchAll(
            "SELECT ot.*, 
                    seller.full_name AS seller_name, seller.phone AS seller_phone,
                    buyer.full_name AS buyer_name, buyer.phone AS buyer_phone,
                    u.email AS approved_by_email, u.first_name AS approved_by_name
             FROM ownership_transfers ot
             LEFT JOIN owners seller ON seller.id = ot.seller_id
             LEFT JOIN owners buyer ON buyer.id = ot.buyer_id
             LEFT JOIN users u ON u.id = ot.approved_by
             WHERE ot.vehicle_id = :vid
             ORDER BY ot.id DESC",
            ['vid' => $vehicle['id']]
        );

        // Fetch all payments
        $payments = $db->fetchAll(
            "SELECT p.*, o.full_name AS payer_name, u.email AS collector_email, u.first_name AS collector_name
             FROM payments p
             LEFT JOIN owners o ON o.id = p.owner_id
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE p.vehicle_id = :vid
             ORDER BY p.id DESC",
            ['vid' => $vehicle['id']]
        );

        // Fetch all verification records
        $verifications = $db->fetchAll(
            "SELECT vr.*, u.email AS verifier_email, u.first_name AS verifier_name
             FROM verification_records vr
             LEFT JOIN users u ON u.id = vr.verifier_id
             WHERE vr.vehicle_id = :vid
             ORDER BY vr.id DESC",
            ['vid' => $vehicle['id']]
        );

        // Latest verification status
        $latestVerification = !empty($verifications) ? $verifications[0] : null;

        // Total payments
        $totalPayments = $db->fetch(
            "SELECT SUM(amount) AS total FROM payments WHERE vehicle_id = :vid",
            ['vid' => $vehicle['id']]
        );

        // Vehicle image URL
        $vehicleImageUrl = '';
        if (!empty($vehicle['image_path'])) {
            $vehicleImageUrl = rtrim(BASE_URL, '/') . '/' . ltrim($vehicle['image_path'], '/');
        }

        $csrfToken = $this->generateCsrfToken();

        $this->render('vehicles/view', [
            'title'              => 'Vehicle Details — ' . $vehicle['plate_number'],
            'activePage'         => 'vehicles',
            'vehicle'            => $vehicle,
            'customFields'       => $customFields,
            'currentOwner'       => $currentOwner,
            'previousOwners'     => $previousOwners,
            'ownershipHistory'   => $ownershipHistory,
            'transfers'          => $transfers,
            'payments'           => $payments,
            'verifications'      => $verifications,
            'latestVerification' => $latestVerification,
            'totalPayments'      => $totalPayments['total'] ?? 0,
            'vehicleImageUrl'    => $vehicleImageUrl,
            'csrfToken'          => $csrfToken
        ]);
    }

    public function register() {
        $db = Database::getInstance();
        $ownerModel = new Owner();
        $dfModel = new DynamicField();
        $settingModel = new Setting();
        
        $error = null;
        $success = null;

        if (!empty($_SESSION['paystack_success_message'])) {
            $success = $_SESSION['paystack_success_message'];
            unset($_SESSION['paystack_success_message']);
        }

        // Fetch dynamic fields and owners
        $dynamicFields = $dfModel->getFieldsForEntity('vehicle');
        $owners = $ownerModel->all();
        $onboardingFee = $settingModel->get('onboarding_fee', '0.00');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $vin = strtoupper(trim($_POST['vin'] ?? ''));
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
                $rfidTag = trim($_POST['rfid_tag'] ?? '');
                $qrCode = trim($_POST['qr_code'] ?? '');
                
                $ownerId = (int)($_POST['owner_id'] ?? 0);
                
                // Payment parameters
                $paymentMethod = $_POST['payment_method'] ?? 'CASH';
                $receiptNumber = trim($_POST['receipt_number'] ?? '');
                $paymentAmount = (float)$onboardingFee;

                // Imported registration metadata
                $registrationMeta = [
                    'country_of_origin' => trim($_POST['country_of_origin'] ?? ''),
                    'country_of_manufacture' => trim($_POST['country_of_manufacture'] ?? ''),
                    'ship_year' => trim($_POST['ship_year'] ?? ''),
                    'importer_name' => trim($_POST['importer_name'] ?? ''),
                    'importer_company' => trim($_POST['importer_company'] ?? ''),
                    'importer_address' => trim($_POST['importer_address'] ?? ''),
                    'importer_email' => trim($_POST['importer_email'] ?? ''),
                    'importer_tel' => trim($_POST['importer_tel'] ?? ''),
                    'clearing_agent_name' => trim($_POST['clearing_agent_name'] ?? ''),
                    'clearing_agent_company' => trim($_POST['clearing_agent_company'] ?? ''),
                    'clearing_agent_address' => trim($_POST['clearing_agent_address'] ?? ''),
                    'clearing_agent_email' => trim($_POST['clearing_agent_email'] ?? ''),
                    'clearing_agent_tel' => trim($_POST['clearing_agent_tel'] ?? ''),
                    'foreign_office_name' => trim($_POST['foreign_office_name'] ?? ''),
                    'foreign_office_company' => trim($_POST['foreign_office_company'] ?? ''),
                    'foreign_office_address' => trim($_POST['foreign_office_address'] ?? ''),
                    'foreign_office_email' => trim($_POST['foreign_office_email'] ?? ''),
                    'foreign_office_tel' => trim($_POST['foreign_office_tel'] ?? ''),
                    'port_name' => trim($_POST['port_name'] ?? ''),
                    'port_company' => trim($_POST['port_company'] ?? ''),
                    'port_address' => trim($_POST['port_address'] ?? ''),
                    'port_email' => trim($_POST['port_email'] ?? ''),
                    'port_tel' => trim($_POST['port_tel'] ?? ''),
                    'ship_departure_port' => trim($_POST['ship_departure_port'] ?? ''),
                    'ship_departure_date' => trim($_POST['ship_departure_date'] ?? ''),
                    'ship_landing_date' => trim($_POST['ship_landing_date'] ?? ''),
                    'custom_papers_status' => trim($_POST['custom_papers_status'] ?? ''),
                    'purchase_date' => trim($_POST['purchase_date'] ?? ''),
                    'purchase_amount' => trim($_POST['purchase_amount'] ?? ''),
                    'means_of_identification' => isset($_POST['means_of_identification']) ? (array)$_POST['means_of_identification'] : [],
                    'insurance_cover' => trim($_POST['insurance_cover'] ?? ''),
                    'number_plate_state' => trim($_POST['number_plate_state'] ?? ''),
                    'number_plate_lga' => trim($_POST['number_plate_lga'] ?? ''),
                    'agent_name' => trim($_POST['agent_name'] ?? ''),
                    'agent_address' => trim($_POST['agent_address'] ?? ''),
                    'agent_tel' => trim($_POST['agent_tel'] ?? ''),
                    'agent_email' => trim($_POST['agent_email'] ?? ''),
                    'tax_number' => trim($_POST['tax_number'] ?? ''),
                    'vehicle_particulars_number' => trim($_POST['vehicle_particulars_number'] ?? ''),
                    'vehicle_particulars_purchase_date' => trim($_POST['vehicle_particulars_purchase_date'] ?? ''),
                    'vehicle_particulars_amount' => trim($_POST['vehicle_particulars_amount'] ?? ''),
                    'vehicle_particulars_expiry_date' => trim($_POST['vehicle_particulars_expiry_date'] ?? ''),
                    'pol_clearance_name' => trim($_POST['pol_clearance_name'] ?? ''),
                    'pol_clearance_rank' => trim($_POST['pol_clearance_rank'] ?? ''),
                    'pol_clearance_office_address' => trim($_POST['pol_clearance_office_address'] ?? ''),
                    'pol_clearance_local_govt' => trim($_POST['pol_clearance_local_govt'] ?? ''),
                    'pol_clearance_state' => trim($_POST['pol_clearance_state'] ?? ''),
                    'pol_clearance_tel' => trim($_POST['pol_clearance_tel'] ?? ''),
                    'pol_clearance_email' => trim($_POST['pol_clearance_email'] ?? ''),
                    'dl_name' => trim($_POST['dl_name'] ?? ''),
                    'dl_rank' => trim($_POST['dl_rank'] ?? ''),
                    'dl_address' => trim($_POST['dl_address'] ?? ''),
                    'dl_tel' => trim($_POST['dl_tel'] ?? ''),
                    'dl_email' => trim($_POST['dl_email'] ?? ''),
                ];

                for ($i = 1; $i <= 3; $i++) {
                    $registrationMeta["custom_officer_{$i}_name"] = trim($_POST["custom_officer_{$i}_name"] ?? '');
                    $registrationMeta["custom_officer_{$i}_rank"] = trim($_POST["custom_officer_{$i}_rank"] ?? '');
                    $registrationMeta["custom_officer_{$i}_address"] = trim($_POST["custom_officer_{$i}_address"] ?? '');
                    $registrationMeta["custom_officer_{$i}_tel"] = trim($_POST["custom_officer_{$i}_tel"] ?? '');
                    $registrationMeta["custom_officer_{$i}_email"] = trim($_POST["custom_officer_{$i}_email"] ?? '');
                    $registrationMeta["police_officer_{$i}_name"] = trim($_POST["police_officer_{$i}_name"] ?? '');
                    $registrationMeta["police_officer_{$i}_rank"] = trim($_POST["police_officer_{$i}_rank"] ?? '');
                    $registrationMeta["police_officer_{$i}_address"] = trim($_POST["police_officer_{$i}_address"] ?? '');
                    $registrationMeta["police_officer_{$i}_tel"] = trim($_POST["police_officer_{$i}_tel"] ?? '');
                    $registrationMeta["police_officer_{$i}_email"] = trim($_POST["police_officer_{$i}_email"] ?? '');
                    $registrationMeta["dss_officer_{$i}_name"] = trim($_POST["dss_officer_{$i}_name"] ?? '');
                    $registrationMeta["dss_officer_{$i}_rank"] = trim($_POST["dss_officer_{$i}_rank"] ?? '');
                    $registrationMeta["dss_officer_{$i}_address"] = trim($_POST["dss_officer_{$i}_address"] ?? '');
                    $registrationMeta["dss_officer_{$i}_tel"] = trim($_POST["dss_officer_{$i}_tel"] ?? '');
                    $registrationMeta["dss_officer_{$i}_email"] = trim($_POST["dss_officer_{$i}_email"] ?? '');
                    $registrationMeta["nia_officer_{$i}_name"] = trim($_POST["nia_officer_{$i}_name"] ?? '');
                    $registrationMeta["nia_officer_{$i}_rank"] = trim($_POST["nia_officer_{$i}_rank"] ?? '');
                    $registrationMeta["nia_officer_{$i}_address"] = trim($_POST["nia_officer_{$i}_address"] ?? '');
                    $registrationMeta["nia_officer_{$i}_tel"] = trim($_POST["nia_officer_{$i}_tel"] ?? '');
                    $registrationMeta["nia_officer_{$i}_email"] = trim($_POST["nia_officer_{$i}_email"] ?? '');
                }

                // Validate uniqueness (VIN, Plate, Engine, Chassis)
                $existingVin = $db->fetch("SELECT id FROM vehicles WHERE vin = :vin LIMIT 1", ['vin' => $vin]);
                $existingPlate = $db->fetch("SELECT id FROM vehicles WHERE plate_number = :plate LIMIT 1", ['plate' => $plateNumber]);
                $existingEngine = $db->fetch("SELECT id FROM vehicles WHERE engine_number = :engine LIMIT 1", ['engine' => $engineNumber]);
                $existingChassis = $db->fetch("SELECT id FROM vehicles WHERE chassis_number = :chassis LIMIT 1", ['chassis' => $chassisNumber]);

                if (empty($vin) || empty($engineNumber) || empty($chassisNumber) || empty($plateNumber) || !$ownerId) {
                    $error = 'All primary vehicle details and owner selection are required.';
                } elseif ($existingVin) {
                    $error = 'A vehicle with this VIN Number is already registered.';
                } elseif ($existingPlate) {
                    $error = 'A vehicle with this Plate Number is already registered.';
                } elseif ($existingEngine) {
                    $error = 'A vehicle with this Engine Number is already registered.';
                } elseif ($existingChassis) {
                    $error = 'A vehicle with this Chassis Number is already registered.';
                } else {
                    // Process Secure File Upload
                    $imagePath = null;
                    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['vehicle_image']['tmp_name'];
                        $fileName = $_FILES['vehicle_image']['name'];
                        $fileSize = $_FILES['vehicle_image']['size'];
                        $fileType = $_FILES['vehicle_image']['type'];
                        
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));
                        
                        $allowedExtensions = ['jpg', 'jpeg', 'png'];
                        if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                            $uploadFileDir = BASE_PATH . '/public/uploads/vehicles/';
                            
                            if (!is_dir($uploadFileDir)) {
                                mkdir($uploadFileDir, 0777, true);
                            }
                            
                            $dest_path = $uploadFileDir . $newFileName;
                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $imagePath = 'public/uploads/vehicles/' . $newFileName;
                            }
                        } else {
                            $error = 'Invalid image type or size exceeded (Max 5MB, JPG/PNG only).';
                        }
                    }

                    // Process Receipt File Upload
                    $receiptFilePath = null;
                    if (!$error && isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['receipt_file']['tmp_name'];
                        $fileName = $_FILES['receipt_file']['name'];
                        $fileSize = $_FILES['receipt_file']['size'];
                        $fileType = $_FILES['receipt_file']['type'];
                        
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

                    // Process Customs Clearance Document
                    $customsDocPath = null;
                    if (!$error && isset($_FILES['customs_doc']) && $_FILES['customs_doc']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['customs_doc']['tmp_name'];
                        $fileName = $_FILES['customs_doc']['name'];
                        $fileSize = $_FILES['customs_doc']['size'];
                        
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));
                        
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                        if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                            $uploadFileDir = BASE_PATH . '/public/uploads/documents/';
                            if (!is_dir($uploadFileDir)) {
                                mkdir($uploadFileDir, 0777, true);
                            }
                            $dest_path = $uploadFileDir . $newFileName;
                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $customsDocPath = 'public/uploads/documents/' . $newFileName;
                            }
                        } else {
                            $error = 'Invalid Customs document type or size exceeded (Max 5MB, JPG/PNG/PDF only).';
                        }
                    }

                    // Process Police Clearance Document
                    $policeDocPath = null;
                    if (!$error && isset($_FILES['police_doc']) && $_FILES['police_doc']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['police_doc']['tmp_name'];
                        $fileName = $_FILES['police_doc']['name'];
                        $fileSize = $_FILES['police_doc']['size'];
                        
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));
                        
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                        if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                            $uploadFileDir = BASE_PATH . '/public/uploads/documents/';
                            if (!is_dir($uploadFileDir)) {
                                mkdir($uploadFileDir, 0777, true);
                            }
                            $dest_path = $uploadFileDir . $newFileName;
                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $policeDocPath = 'public/uploads/documents/' . $newFileName;
                            }
                        } else {
                            $error = 'Invalid Police clearance document type or size exceeded (Max 5MB, JPG/PNG/PDF only).';
                        }
                    }

                    if (!$error) {
                        // Gather dynamic fields values
                        $customFieldsValues = $registrationMeta;
                        $customFieldsValues['customs_doc_path'] = $customsDocPath;
                        $customFieldsValues['police_doc_path'] = $policeDocPath;
                        foreach ($dynamicFields as $field) {
                            $fieldName = $field['field_name'];
                            $val = $_POST['custom_' . $field['id']] ?? '';
                            
                            // Check if required
                            if ($field['is_required'] && empty($val)) {
                                $error = "Custom field '{$fieldName}' is required.";
                                break;
                            }
                            $customFieldsValues[$fieldName] = $val;
                        }

                        if (!$error) {
                            $pdo = $db->getConnection();
                            try {
                                $pdo->beginTransaction();

                                // 1. Save vehicle
                                $vehicleId = $db->insert('vehicles', [
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
                                    'rfid_tag' => $rfidTag,
                                    'qr_code' => $qrCode,
                                    'image_path' => $imagePath,
                                    'custom_fields' => json_encode($customFieldsValues)
                                ]);

                                // 2. Add initial ownership history
                                $db->insert('ownership_history', [
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

                                // 3. Save payment record
                                $db->insert('payments', [
                                    'vehicle_id' => $vehicleId,
                                    'owner_id' => $ownerId,
                                    'amount' => $paymentAmount,
                                    'payment_method' => $paymentMethod,
                                    'collected_by' => $_SESSION['user_id'],
                                    'receipt_number' => !empty($receiptNumber) ? $receiptNumber : 'REC-' . strtoupper(bin2hex(random_bytes(4))),
                                    'payment_date' => date('Y-m-d'),
                                    'receipt_file' => $receiptFilePath
                                ]);

                                // 4. Create a default pending verification record
                                $db->insert('verification_records', [
                                    'vehicle_id' => $vehicleId,
                                    'verifier_id' => $_SESSION['user_id'],
                                    'verification_type' => 'VEHICLE',
                                    'status' => 'PENDING',
                                    'notes' => 'Awaiting physical and document audit.'
                                ]);

                                $pdo->commit();
                                AuditLog::log($_SESSION['user_id'], "Registered new vehicle (Plate: {$plateNumber}, VIN: {$vin}) linked to Owner ID: {$ownerId}");
                                ActivityLog::log("New vehicle onboarding successful: Plate {$plateNumber}");
                                
                                $success = 'Vehicle registered and initial payment logged successfully.';
                                $registeredVehicleId = $vehicleId;
                            } catch (\Exception $e) {
                                $pdo->rollBack();
                                $error = 'Database transaction failed: ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('vehicles/register', [
            'title'         => 'Register New Vehicle',
            'activePage'    => 'vehicle_reg',
            'owners'        => $owners,
            'dynamicFields' => $dynamicFields,
            'onboardingFee' => $onboardingFee,
            'settingModel'  => $settingModel,
            'paystackEnabled' => !empty($settingModel->get('paystack_public_key')) && !empty($settingModel->get('paystack_secret_key')),
            'error'         => $error,
            'success'       => $success,
            'registeredVehicleId' => $registeredVehicleId ?? null,
            'csrfToken'     => $csrfToken
        ]);
    }

    /**
     * Complete and update optional / skipped registry details.
     * POST /vehicle/update/{id}
     */
    public function update($id = null) {
        if (!$id || !is_numeric($id)) {
            $this->redirect('/vehicle/list');
        }

        $db = Database::getInstance();
        $vehicle = $db->fetch("SELECT * FROM vehicles WHERE id = :id", ['id' => (int)$id]);
        if (!$vehicle) {
            $this->redirect('/vehicle/list');
        }

        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('registration');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $_SESSION['vehicle_update_error'] = 'Invalid CSRF security token.';
                $this->redirect("/vehicle/view/{$id}");
            }

            $color = trim($_POST['color'] ?? '');
            $fuelType = trim($_POST['fuel_type'] ?? '');
            $transmission = trim($_POST['transmission'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $class = trim($_POST['class'] ?? '');
            $rfidTag = trim($_POST['rfid_tag'] ?? '');
            $qrCode = trim($_POST['qr_code'] ?? '');

            // Process image file upload if uploaded
            $imagePath = $vehicle['image_path'];
            if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['vehicle_image']['tmp_name'];
                $fileName = $_FILES['vehicle_image']['name'];
                $fileSize = $_FILES['vehicle_image']['size'];
                
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = BASE_PATH . '/public/uploads/vehicles/';
                    
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }
                    
                    $dest_path = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $imagePath = 'public/uploads/vehicles/' . $newFileName;
                    }
                }
            }

            // Update database fields
            $db->update('vehicles', [
                'color' => $color,
                'fuel_type' => $fuelType,
                'transmission' => $transmission,
                'category' => $category,
                'class' => $class,
                'rfid_tag' => $rfidTag,
                'qr_code' => $qrCode,
                'image_path' => $imagePath
            ], 'id = :id', ['id' => (int)$id]);

            // Existing custom fields array
            $existingCustom = [];
            if (!empty($vehicle['custom_fields'])) {
                $existingCustom = json_decode($vehicle['custom_fields'], true) ?: [];
            }

            $metaKeys = [
                'country_of_origin', 'country_of_manufacture', 'ship_year',
                'importer_name', 'importer_company', 'importer_address', 'importer_email', 'importer_tel',
                'clearing_agent_name', 'clearing_agent_company', 'clearing_agent_address', 'clearing_agent_email', 'clearing_agent_tel',
                'foreign_office_name', 'foreign_office_company', 'foreign_office_address', 'foreign_office_email', 'foreign_office_tel',
                'port_name', 'port_company', 'port_address', 'port_email', 'port_tel',
                'ship_departure_port', 'ship_departure_date', 'ship_landing_date', 'custom_papers_status',
                'purchase_date', 'purchase_amount', 'insurance_cover', 'number_plate_state', 'number_plate_lga',
                'agent_name', 'agent_address', 'agent_tel', 'agent_email', 'tax_number',
                'vehicle_particulars_number', 'vehicle_particulars_purchase_date', 'vehicle_particulars_amount', 'vehicle_particulars_expiry_date',
                'pol_clearance_name', 'pol_clearance_rank', 'pol_clearance_office_address', 'pol_clearance_local_govt', 'pol_clearance_state', 'pol_clearance_tel', 'pol_clearance_email',
                'dl_name', 'dl_rank', 'dl_address', 'dl_tel', 'dl_email'
            ];

            $updatedCustom = $existingCustom;
            foreach ($metaKeys as $key) {
                if (isset($_POST[$key])) {
                    $updatedCustom[$key] = trim($_POST[$key]);
                }
            }

            if (isset($_POST['means_of_identification'])) {
                $updatedCustom['means_of_identification'] = (array)$_POST['means_of_identification'];
            }

            // Officers (1 to 3)
            for ($i = 1; $i <= 3; $i++) {
                $officerPrefixes = ['custom_officer_', 'police_officer_', 'dss_officer_', 'nia_officer_'];
                foreach ($officerPrefixes as $prefix) {
                    foreach (['name', 'rank', 'address', 'tel', 'email'] as $field) {
                        $k = "{$prefix}{$i}_{$field}";
                        if (isset($_POST[$k])) {
                            $updatedCustom[$k] = trim($_POST[$k]);
                        }
                    }
                }
            }

            // Dynamic fields
            $dfModel = new DynamicField();
            $dynamicFields = $dfModel->getFieldsForEntity('vehicle');
            foreach ($dynamicFields as $field) {
                $k = 'custom_' . $field['id'];
                if (isset($_POST[$k])) {
                    $updatedCustom[$field['field_name']] = trim($_POST[$k]);
                }
            }

            // Process Customs document upload in update
            if (isset($_FILES['customs_doc']) && $_FILES['customs_doc']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['customs_doc']['tmp_name'];
                $fileName = $_FILES['customs_doc']['name'];
                $fileSize = $_FILES['customs_doc']['size'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = BASE_PATH . '/public/uploads/documents/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }
                    $dest_path = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $updatedCustom['customs_doc_path'] = 'public/uploads/documents/' . $newFileName;
                    }
                }
            }

            // Process Police document upload in update
            if (isset($_FILES['police_doc']) && $_FILES['police_doc']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['police_doc']['tmp_name'];
                $fileName = $_FILES['police_doc']['name'];
                $fileSize = $_FILES['police_doc']['size'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                if (in_array($fileExtension, $allowedExtensions) && $fileSize < 5000000) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = BASE_PATH . '/public/uploads/documents/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }
                    $dest_path = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $updatedCustom['police_doc_path'] = 'public/uploads/documents/' . $newFileName;
                    }
                }
            }

            // Save back
            $db->update('vehicles', [
                'custom_fields' => json_encode($updatedCustom)
            ], 'id = :id', ['id' => (int)$id]);

            AuditLog::log($_SESSION['user_id'], "Updated vehicle details (ID: {$id}, Plate: {$vehicle['plate_number']})");
            ActivityLog::log("Vehicle details updated successfully for Plate: {$vehicle['plate_number']}");

            $_SESSION['vehicle_update_success'] = 'Vehicle registration details updated successfully.';
        }

        $this->redirect("/vehicle/view/{$id}");
    }

    /**
     * AJAX endpoint: Check if vehicle identifiers already exist in the registry.
     * GET /vehicle/checkDuplicate?field=vin&value=ABC123
     */
    public function checkDuplicate() {
        $db = Database::getInstance();
        $field = $_GET['field'] ?? '';
        $value = strtoupper(trim($_GET['value'] ?? ''));

        if (empty($value)) {
            $this->json(['exists' => false]);
            return;
        }

        $allowedFields = [
            'vin' => 'vin',
            'plate_number' => 'plate_number',
            'engine_number' => 'engine_number',
            'chassis_number' => 'chassis_number',
            'rfid_tag' => 'rfid_tag',
            'qr_code' => 'qr_code'
        ];

        if (!isset($allowedFields[$field])) {
            $this->json(['exists' => false, 'error' => 'Invalid field']);
            return;
        }

        $col = $allowedFields[$field];
        $existing = $db->fetch(
            "SELECT id, plate_number, manufacturer, model FROM vehicles WHERE {$col} = :val LIMIT 1",
            ['val' => $value]
        );

        if ($existing) {
            $this->json([
                'exists' => true,
                'vehicle' => [
                    'plate_number' => $existing['plate_number'],
                    'name' => $existing['manufacturer'] . ' ' . $existing['model']
                ]
            ]);
        } else {
            $this->json(['exists' => false]);
        }
    }

    /**
     * Render vehicle registration report page for Super Admin.
     * Filters: state, lga
     */
    public function report() {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            $this->forbidden();
            return;
        }

        $db = Database::getInstance();
        $state = trim($_GET['state'] ?? '');
        $lga = trim($_GET['lga'] ?? '');
        $sort = trim($_GET['sort'] ?? '');

        list($sql, $params) = $this->buildReportQuery($state, $lga, $sort);
        $vehicles = $db->fetchAll($sql, $params);
        $rows = $this->loadReportRows($vehicles);

        $this->render('admin/vehicles_report', [
            'title' => 'Vehicle Registration Report',
            'activePage' => 'vehicle_report',
            'rows' => $rows,
            'filter_state' => $state,
            'filter_lga' => $lga,
            'filter_sort' => $sort
        ]);
    }

    /**
     * Export vehicle report as CSV or Excel (CSV-format) for Super Admin.
     * GET params: format=csv|xls, state, lga
     */
    public function exportReport() {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            $this->forbidden();
            return;
        }

        $format = strtolower(trim($_GET['format'] ?? 'csv'));
        $state = trim($_GET['state'] ?? '');
        $lga = trim($_GET['lga'] ?? '');
        $sort = trim($_GET['sort'] ?? '');

        $db = Database::getInstance();
        list($sql, $params) = $this->buildReportQuery($state, $lga, $sort);
        $vehicles = $db->fetchAll($sql, $params);
        $rows = $this->loadReportRows($vehicles);
        $headers = $this->getReportHeaders();

        if ($format === 'xlsx' && class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            $filename = 'vehicle_report_' . date('Ymd_His') . '.xlsx';
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($headers, null, 'A1');

            $rowIndex = 2;
            foreach ($rows as $rowData) {
                $sheet->fromArray($this->buildReportRow($rowData), null, 'A' . $rowIndex);
                $rowIndex++;
            }

            foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        $filename = 'vehicle_report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);

        foreach ($rows as $rowData) {
            fputcsv($out, $this->buildReportRow($rowData));
        }

        fclose($out);
        exit;
    }

    /**
     * Printable report (HTML) which can be saved as PDF via browser print.
     */
    public function printReport() {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            $this->forbidden();
            return;
        }

        $state = trim($_GET['state'] ?? '');
        $lga = trim($_GET['lga'] ?? '');
        $sort = trim($_GET['sort'] ?? '');

        $db = Database::getInstance();
        list($sql, $params) = $this->buildReportQuery($state, $lga, $sort);
        $vehicles = $db->fetchAll($sql, $params);
        $rows = $this->loadReportRows($vehicles);

        $this->render('admin/vehicles_report_print', [
            'title' => 'Vehicle Registration Report (Printable)',
            'rows' => $rows,
            'filter_state' => $state,
            'filter_lga' => $lga,
            'filter_sort' => $sort
        ]);
    }

    /**
     * Server-side PDF generation using Dompdf (if available).
     */
    public function pdfReport() {
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            $this->forbidden();
            return;
        }

        $db = Database::getInstance();
        $state = trim($_GET['state'] ?? '');
        $lga = trim($_GET['lga'] ?? '');
        $sort = trim($_GET['sort'] ?? '');

        list($sql, $params) = $this->buildReportQuery($state, $lga, $sort);
        $vehicles = $db->fetchAll($sql, $params);
        $rows = $this->loadReportRows($vehicles);

        // Render printable HTML view into a string
        $viewFile = BASE_PATH . '/app/views/admin/vehicles_report_print.php';
        if (!file_exists($viewFile)) {
            $this->json(['error' => 'Printable view missing'], 500);
        }

        ob_start();
        // Make the same variables available to the view
        $rows_for_view = $rows; $filter_state = $state; $filter_lga = $lga; // used in view
        include $viewFile;
        $html = ob_get_clean();

        if (!class_exists('\\Dompdf\\Dompdf')) {
            // Dompdf not installed
            header('Content-Type: text/html');
            echo "<h3>Dompdf is not installed.</h3><p>Run <code>composer require dompdf/dompdf</code> in the project root.</p>";
            return;
        }

        $options = new \Dompdf\Options();
        $options->setIsRemoteEnabled(true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'vehicle_report_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]);
        exit;
    }

    private function loadReportRows(array $vehicles): array {
        $db = Database::getInstance();
        $rows = [];

        foreach ($vehicles as $v) {
            $vid = $v['id'];
            $current = $db->fetch(
                "SELECT oh.*, o.full_name, o.phone FROM ownership_history oh LEFT JOIN owners o ON o.id = oh.owner_id WHERE oh.vehicle_id = :vid ORDER BY oh.id DESC LIMIT 1",
                ['vid' => $vid]
            );
            $previous = $db->fetch(
                "SELECT oh.*, o.full_name FROM ownership_history oh LEFT JOIN owners o ON o.id = oh.owner_id WHERE oh.vehicle_id = :vid ORDER BY oh.id DESC LIMIT 1 OFFSET 1",
                ['vid' => $vid]
            );
            $pay = $db->fetch("SELECT SUM(amount) as total_payments FROM payments WHERE vehicle_id = :vid", ['vid' => $vid]);

            $custom = [];
            if (!empty($v['custom_fields'])) {
                $custom = json_decode($v['custom_fields'], true) ?: [];
            }

            $receiptUrl = '';
            $receiptRow = $db->fetch("SELECT receipt_file FROM payments WHERE vehicle_id = :vid ORDER BY id DESC LIMIT 1", ['vid' => $vid]);
            if (!empty($receiptRow['receipt_file'])) {
                $receiptUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' . ltrim($receiptRow['receipt_file'], '/') : $receiptRow['receipt_file'];
            }

            $imageUrl = '';
            if (!empty($v['image_path'])) {
                $imageUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' . ltrim($v['image_path'], '/') : $v['image_path'];
            }

            $rows[] = [
                'vehicle' => $v,
                'current_owner' => $current,
                'previous_owner' => $previous,
                'payments' => $pay,
                'custom' => $custom,
                'receipt_url' => $receiptUrl,
                'image_url' => $imageUrl,
                'summary' => $this->formatCustomSummary($custom)
            ];
        }

        return $rows;
    }

    private function parseSelectedStates(string $state): array {
        $values = preg_split('/[\,;|]+/', $state, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_filter(array_map('trim', $values), fn($value) => $value !== ''));
    }

    private function buildReportQuery(string $state, string $lga, string $sort): array {
        $sql = "SELECT v.* FROM vehicles v WHERE 1";
        $params = [];

        if (!empty($state)) {
            $states = $this->parseSelectedStates($state);
            if (count($states) === 1) {
                $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(v.custom_fields, '$.number_plate_state')) = :state";
                $params['state'] = $states[0];
            } elseif (count($states) > 1) {
                $placeholders = [];
                foreach ($states as $index => $value) {
                    $key = 'state_' . $index;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $value;
                }
                $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(v.custom_fields, '$.number_plate_state')) IN (" . implode(', ', $placeholders) . ")";
            }
        }

        if (!empty($lga)) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(v.custom_fields, '$.number_plate_lga')) = :lga";
            $params['lga'] = $lga;
        }

        if ($sort === 'state') {
            $sql .= " ORDER BY JSON_UNQUOTE(JSON_EXTRACT(v.custom_fields, '$.number_plate_state')) ASC, JSON_UNQUOTE(JSON_EXTRACT(v.custom_fields, '$.number_plate_lga')) ASC, v.plate_number ASC";
        } elseif ($sort === 'category') {
            $sql .= " ORDER BY v.category ASC, v.plate_number ASC";
        } else {
            $sql .= " ORDER BY v.created_at DESC";
        }

        return [$sql, $params];
    }

    private function getReportHeaders(): array {
        return [
            'Vehicle ID',
            'VIN',
            'Plate Number',
            'Engine Number',
            'Chassis Number',
            'Manufacturer',
            'Model',
            'Year',
            'Color',
            'Fuel Type',
            'Transmission',
            'Category',
            'Class',
            'Current Owner',
            'Current Owner Phone',
            'Previous Owner',
            'Total Payments',
            'Purchase Date',
            'Purchase Amount',
            'Importer Name',
            'Importer Company',
            'Port Name',
            'Port Company',
            'State',
            'LGA',
            'Country of Origin',
            'Country of Manufacture',
            'Ship Year',
            'Insurance Cover',
            'Agent Name',
            'Agent Tel',
            'Agent Email',
            'Receipt URL',
            'Image URL',
            'Details Summary',
            'Custom Fields JSON'
        ];
    }

    private function buildReportRow(array $rowData): array {
        $v = $rowData['vehicle'];
        $c = $rowData['custom'];
        return [
            $v['id'] ?? '',
            $v['vin'] ?? '',
            $v['plate_number'] ?? '',
            $v['engine_number'] ?? '',
            $v['chassis_number'] ?? '',
            $v['manufacturer'] ?? '',
            $v['model'] ?? '',
            $v['year'] ?? '',
            $v['color'] ?? '',
            $v['fuel_type'] ?? '',
            $v['transmission'] ?? '',
            $v['category'] ?? '',
            $v['class'] ?? '',
            $rowData['current_owner']['full_name'] ?? '',
            $rowData['current_owner']['phone'] ?? '',
            $rowData['previous_owner']['full_name'] ?? '',
            $rowData['payments']['total_payments'] ?? 0,
            $this->formatCustomFieldValue($c['purchase_date'] ?? ''),
            $this->formatCustomFieldValue($c['purchase_amount'] ?? ''),
            $this->formatCustomFieldValue($c['importer_name'] ?? ''),
            $this->formatCustomFieldValue($c['importer_company'] ?? ''),
            $this->formatCustomFieldValue($c['port_name'] ?? ''),
            $this->formatCustomFieldValue($c['port_company'] ?? ''),
            $this->formatCustomFieldValue($c['number_plate_state'] ?? ''),
            $this->formatCustomFieldValue($c['number_plate_lga'] ?? ''),
            $this->formatCustomFieldValue($c['country_of_origin'] ?? ''),
            $this->formatCustomFieldValue($c['country_of_manufacture'] ?? ''),
            $this->formatCustomFieldValue($c['ship_year'] ?? ''),
            $this->formatCustomFieldValue($c['insurance_cover'] ?? ''),
            $this->formatCustomFieldValue($c['agent_name'] ?? ''),
            $this->formatCustomFieldValue($c['agent_tel'] ?? ''),
            $this->formatCustomFieldValue($c['agent_email'] ?? ''),
            $rowData['receipt_url'] ?? '',
            $rowData['image_url'] ?? '',
            $rowData['summary'] ?? '',
            json_encode($c, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ];
    }

    private function formatCustomSummary(array $custom): string {
        $mapping = [
            'country_of_origin' => 'Country of Origin',
            'country_of_manufacture' => 'Country of Manufacture',
            'ship_year' => 'Ship Year',
            'importer_name' => 'Importer Name',
            'importer_company' => 'Importer Company',
            'importer_address' => 'Importer Address',
            'importer_email' => 'Importer Email',
            'importer_tel' => 'Importer Phone',
            'port_name' => 'Port Name',
            'port_company' => 'Port Company',
            'port_address' => 'Port Address',
            'port_email' => 'Port Email',
            'port_tel' => 'Port Phone',
            'ship_departure_port' => 'Departure Port',
            'ship_departure_date' => 'Departure Date',
            'ship_landing_date' => 'Landing Date',
            'custom_papers_status' => 'Custom Papers Status',
            'purchase_date' => 'Purchase Date',
            'purchase_amount' => 'Purchase Amount',
            'means_of_identification' => 'Means of ID',
            'insurance_cover' => 'Insurance Cover',
            'number_plate_state' => 'Plate State',
            'number_plate_lga' => 'Plate LGA',
            'agent_name' => 'Agent Name',
            'agent_address' => 'Agent Address',
            'agent_tel' => 'Agent Phone',
            'agent_email' => 'Agent Email',
            'tax_number' => 'Tax Number',
            'vehicle_particulars_number' => 'Vehicle Particulars No.',
            'vehicle_particulars_purchase_date' => 'Vehicle Particulars Purchase Date',
            'vehicle_particulars_amount' => 'Vehicle Particulars Amount',
            'vehicle_particulars_expiry_date' => 'Vehicle Particulars Expiry Date',
            'pol_clearance_name' => 'POL Clearance Name',
            'pol_clearance_rank' => 'POL Clearance Rank',
            'pol_clearance_office_address' => 'POL Clearance Office',
            'pol_clearance_local_govt' => 'POL Clearance LGA',
            'pol_clearance_state' => 'POL Clearance State',
            'pol_clearance_tel' => 'POL Clearance Phone',
            'pol_clearance_email' => 'POL Clearance Email',
            'dl_name' => 'DL Name',
            'dl_rank' => 'DL Rank',
            'dl_address' => 'DL Address',
            'dl_tel' => 'DL Phone',
            'dl_email' => 'DL Email'
        ];

        $summary = [];
        foreach ($mapping as $key => $label) {
            if (!isset($custom[$key]) || $custom[$key] === '' || $custom[$key] === null) {
                continue;
            }
            $summary[] = $label . ': ' . $this->formatCustomFieldValue($custom[$key]);
        }

        foreach ($custom as $key => $value) {
            if (isset($mapping[$key]) || $value === '' || $value === null) {
                continue;
            }
            $label = ucwords(str_replace(['_', '-'], [' ', ' '], $key));
            $summary[] = $label . ': ' . $this->formatCustomFieldValue($value);
        }

        return implode(' | ', $summary);
    }

    private function formatCustomFieldValue($value): string {
        if (is_array($value)) {
            return implode(', ', array_map('trim', $value));
        }
        return trim((string)$value);
    }
}
