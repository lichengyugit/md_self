<?php
class BackstageLogModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_backstage_log');
        $this->log->log_debug('BackstageLogModel  model be initialized');
    }
    
    
    /*
     * 后台操作日志添加
     * */
    public function addLog($data){
        $data['create_time']=time();
        $data['create_date']=date("Y-m-d H:i:s",time());
        return $this->insert($data);
    }
    /**
     * 根据条件获取后台操作日志数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /**
     * 根据条件获得所有后台日志列表
     */
    public function getAllCabinetByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC  LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     * 条件查询获取数据
     * */
    public function getSearchBackstageData($data='',$limit='')
    {
        $where='';
        if(isset($data['operation_type']) && $data['operation_type']!=''){
            $where.=" AND operation_type =".$data['operation_type'];
        }
        empty($data['operation_state'])|| $where.=" AND operation_state =".$data['operation_state'];
        empty($data['user_flag'])      || $where.=" AND user_flag =".'"'.$data['user_flag'].'"';
        if(!empty($data['create_time'])){
            $str=preg_split('/\s-\s/',$data['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=" AND create_time BETWEEN ".$strTime."  AND ".$endTime;
        }
        empty($data['input_data'])     || $where.=" AND user_name LIKE " ."'%". $data['input_data'] ."%' OR user_mobile LIKE " ."'%". $data['input_data'] ."%' OR url LIKE " ."'%". $data['input_data'] ."%'";
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }
}
?>