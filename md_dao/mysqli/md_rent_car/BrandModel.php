<?php
class BrandModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_rent_car', 'md_brand');
        $this->log->log_debug('BrandModel  model be initialized');
    }

    //根据条件获取多条品牌信息
    public function getBrandByAll($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql);
    }
}
?>