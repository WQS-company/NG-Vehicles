<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;

class HomeController extends Controller {
    public function index() {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        } else {
            // Fetch actual database stats for the landing page
            try {
                $db = \Core\Database::getInstance();
                
                // 1. Total vehicles
                $vehiclesCount = (int)$db->fetch("SELECT COUNT(*) as count FROM vehicles")['count'];
                
                // 2. Total verifications
                $verificationsCount = (int)$db->fetch("SELECT COUNT(*) as count FROM verification_records")['count'];
                
                // 3. States Synchronized (from vehicles.custom_fields)
                $vehicles = $db->fetchAll("SELECT custom_fields FROM vehicles WHERE custom_fields IS NOT NULL");
                $states = [];
                foreach ($vehicles as $v) {
                    $cf = json_decode($v['custom_fields'], true);
                    if (!empty($cf['number_plate_state'])) {
                        $states[trim($cf['number_plate_state'])] = true;
                    }
                }
                $statesCount = count($states);
                
                // 4. Trace Accuracy
                $approvedCount = (int)$db->fetch("SELECT COUNT(*) as count FROM verification_records WHERE status = 'APPROVED'")['count'];
                $accuracy = $verificationsCount > 0 ? round(($approvedCount / $verificationsCount) * 100, 2) : 100.00;
                
            } catch (\Exception $e) {
                $vehiclesCount = 0;
                $verificationsCount = 0;
                $statesCount = 0;
                $accuracy = 100.00;
            }

            $this->render('home', [
                'title' => 'National Vehicle Ownership & Traceability System',
                'vehiclesCount' => $vehiclesCount,
                'verificationsCount' => $verificationsCount,
                'statesCount' => $statesCount,
                'accuracy' => $accuracy
            ]);
        }
    }

    public function privacy() {
        $settingModel = new \App\Models\Setting();
        $this->render('home/privacy', [
            'title' => 'Privacy Policy',
            'privacy_policy' => $settingModel->get('privacy_policy', '')
        ]);
    }

    public function terms() {
        $settingModel = new \App\Models\Setting();
        $this->render('home/terms', [
            'title' => 'Terms & Conditions',
            'terms_conditions' => $settingModel->get('terms_conditions', '')
        ]);
    }
}
