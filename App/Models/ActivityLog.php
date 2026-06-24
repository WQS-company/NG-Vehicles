<?php
namespace App\Models;

use Core\Model;

class ActivityLog extends Model {
    protected $table = 'activity_logs';

    public static function log($description, $userId = null) {
        $db = \Core\Database::getInstance();
        return $db->insert('activity_logs', [
            'description' => $description,
            'performed_by' => $userId ?? $_SESSION['user_id'] ?? null
        ]);
    }

    public function getLogs() {
        $sql = "SELECT act.*, u.email 
                FROM {$this->table} act 
                LEFT JOIN users u ON act.performed_by = u.id 
                ORDER BY act.performed_at DESC";
        return $this->db->fetchAll($sql);
    }
}
