<?php
namespace App\Models;

use Core\Model;

class Notification extends Model {
    protected $table = 'notifications';

    public function getNotificationsForUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE recipient_id = :recipient_id ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, ['recipient_id' => $userId]);
    }

    public function markAsRead($id) {
        return $this->update($id, ['is_read' => 1]);
    }

    // Mock SMS/Email dispatching & database logging
    public function send($recipientId, $type, $message) {
        // Here we log the notification in the DB
        $this->create([
            'recipient_id' => $recipientId,
            'type' => $type,
            'payload' => $message,
            'is_read' => 0
        ]);

        // Simulating the SMS/Email triggers
        if ($type === 'SMS') {
            // SMS logic integration stub
            error_log("Sending SMS payload to Admin {$recipientId}: {$message}");
        } elseif ($type === 'EMAIL') {
            // Email logic integration stub
            error_log("Sending Email payload to Admin {$recipientId}: {$message}");
        }

        return true;
    }
}
