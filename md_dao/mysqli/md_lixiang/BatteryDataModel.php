<?php
class BatteryDataModel extends Db_Model {
    protected $table=array(

    );

    public function __construct() {
        parent::__construct($this->dbname,'md_battery_data');
        $this->log->log_debug('BatteryDataModel  model be initialized');
    }

    /**
     * 批量插入电池数据
     */
    public function insertBatterys($parames){
        $sql=" INSERT IGNORE INTO ".$this->tablename."(`battery_num`,`site_id`,`cabinet_id`,`user_id`,`create_time`,`create_date`) VALUES ";
            $sql.='("'.$parames['batteryNum'].'","'.$parames['siteId'].'","'.$parames['cabinetId'].'","'.$parames['userId'].'","'.time().'","'.date("Y-m-d H:i:s",time()).'"),';
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }


}
