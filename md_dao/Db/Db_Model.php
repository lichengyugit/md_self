<?php
require_once 'Db_Cacheable.php';
class Db_Model extends Db_Cacheable {
    var $log;
    var $dbname;
    public $database='md_lixiang';
    public function __construct($dbname, $table) {
        parent::__construct($dbname, $table);
        $this->log = &get_log();
        // $this->database='cro';
    }

    public function insertDuplicateKey($data) {
        $ks = array();
        $vals = array();
        $values = array();
        $updates = '';
        foreach ($data as $key=>$val) {
            $ks[] = $key;
            $vals[] = '?';
            $values[] = $val;
            $updates .= $key . ' = ?,';
        }
        $sql = 'INSERT INTO ' . $this->tablename . '(' . implode(', ', $ks) . ') VALUES(' . implode(', ', $vals) . ') ON DUPLICATE KEY UPDATE ' . $updates;
        $sql = rtrim($sql, ',');
        $this->writeQuery($sql, array_merge($values, $values));
        return $this->write_db->affected_rows();
    }

    public function affectedRows() {
        return $this->write_db->affected_rows();
    }

    public function lastInsertId() {
        return $this->write_db->insert_id();
    }

    public function insert($data) {
        $this->write_db->insert($this->tablename, $data);
        parent::afterInsert($data);
        return $this->write_db->insert_id();
    }

    /**
     * [insertBatch 批量增数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function insertBatch($data) {
        $this->write_db->insert_batch($this->tablename, $data);
        parent::afterInsert($data);
        //return $this->write_db->insert_id();
        return $this->write_db->affected_rows();
    }

    public function update($data, $wheres) {
        $this->write_db->where($wheres);
        $this->write_db->update($this->tablename, $data);
        parent::afterUpdate($data, $wheres);
        return $this->write_db->affected_rows();
    }

    public function delete($id) {
        $wheres['id'] = $id;
        $this->write_db->delete($this->tablename, $wheres);
        parent::afterDelete($wheres);
        return $this->write_db->affected_rows();
    }

    /**
     * [deleteBath 批量删除]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteBath($wheres) {
        $this->write_db->delete($this->tablename, $wheres);
        parent::afterDelete($wheres);
        return $this->write_db->affected_rows();
    }

    public function replace($data) {
        $this->write_db->insert($this->tablename, $data);
        return $this->write_db->insert_id();
    }

    public function deleteByParam($wheres) {
        $this->write_db->delete($this->tablename, $wheres);
        parent::afterDelete($wheres);
        return $this->write_db->affected_rows();
    }

    public function readQuery($sql, $binds = FALSE) {
        return $this->read_db->query($sql, $binds);
    }

    public function writeQuery($sql, $binds = FALSE) {
        $result = $this->write_db->query($sql, $binds);
        if ($result) {
            parent::afterQuery($sql, $binds);
        }
        return $result;
    }

    public function trans_start() {
        return $this->write_db->trans_start();
    }
    public function trans_complete() {
        return $this->write_db->trans_complete();
    }
    public function trans_status() {
        return $this->write_db->trans_status();
    }
}


