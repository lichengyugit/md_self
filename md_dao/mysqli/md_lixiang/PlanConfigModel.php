<?php
class PlanConfigModel extends Db_Model {
    protected $table=array(

    );

    public function __construct() {
        parent::__construct($this->dbname,'md_plan_config');
        $this->log->log_debug('PlanConfigModel  model be initialized');
    }
    
    /**
     * 根据条件获取单条方案配置信息
     */
    public function getPlanConfigByAttrs($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
}