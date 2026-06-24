<?php
namespace App\Models;

use Core\Model;

class User extends Model {
    protected $table = 'users';

    public function findByEmailOrPhone($input) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :input OR phone = :input LIMIT 1";
        return $this->db->fetch($sql, ['input' => $input]);
    }

    public function createAdmin($email, $phone, $password, $role) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        return $this->create([
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $hash,
            'role' => $role,
            'is_active' => 1
        ]);
    }

    public function updateStatus($id, $isActive) {
        return $this->update($id, ['is_active' => $isActive ? 1 : 0]);
    }
}
