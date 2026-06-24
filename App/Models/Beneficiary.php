<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Beneficiary extends Model {
    protected $table = 'beneficiaries';

    public function findByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, ['uid' => $userId]);
    }

    public function createProfile($userId, $data) {
        $payload = array_merge($data, ['user_id' => $userId]);
        return $this->create($payload);
    }

    public function updateProfile($id, $data) {
        return $this->update($id, $data);
    }

    public function suspend($id, $suspended = true) {
        return $this->update($id, ['is_suspended' => $suspended ? 1 : 0]);
    }

    public function allActive() {
        $sql = "SELECT b.*, u.email, u.first_name, u.phone FROM {$this->table} b JOIN users u ON u.id = b.user_id WHERE b.is_suspended = 0 ORDER BY b.id DESC";
        return $this->db->fetchAll($sql);
    }

    public function getEarnings($beneficiaryId) {
        $db = Database::getInstance();
        $sql = "SELECT SUM(amount) as total FROM beneficiary_earnings WHERE beneficiary_id = :bid";
        $res = $db->fetch($sql, ['bid' => $beneficiaryId]);
        return $res ? (float)$res['total'] : 0.0;
    }

    public function addEarning($beneficiaryId, $amount, $reference = null, $note = null) {
        $db = Database::getInstance();
        return $db->insert('beneficiary_earnings', [
            'beneficiary_id' => $beneficiaryId,
            'amount' => $amount,
            'reference' => $reference,
            'note' => $note
        ]);
    }
}
