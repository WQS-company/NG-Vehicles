<?php
namespace App\Models;

use Core\Model;

class Verification extends Model {
    protected $table = 'verification_records';

    public function getRecordsForVehicle($vehicleId) {
        $sql = "SELECT vr.*, u.email as verifier_email 
                FROM {$this->table} vr 
                JOIN users u ON vr.verifier_id = u.id 
                WHERE vr.vehicle_id = :vehicle_id 
                ORDER BY vr.created_at DESC";
        return $this->db->fetchAll($sql, ['vehicle_id' => $vehicleId]);
    }

    public function getPendingCount() {
        return $this->db->fetch("SELECT COUNT(*) as cnt FROM {$this->table} WHERE status = 'PENDING'")['cnt'];
    }

    public function getApprovedCount() {
        return $this->db->fetch("SELECT COUNT(*) as cnt FROM {$this->table} WHERE status = 'APPROVED'")['cnt'];
    }
}
