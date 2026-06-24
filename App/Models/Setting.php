<?php
namespace App\Models;

use Core\Model;

class Setting extends Model {
    protected $table = 'settings';

    public function get($key, $default = null) {
        $sql = "SELECT `value` FROM {$this->table} WHERE `key` = :key LIMIT 1";
        $res = $this->db->fetch($sql, ['key' => $key]);
        return $res ? $res['value'] : $default;
    }

    public function set($key, $value) {
        $sql = "INSERT INTO {$this->table} (`key`, `value`) VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE `value` = :value_update, updated_at = CURRENT_TIMESTAMP";
        return $this->db->query($sql, [
            'key' => $key,
            'value' => $value,
            'value_update' => $value
        ]);
    }

    public function getAllSettings() {
        $settings = $this->all();
        $formatted = [];
        foreach ($settings as $s) {
            $formatted[$s['key']] = $s['value'];
        }
        return $formatted;
    }
}
