<?php
class BatteryLogModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_battery_log');
        $this->log->log_debug('BatteryLogModel  model be initialized');
    }
    
    
    
    public function addLog($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 批量增加电池使用日志记录
     * @param array $data
     */
    public function addLogs($data){
        $sql=" INSERT INTO ".$this->tablename."(`battery_id`,`cabinet_id`,`user_id`,`user_name`,`type`,`msg`,`dump_energy`,`service_time`,`create_time`,`create_date`,`soh`) VALUES ";
        foreach($data as $k=>$v){
            $sql.='("'.$v['batteryId'].'","'.$v['cabinetId'].'","'.$v['userId'].'","'.$v['userName'].'","'.$v['type'].'","'.$v['msg'].'","'.$v['dump_energy'].'","'.$v['service_time'].'","'.time().'","'.date('Y-m-d H:i:s').'","'.$v['soh'].'"),';
        }
        /*$sql=" INSERT INTO ".$this->tablename."(`battery_id`,`cabinet_id`,`user_id`,`user_name`,`type`,`msg`,`dump_energy`,`service_time`,`create_time`,`create_date`) VALUES ";
        foreach($data as $k=>$v){
            $sql.='("'.$v['batteryId'].'","'.$v['cabinetId'].'","'.$v['userId'].'","'.$v['userName'].'","'.$v['type'].'","'.$v['msg'].'","'.$v['dump_energy'].'","'.$v['service_time'].'","'.time().'","'.date('Y-m-d H:i:s').'"),';
        }*/
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /*
     * 按条件查询所有数据
     * */
    public function getBatteryLogAll($params,$limit='')
    {
        $where='';
        if(isset($params['type']) && $params['type']!=''){
            $where.=" AND type= ".$params[ 'type'];
        }
        if(!empty($params['create_time'])){
            $str=preg_split('/\s-\s/',$params['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND create_time>='.$strTime.' AND create_time<='.$endTime;
        }
        if(isset($params['input_data']) && $params['input_data']!=''){
            $inputData=trim($params['input_data']);
            if(preg_match('/^[\x7f-\xff]+$/',$inputData)) {
                $where .= " AND  user_name  =" . "'".$inputData."'";
            }elseif(preg_match( '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{13}$/',$inputData)){
                $where.=" AND battery_id="."'".$inputData."'";
            }elseif(preg_match('/^(\b[0-9a-zA-Z]{8}\b[^0-9a-zA-Z]?)+$/',$inputData)){
                $where.=" AND cabinet_id="."'".$inputData."'";
            }
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE
         id<=(SELECT id FROM ".$this->tablename." WHERE status<2 ".$where." ORDER BY id DESC LIMIT ".$limit.",1) AND status<2  ".$where." ORDER BY id DESC LIMIT 15";
        return $this->getCacheResultArray($sql);
    }

    /*
     * 按条件查询数量
     * */
    public function getBatteryCount($params)
    {
        $where='';
        if(isset($params['type']) && $params['type']!=''){
            $where.=" AND type= ".$params[ 'type'];
        }
        if(!empty($params['create_time'])){
            $str=preg_split('/\s-\s/',$params['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND create_time>='.$strTime.' AND create_time<='.$endTime;
        }
        if(isset($params['input_data']) && $params['input_data']!=''){
            $inputData=trim($params['input_data']);
            if(preg_match('/^[\x7f-\xff]+$/',$inputData)) {
                $where .= " AND  user_name  ="."'".$inputData."'";
            }elseif(preg_match( '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{13}$/',$inputData)){
                $where.=" AND battery_id="."'".$inputData."'";
            }elseif(preg_match('/^(\b[0-9a-zA-Z]{8}\b[^0-9a-zA-Z]?)+$/',$inputData)){
                $where.=" AND cabinet_id="."'".$inputData."'";
            }
        }
        $sql=" SELECT count(1) FROM ".$this->tablename." WHERE status < 2 ".$where;
        return $this->getCacheRowArray($sql)['count(1)'];
    }
}
?>