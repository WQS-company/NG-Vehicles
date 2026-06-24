<?php
namespace App\Models;

use Core\Model;

class Payment extends Model {
    protected $table = 'payments';

    public function getPaymentsForVehicle($vehicleId) {
        $sql = "SELECT p.*, o.full_name as owner_name, u.email as collected_by_email 
                FROM {$this->table} p 
                JOIN owners o ON p.owner_id = o.id 
                JOIN users u ON p.collected_by = u.id 
                WHERE p.vehicle_id = :vehicle_id 
                ORDER BY p.payment_date DESC";
        return $this->db->fetchAll($sql, ['vehicle_id' => $vehicleId]);
    }

    public function getRevenueStats() {
        $sql = "SELECT 
                    SUM(CASE WHEN payment_date = CURDATE() THEN amount ELSE 0 END) as daily_revenue,
                    SUM(CASE WHEN payment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN amount ELSE 0 END) as weekly_revenue,
                    SUM(CASE WHEN payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as monthly_revenue,
                    SUM(amount) as total_revenue 
                FROM {$this->table}";
        return $this->db->fetch($sql);
    }

    public function filterPayments($vehicleId = null, $ownerId = null, $startDate = null, $endDate = null) {
        $sql = "SELECT p.*, v.plate_number, v.vin, o.full_name as owner_name, u.email as collected_by_email 
                FROM {$this->table} p 
                JOIN vehicles v ON p.vehicle_id = v.id 
                JOIN owners o ON p.owner_id = o.id 
                JOIN users u ON p.collected_by = u.id 
                WHERE 1=1";
        $params = [];

        if ($vehicleId) {
            $sql .= " AND p.vehicle_id = :vehicle_id";
            $params['vehicle_id'] = $vehicleId;
        }
        if ($ownerId) {
            $sql .= " AND p.owner_id = :owner_id";
            $params['owner_id'] = $ownerId;
        }
        if ($startDate) {
            $sql .= " AND p.payment_date >= :start_date";
            $params['start_date'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND p.payment_date <= :end_date";
            $params['end_date'] = $endDate;
        }

        $sql .= " ORDER BY p.payment_date DESC";
        return $this->db->fetchAll($sql, $params);
    }
}
