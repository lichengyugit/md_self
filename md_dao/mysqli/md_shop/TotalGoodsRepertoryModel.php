<?php
class TotalGoodsRepertoryModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_total_goods_repertory');
        $this->log->log_debug('TotalGoodsRepertoryModel  model be initialized');
    }

    //根据条件获取单条总库存信息
    public function getTotalGoodsByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql);
    }

    //获取购买商品列表
    public function getSearchTotalGoodsByAttr($parames){
        $sql = " SELECT *,min(retail_price) as min_retail_price FROM ".$this->tablename." WHERE status=0 AND all_stock>0 ";
        empty($parames['brand_id']) || $sql.=' AND brand_id= '.$parames['brand_id'];
        empty($parames['brand_name']) || $sql.=' AND brand_name like "%'.$parames['brand_name'].'%"';
        $sql.=" GROUP BY brand_series_type_id ";
        if($parames['the_price']==1){
            $sql.="ORDER BY min_retail_price";
        }elseif($parames['the_price']==2){
            $sql.="ORDER BY min_retail_price DESC";
        }
        return $this->getCacheResultArray($sql);
    }

    //根据条件获取多条总库存信息
    public function getTotalGoodsByAll($parames)
    {
        $where="";
        if(isset($parames['type'])){
            $where=" AND all_stock>0";
            unset($parames['type']);
        }
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }

        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql);
    }
}
?>