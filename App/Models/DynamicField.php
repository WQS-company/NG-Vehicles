<?php
namespace App\Models;

use Core\Model;

class DynamicField extends Model {
    protected $table = 'dynamic_fields';

    protected function hasActiveColumn() {
        static $hasActive = null;
        if ($hasActive !== null) {
            return $hasActive;
        }

        try {
            $sql = "SHOW COLUMNS FROM {$this->table} LIKE 'is_active'";
            $column = $this->db->fetch($sql);
            $hasActive = !empty($column);
        } catch (\Exception $e) {
            $hasActive = false;
        }

        return $hasActive;
    }

    public function getFieldsForEntity($entity, $includeInactive = false) {
        $params = ['entity' => $entity];
        if ($includeInactive || !$this->hasActiveColumn()) {
            $sql = "SELECT * FROM {$this->table} WHERE entity = :entity ORDER BY created_at ASC";
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE entity = :entity AND is_active = 1 ORDER BY created_at ASC";
        }

        $rows = $this->db->fetchAll($sql, $params);
        if (!$this->hasActiveColumn()) {
            foreach ($rows as &$row) {
                if (!isset($row['is_active'])) {
                    $row['is_active'] = 1;
                }
            }
            unset($row);
        }

        return $rows;
    }

    public function setActive($id, $active = true) {
        if (!$this->hasActiveColumn()) {
            return false;
        }

        return $this->update($id, ['is_active' => $active ? 1 : 0]);
    }
}
