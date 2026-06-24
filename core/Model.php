<?php
namespace Core;

class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->update($this->table, $data, 'id = :where_id', ['where_id' => $id]);
    }

    public function delete($id) {
        // Safe delete / restrict delete depends on requirements
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->rowCount();
    }
}
