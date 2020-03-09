<?php
class GoodsPropertyModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_goods_property');
        $this->log->log_debug('GoodsPropertyModel  model be initialized');
    }

    //根据条件获取多条属性
    public function getPropertysByAttr($parames)
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