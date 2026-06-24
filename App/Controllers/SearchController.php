<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Vehicle;
use App\Models\Transfer;
use App\Models\Payment;
use App\Models\Verification;

class SearchController extends Controller {

    public function __construct() {
        Auth::requireAuth();
    }

    public function index() {
        $db = Database::getInstance();
        $vehicleModel = new Vehicle();
        $transferModel = new Transfer();
        $paymentModel = new Payment();
        $verificationModel = new Verification();

        $query = trim($_GET['q'] ?? '');
        $results = [];
        $vehicleData = null;
        $ownershipHistory = [];
        $paymentRecords = [];
        $verificationRecords = [];

        if (!empty($query)) {
            // Search vehicles list
            $results = $vehicleModel->search($query);
            
            // If query is an exact match (e.g. Plate/VIN), load full trace details
            if (count($results) === 1) {
                $vehicleId = $results[0]['id'];
                $vehicleData = $vehicleModel->find($vehicleId);
                
                // Get ownership timeline
                $ownershipHistory = $transferModel->getHistoryForVehicle($vehicleId);
                
                // Get payment logs
                $paymentRecords = $paymentModel->getPaymentsForVehicle($vehicleId);
                
                // Get verification logs
                $verificationRecords = $verificationModel->getRecordsForVehicle($vehicleId);
            }
        }

        $this->render('search/index', [
            'title' => 'Global Registry Search & Trace',
            'activePage' => 'search',
            'query' => $query,
            'results' => $results,
            'vehicle' => $vehicleData,
            'history' => $ownershipHistory,
            'payments' => $paymentRecords,
            'verifications' => $verificationRecords
        ]);
    }

    // Direct trace API for deep lookup
    public function trace($vehicleId) {
        $vehicleModel = new Vehicle();
        $transferModel = new Transfer();
        $paymentModel = new Payment();
        $verificationModel = new Verification();

        $vehicleId = (int)$vehicleId;
        $vehicle = $vehicleModel->find($vehicleId);
        
        if (!$vehicle) {
            $this->json(['success' => false, 'message' => 'Vehicle not found']);
        }

        $this->json([
            'success' => true,
            'vehicle' => $vehicle,
            'history' => $transferModel->getHistoryForVehicle($vehicleId),
            'payments' => $paymentModel->getPaymentsForVehicle($vehicleId),
            'verifications' => $verificationModel->getRecordsForVehicle($vehicleId)
        ]);
    }
}
