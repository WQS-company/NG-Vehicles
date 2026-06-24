<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Vehicle;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Verification;
use App\Models\ActivityLog;

class ReportController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('reports');
        }
    }

    public function manage() {
        $db = Database::getInstance();
        $vehicles = $db->fetchAll(
            "SELECT v.id, v.vin, v.plate_number, v.engine_number, v.chassis_number, v.manufacturer, v.model, v.year, v.color, v.category, v.class, o.state, o.lga, DATE_FORMAT(v.created_at, '%Y-%m-%d') as created_at
             FROM vehicles v
             LEFT JOIN (
                 SELECT oh.vehicle_id, oh.owner_id
                 FROM ownership_history oh
                 INNER JOIN (
                     SELECT vehicle_id, MAX(id) AS max_id
                     FROM ownership_history
                     GROUP BY vehicle_id
                 ) latest ON latest.vehicle_id = oh.vehicle_id AND latest.max_id = oh.id
             ) oh ON oh.vehicle_id = v.id
             LEFT JOIN owners o ON o.id = oh.owner_id
             ORDER BY v.created_at DESC"
        );

        $states = $db->fetchAll(
            "SELECT DISTINCT state FROM owners WHERE state IS NOT NULL AND state <> '' ORDER BY state"
        );

        $categories = $db->fetchAll(
            "SELECT DISTINCT category FROM vehicles WHERE category IS NOT NULL AND category <> '' ORDER BY category"
        );

        $classes = $db->fetchAll(
            "SELECT DISTINCT class FROM vehicles WHERE class IS NOT NULL AND class <> '' ORDER BY class"
        );

        $this->render('reports/manage', [
            'title' => 'Registry Reporting & Exports Center',
            'activePage' => 'reports',
            'vehicles' => $vehicles,
            'states' => $states,
            'categories' => $categories,
            'classes' => $classes,
            'totalVehicles' => count($vehicles)
        ]);
    }

    // Export endpoint handling CSV generation
    public function export($type) {
        $db = Database::getInstance();
        $filename = "nvots_" . $type . "_" . date('Ymd_His') . ".csv";

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen("php://output", "w");

        if ($type === 'vehicles') {
            fputcsv($output, ['ID', 'VIN', 'Plate Number', 'Engine Number', 'Chassis Number', 'Make', 'Model', 'Year', 'Color', 'Class', 'Created At']);
            $data = $db->fetchAll("SELECT id, vin, plate_number, engine_number, chassis_number, manufacturer, model, year, color, class, created_at FROM vehicles");
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        } elseif ($type === 'owners') {
            fputcsv($output, ['ID', 'Full Name', 'Phone', 'Email', 'Gender', 'Nationality', 'NIN', 'State', 'LGA', 'Created At']);
            $data = $db->fetchAll("SELECT id, full_name, phone, email, gender, nationality, nin, state, lga, created_at FROM owners");
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        } elseif ($type === 'revenue') {
            fputcsv($output, ['Payment ID', 'Receipt No', 'Plate Number', 'Owner Name', 'Amount (NGN)', 'Method', 'Collected By', 'Receipt File', 'Date']);
            $data = $db->fetchAll("
                SELECT p.id, p.receipt_number, v.plate_number, o.full_name, p.amount, p.payment_method, u.email, p.receipt_file, p.payment_date 
                FROM payments p
                JOIN vehicles v ON p.vehicle_id = v.id
                JOIN owners o ON p.owner_id = o.id
                JOIN users u ON p.collected_by = u.id
            ");
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        } elseif ($type === 'verifications') {
            fputcsv($output, ['Audit ID', 'Plate Number', 'Make', 'Model', 'Type', 'Status', 'Auditor Email', 'Audit Date']);
            $data = $db->fetchAll("
                SELECT vr.id, v.plate_number, v.manufacturer, v.model, vr.verification_type, vr.status, u.email, vr.verified_at 
                FROM verification_records vr
                JOIN vehicles v ON vr.vehicle_id = v.id
                LEFT JOIN users u ON vr.verifier_id = u.id
            ");
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        } elseif ($type === 'activity') {
            fputcsv($output, ['Log ID', 'Activity Description', 'Performed By', 'Timestamp']);
            $data = $db->fetchAll("
                SELECT act.id, act.description, u.email, act.performed_at 
                FROM activity_logs act
                LEFT JOIN users u ON act.performed_by = u.id
            ");
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}
