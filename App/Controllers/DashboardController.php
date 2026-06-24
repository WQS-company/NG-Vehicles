<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;

class DashboardController extends Controller {

    public function __construct() {
        Auth::requireAuth();
    }

    public function index() {
        if (Auth::role() === ROLE_BENEFICIARY) {
            $this->redirect('/beneficiary/dashboard');
        }
        $db = Database::getInstance();

        // 1. Core counters
        $totalVehicles = $db->fetch("SELECT COUNT(*) as cnt FROM vehicles")['cnt'] ?? 0;
        $totalOwners = $db->fetch("SELECT COUNT(*) as cnt FROM owners")['cnt'] ?? 0;
        $totalTransfers = $db->fetch("SELECT COUNT(*) as cnt FROM ownership_transfers")['cnt'] ?? 0;
        $totalRevenue = $db->fetch("SELECT SUM(amount) as sum FROM payments")['sum'] ?? 0;
        
        $pendingVerifications = $db->fetch("SELECT COUNT(*) as cnt FROM verification_records WHERE status = 'PENDING'")['cnt'] ?? 0;
        $approvedVerifications = $db->fetch("SELECT COUNT(*) as cnt FROM verification_records WHERE status = 'APPROVED'")['cnt'] ?? 0;
        
        // Active vs Suspended (Using user active field status / sample values)
        $activeVehicles = $totalVehicles; // For standard registry
        $suspendedVehicles = 0; // Configured manually in setting / dynamic status

        // 2. Recent activities (audit and activity logs)
        $recentActivities = $db->fetchAll("
            SELECT act.*, u.email 
            FROM activity_logs act 
            LEFT JOIN users u ON act.performed_by = u.id 
            ORDER BY act.performed_at DESC LIMIT 5
        ");

        // 3. Recent Registrations
        $recentRegistrations = $db->fetchAll("
            SELECT v.*, oh.purchase_date, o.full_name as current_owner 
            FROM vehicles v 
            LEFT JOIN ownership_history oh ON oh.vehicle_id = v.id 
            LEFT JOIN owners o ON oh.owner_id = o.id 
            ORDER BY v.created_at DESC LIMIT 5
        ");

        // 4. Recent Transfers
        $recentTransfers = $db->fetchAll("
            SELECT ot.*, v.plate_number, v.vin, s.full_name as seller_name, b.full_name as buyer_name 
            FROM ownership_transfers ot 
            JOIN vehicles v ON ot.vehicle_id = v.id 
            JOIN owners s ON ot.seller_id = s.id 
            JOIN owners b ON ot.buyer_id = b.id 
            ORDER BY ot.created_at DESC LIMIT 5
        ");

        // 5. Monthly Registration Data for Chart.js
        $monthlyRegData = $db->fetchAll("
            SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count 
            FROM vehicles 
            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
            ORDER BY created_at ASC LIMIT 12
        ");

        // Render Dashboard View
        $this->render('dashboard', [
            'title' => 'Federal Command Center Dashboard',
            'activePage' => 'dashboard',
            'stats' => [
                'vehicles' => $totalVehicles,
                'owners' => $totalOwners,
                'transfers' => $totalTransfers,
                'revenue' => $totalRevenue,
                'pending_ver' => $pendingVerifications,
                'approved_ver' => $approvedVerifications,
                'active_vehicles' => $activeVehicles,
                'suspended_vehicles' => $suspendedVehicles
            ],
            'recentActivities' => $recentActivities,
            'recentRegistrations' => $recentRegistrations,
            'recentTransfers' => $recentTransfers,
            'chartData' => [
                'monthlyReg' => $monthlyRegData
            ]
        ]);
    }
}
