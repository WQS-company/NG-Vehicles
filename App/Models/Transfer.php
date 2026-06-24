<?php
namespace App\Models;

use Core\Model;

class Transfer extends Model {
    protected $table = 'ownership_transfers';

    public function getHistoryForVehicle($vehicleId) {
        $sql = "SELECT oh.*, o.full_name, o.phone, o.email, o.passport_photo_path, o.signature_path 
                FROM ownership_history oh 
                JOIN owners o ON oh.owner_id = o.id 
                WHERE oh.vehicle_id = :vehicle_id 
                ORDER BY oh.purchase_date ASC, oh.created_at ASC";
        return $this->db->fetchAll($sql, ['vehicle_id' => $vehicleId]);
    }

    public function getCurrentOwner($vehicleId) {
        $sql = "SELECT oh.*, o.full_name, o.phone, o.email, o.passport_photo_path 
                FROM ownership_history oh 
                JOIN owners o ON oh.owner_id = o.id 
                WHERE oh.vehicle_id = :vehicle_id 
                ORDER BY oh.purchase_date DESC, oh.created_at DESC LIMIT 1";
        return $this->db->fetch($sql, ['vehicle_id' => $vehicleId]);
    }

    // Perform ownership transfer inside a transaction to ensure immutability and consistency
    public function transferOwnership($vehicleId, $sellerId, $buyerId, $data) {
        $pdo = $this->db->getConnection();
        try {
            $pdo->beginTransaction();

            // 1. Create a record in ownership_transfers
            $transferData = [
                'vehicle_id' => $vehicleId,
                'seller_id' => $sellerId,
                'buyer_id' => $buyerId,
                'transfer_date' => $data['transfer_date'],
                'sale_price' => $data['sale_price'],
                'market_name' => $data['market_name'],
                'witness_name' => $data['witness_name'],
                'middleman_name' => $data['middleman_name'],
                'approved_by' => $data['approved_by']
            ];
            $this->db->insert('ownership_transfers', $transferData);

            // 2. Add new current owner to ownership_history (Immutable Chain)
            $historyData = [
                'vehicle_id' => $vehicleId,
                'owner_id' => $buyerId,
                'purchase_date' => $data['transfer_date'],
                'purchase_amount' => $data['sale_price'],
                'market_name' => $data['market_name'],
                'seller_name' => $data['seller_name'],
                'seller_phone' => $data['seller_phone'],
                'witness_name' => $data['witness_name'],
                'middleman_name' => $data['middleman_name']
            ];
            $this->db->insert('ownership_history', $historyData);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
