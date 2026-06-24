<?php
namespace App\Models;

use Core\Model;

class Vehicle extends Model {
    protected $table = 'vehicles';

    public function findByVin($vin) {
        $sql = "SELECT * FROM {$this->table} WHERE vin = :vin LIMIT 1";
        return $this->db->fetch($sql, ['vin' => $vin]);
    }

    public function findByPlateNumber($plateNumber) {
        $sql = "SELECT * FROM {$this->table} WHERE plate_number = :plate_number LIMIT 1";
        return $this->db->fetch($sql, ['plate_number' => $plateNumber]);
    }

    public function findByRfidTag($tag) {
        $sql = "SELECT * FROM {$this->table} WHERE rfid_tag = :tag LIMIT 1";
        return $this->db->fetch($sql, ['tag' => $tag]);
    }

    public function findByQrCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE qr_code = :code LIMIT 1";
        return $this->db->fetch($sql, ['code' => $code]);
    }

    public function search($query) {
        $sql = "SELECT v.*, o.full_name as current_owner_name, o.phone as current_owner_phone 
                FROM {$this->table} v 
                LEFT JOIN ownership_history oh ON oh.vehicle_id = v.id 
                LEFT JOIN owners o ON o.id = oh.owner_id
                WHERE v.vin LIKE :q1 
                   OR v.engine_number LIKE :q2 
                   OR v.chassis_number LIKE :q3 
                   OR v.plate_number LIKE :q4 
                   OR v.manufacturer LIKE :q5 
                   OR v.model LIKE :q6 
                   OR v.rfid_tag LIKE :q7 
                   OR v.qr_code LIKE :q8 
                   OR o.full_name LIKE :q9 
                   OR o.phone LIKE :q10 
                   OR o.nin LIKE :q11 
                   OR o.bvn LIKE :q12
                GROUP BY v.id";
        $val = "%{$query}%";
        return $this->db->fetchAll($sql, [
            'q1' => $val,
            'q2' => $val,
            'q3' => $val,
            'q4' => $val,
            'q5' => $val,
            'q6' => $val,
            'q7' => $val,
            'q8' => $val,
            'q9' => $val,
            'q10' => $val,
            'q11' => $val,
            'q12' => $val
        ]);
    }
}
