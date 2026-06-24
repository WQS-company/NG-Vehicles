<?php
namespace App\Models;

use Core\Model;

class Owner extends Model {
    protected $table = 'owners';

    public function findByPhone($phone) {
        $sql = "SELECT * FROM {$this->table} WHERE phone = :phone LIMIT 1";
        return $this->db->fetch($sql, ['phone' => $phone]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        return $this->db->fetch($sql, ['email' => $email]);
    }
}
