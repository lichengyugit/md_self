<?php
class VehicleReserveModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_vehicle_reserve');
        $this->log->log_debug('VehicleReserveModel  model be initialized');
    }

    /**
     * 根据条件删除车辆预定信息
     */
    public function delectInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " DELETE FROM ".$this->tablename." WHERE `status`=0 ".$where;
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /**
     * 添加单条预定信息
     */
    public function saveVehicleData($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
}
?>