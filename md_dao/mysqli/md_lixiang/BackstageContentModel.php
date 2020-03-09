<?php
class BackstageContentModel extends DB_Model {
    protected $tables = array(

    );

    public function __construct() 
    {
        parent::__construct($this->dbname, 'md_backstage_content');
        $this->log->log_debug('BackstageContentModel  model be initialized');
    }
    
    /**
     * 添加
     */
    public function addBackstateContent($data)
    {
        $data['create_time'] = time();
        $data['create_date'] = date('Y-m-d H:i:s');
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * 批量添加
     */
    public function bashSaveBackContent($data)
    {
        $rs=$this->insertBatch($data);
        if($rs){
            return $rs;
        }else{
            return false;
        }
    }


    /**
     * 根据条件获得所有后台操作日志详情内容
     */
    public function getBackstageContentData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
}
?>