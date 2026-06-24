<?php
namespace App\Models;

use Core\Model;

class AuditLog extends Model {
    protected $table = 'audit_logs';

    // Immutable creation
    public static function log($userId, $action) {
        $db = \Core\Database::getInstance();
        return $db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    public function getLogs() {
        $sql = "SELECT al.*, u.email, u.role 
                FROM {$this->table} al 
                JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC";
        return $this->db->fetchAll($sql);
    }
}
