<?php
class VehicleLogModel extends Db_Model {
    protected $table=array(
         'cabinet'=>'md_lixiang.md_cabinet',
         'user'=>'md_lixiang.md_user',
         'site'=>'md_lixiang.md_site',
    );

    public function __construct() {
        parent::__construct('md_rent_car','md_vehicle_log');
        $this->log->log_debug('VehicleLogModel  model be initialized');
    }

    
    /**
     * 添加log
     */
    public function addCarLog($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }


}


