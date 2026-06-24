<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Commission extends Model {
    protected $table = 'commission_recipients';

    public function __construct() {
        parent::__construct();
        $this->ensureCommissionSchema();
    }

    protected function ensureCommissionSchema() {
        $hasTable = $this->db->fetch("SHOW TABLES LIKE 'commission_recipients'");
        if ($hasTable) {
            return;
        }

        $queries = [
            "CREATE TABLE IF NOT EXISTS commission_recipients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(255) NULL,
                bank_name VARCHAR(150) NULL,
                bank_code VARCHAR(50) NULL,
                account_number VARCHAR(50) NULL,
                account_name VARCHAR(150) NULL,
                percentage_share DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                paystack_recipient_code VARCHAR(150) NULL,
                total_paid DECIMAL(15,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS commission_payouts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                revenue_amount DECIMAL(15,2) NOT NULL,
                processed_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS commission_payout_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                payout_id INT NOT NULL,
                recipient_id INT NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                status ENUM('PENDING','SUCCESS','FAILED') DEFAULT 'PENDING',
                paystack_transfer_id VARCHAR(150) NULL,
                response TEXT NULL,
                paid_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_payout_event FOREIGN KEY (payout_id) REFERENCES commission_payouts(id) ON DELETE CASCADE,
                CONSTRAINT fk_payout_recipient FOREIGN KEY (recipient_id) REFERENCES commission_recipients(id) ON DELETE CASCADE
            ) ENGINE=InnoDB"
        ];

        foreach ($queries as $sql) {
            $this->db->query($sql);
        }
    }

    public function allRecipients() {
        return $this->all();
    }

    public function findRecipient($id) {
        return $this->find($id);
    }

    public function addRecipient($data) {
        return $this->create($data);
    }

    public function updateRecipient($id, $data) {
        return $this->update($id, $data);
    }

    public function deleteRecipient($id) {
        return $this->delete($id);
    }

    // Calculate shares for a given revenue amount based on stored percentage_share
    public function calculateShares(float $revenue) {
        $recipients = $this->allRecipients();
        $allocations = [];
        foreach ($recipients as $r) {
            $amount = round(($r['percentage_share'] / 100.0) * $revenue, 2);
            $allocations[] = [
                'recipient' => $r,
                'amount' => $amount
            ];
        }
        return $allocations;
    }

    // Create a payout event and items
    public function createPayoutEvent($title, $revenue, $processedBy, $allocations) {
        $db = Database::getInstance();
        $payoutId = $db->insert('commission_payouts', [
            'title' => $title,
            'revenue_amount' => $revenue,
            'processed_by' => $processedBy
        ]);

        foreach ($allocations as $alloc) {
            $db->insert('commission_payout_items', [
                'payout_id' => $payoutId,
                'recipient_id' => $alloc['recipient']['id'],
                'amount' => $alloc['amount']
            ]);
        }

        return $payoutId;
    }

    public function getPayoutById($id) {
        return $this->db->fetch("SELECT * FROM commission_payouts WHERE id = :id", ['id' => $id]);
    }

    public function getPayoutItems($payoutId) {
        return $this->db->fetchAll("SELECT i.*, r.name, r.email, r.account_number, r.bank_name FROM commission_payout_items i JOIN commission_recipients r ON r.id = i.recipient_id WHERE i.payout_id = :p", ['p' => $payoutId]);
    }

    // Execute transfer via Paystack (create recipient if missing)
    public function executeTransfer($recipient, $amount, $reference = null) {
        $config = require BASE_PATH . '/config/paystack.php';
        $secret = $config['secret_key'] ?? '';
        if (empty($secret)) {
            return ['status' => false, 'message' => 'Paystack secret not configured'];
        }

        // Ensure recipient has paystack_recipient_code
        if (empty($recipient['paystack_recipient_code']) && !empty($recipient['account_number']) && !empty($recipient['bank_code'])) {
            $create = $this->createPaystackRecipient($recipient, $secret, $config);
            if ($create['status']) {
                // persist code
                $this->db->update('commission_recipients', ['paystack_recipient_code' => $create['data']['recipient_code']], 'id = :where_id', ['where_id' => $recipient['id']]);
                $recipient['paystack_recipient_code'] = $create['data']['recipient_code'];
            } else {
                return ['status' => false, 'message' => 'Failed to create paystack recipient: ' . ($create['message'] ?? 'unknown')];
            }
        }

        if (empty($recipient['paystack_recipient_code'])) {
            return ['status' => false, 'message' => 'Recipient missing paystack recipient code'];
        }

        // Prepare transfer
        $url = rtrim($config['base_url'], '/') . '/transfer';
        $payload = [
            'source' => 'balance',
            'reason' => 'Commission payout',
            'amount' => (int)round($amount * 100),
            'recipient' => $recipient['paystack_recipient_code']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $secret,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($resp, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['status']) && $decoded['status'] === true) {
            return ['status' => true, 'data' => $decoded['data']];
        }

        return ['status' => false, 'message' => $decoded['message'] ?? 'Transfer failed', 'raw' => $decoded];
    }

    protected function createPaystackRecipient($recipient, $secret, $config) {
        $url = rtrim($config['base_url'], '/') . '/transferrecipient';
        $payload = [
            'type' => 'nuban',
            'name' => $recipient['account_name'] ?? $recipient['name'],
            'account_number' => $recipient['account_number'],
            'bank_code' => $recipient['bank_code'],
            'currency' => $config['currency'] ?? 'NGN'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $secret,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($resp, true);
        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['status']) && $decoded['status'] === true) {
            return ['status' => true, 'data' => $decoded['data']];
        }
        return ['status' => false, 'message' => $decoded['message'] ?? 'Recipient creation failed', 'raw' => $decoded];
    }
}
