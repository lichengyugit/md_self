<?php
class BreakdownBatteryModel extends Db_Model {
    protected $table=array(

    );

    public function __construct() {
        parent::__construct($this->dbname,'md_breakdown_battery');
        $this->log->log_debug('breakdownBatteryModel  model be initialized');
    }

    /**
     * 插入电池信息反馈表数据
     */
    public function insertData($parames){
        $insert=$this->insert($parames);
        return $this->lastInsertId();
    }

    /**
     * 根据属性获取单条信息
     */
    public function getInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 批量插入数据
     */
    public function insertDatas($parames){
        $sql=" INSERT INTO ".$this->tablename."(`user_id`,`user_name`,`mobile`,`battery_num`,`type`,`msg`,`create_time`,`create_date`) VALUES ";
        foreach($parames['type'] as $k=>$v){
            $sql.='("'.$parames['user_id'].'","'.$parames['user_name'].'","'.$parames['mobile'].'","'.$parames['battery_num'].'","'.$v.'","'.$parames['msg'].'","'.$parames['create_time'].'","'.$parames['create_date'].'"),';
        }
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /*
     * 后台列表数据
     * */
    public function getBreakdownBatteryData($data,$limit='')
    {
        $where='';
//        empty($data['type']) || $where.=" AND type=".$data['type'];
        empty($data['type']) || $where.=" AND user_id in(select user_id from ".$this->tablename." where status=0 and type=".$data['type'].") and battery_num in (select battery_num from ".$this->tablename." where status=0 and type=".$data['type'].")";
        if(isset($data['input_data']) && $data['input_data']!=''){
            $inputData=trim($data['input_data']);
            if(preg_match("/^1[345678]{1}\d{9}$/",$inputData)){
                $where.=" AND mobile=".$inputData;
            }elseif(preg_match('/^[\x7f-\xff]+$/',$inputData)){
                $where.=" AND  user_name LIKE "."'%". $inputData ."%'";
            }elseif(preg_match( '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{13}$/',$inputData)){
                $where.=" AND battery_num="."'".$inputData."'";
            }
        }
        $sql=" SELECT create_date,msg,user_name,mobile,battery_num,GROUP_CONCAT(type) AS brack_type FROM ".$this->tablename." WHERE status=0 ".$where." GROUP BY user_id,battery_num ORDER BY id DESC ".$limit;
        return $this->getCacheResultArray($sql);exit;
    }

}
