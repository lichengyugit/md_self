<?php
class PayTestModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'pay_test');
        $this->log->log_debug('DistrictModel  model be initialized');
    }
    
    
    
    public function addLog($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    public function test($parames){
        $sql=" INSERT IGNORE INTO ".$this->tablename."(`battery_num`,`create_time`,`create_date`) VALUES ";
        foreach($parames as $k=>$v){
            $sql.='('.'"'.$v.'"'.','.'"'.time().'"'.','.'"'.date("Y-m-d H:i:s",time()).'"'.')'.',';
        }
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }
    
    /**
     * 获取所有电池编号
     */
    public function getBatterysNumberByAttr(){
        $sql = " SELECT battery_num FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }
}
?>