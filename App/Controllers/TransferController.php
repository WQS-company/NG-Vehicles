<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Vehicle;
use App\Models\Owner;
use App\Models\Transfer;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class TransferController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('registration');
        }
    }

    public function create() {
        $db = Database::getInstance();
        $vehicleModel = new Vehicle();
        $ownerModel = new Owner();
        $transferModel = new Transfer();

        $error = null;
        $success = null;

        $vehicles = $vehicleModel->all();
        $owners = $ownerModel->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
                $buyerId = (int)($_POST['buyer_id'] ?? 0);
                
                $salePrice = (float)($_POST['sale_price'] ?? 0);
                $marketName = trim($_POST['market_name'] ?? '');
                $witnessName = trim($_POST['witness_name'] ?? '');
                $middlemanName = trim($_POST['middleman_name'] ?? '');
                
                // Get current owner (seller) of vehicle
                $currentOwner = $transferModel->getCurrentOwner($vehicleId);

                if (!$vehicleId || !$buyerId) {
                    $error = 'Please select a vehicle and a target buyer.';
                } elseif (!$currentOwner) {
                    $error = 'This vehicle has no registered owner in the system.';
                } elseif ($currentOwner['owner_id'] == $buyerId) {
                    $error = 'Cannot transfer ownership to the current owner.';
                } else {
                    // Start transaction
                    try {
                        $transferData = [
                            'transfer_date' => date('Y-m-d'),
                            'sale_price' => $salePrice,
                            'market_name' => $marketName,
                            'witness_name' => $witnessName,
                            'middleman_name' => $middlemanName,
                            'approved_by' => $_SESSION['user_id'],
                            'seller_name' => $currentOwner['full_name'],
                            'seller_phone' => $currentOwner['phone']
                        ];
                        
                        $transferModel->transferOwnership($vehicleId, $currentOwner['owner_id'], $buyerId, $transferData);

                        // Audit & Activity Logs
                        $vehicleDetails = $vehicleModel->find($vehicleId);
                        AuditLog::log($_SESSION['user_id'], "Transferred Vehicle (Plate: {$vehicleDetails['plate_number']}) from ID {$currentOwner['owner_id']} to ID {$buyerId}");
                        ActivityLog::log("Ownership transfer approved: Vehicle {$vehicleDetails['plate_number']}");

                        $success = 'Ownership transfer successfully executed and recorded permanently.';
                    } catch (\Exception $e) {
                        $error = 'Transfer failed: ' . $e->getMessage();
                    }
                }
            }
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('transfers/create', [
            'title' => 'Process Ownership Transfer',
            'activePage' => 'transfer',
            'vehicles' => $vehicles,
            'owners' => $owners,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $csrfToken
        ]);
    }

    public function getCurrentOwnerApi($vehicleId) {
        Auth::requireAuth();
        $transferModel = new Transfer();
        $currentOwner = $transferModel->getCurrentOwner((int)$vehicleId);
        
        if ($currentOwner) {
            $this->json([
                'success' => true,
                'owner' => $currentOwner
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'No registered owner found for this vehicle.'
            ]);
        }
    }
}
