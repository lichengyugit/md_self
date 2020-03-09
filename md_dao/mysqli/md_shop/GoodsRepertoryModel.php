<?php
class GoodsRepertoryModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_goods_repertory');
        $this->log->log_debug('GoodsRepertoryModel  model be initialized');
    }
    /**
     * 根据条件获得所有商家库存信息
     */
    public function getAllMerchantData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 ".$where;
        $arr=$this->getCacheResultArray($sql,$where);
        return $arr;
    }


    /**
     * 根据条件获得所有商家库存信息
     */
    public function getAllMerchantProData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT id,brand_id,brand_name,GROUP_CONCAT(brand_series_id) AS brand_series_id,GROUP_CONCAT(brand_series_name) AS brand_series_name FROM ".$this->tablename." WHERE `status` = 0 ".$where ." GROUP BY brand_id";
        $arr=$this->getCacheResultArray($sql,$where);
        return $arr;
    }

    //按条件查询单条数据
    public function getRepertoryInfoAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /*
     * 修改商家库存信息
     * */
    public function update($data,$wheres)
    {
        $sql=" UPDATE ".$this->tablename." SET repertory_num=".$data." WHERE ".$wheres;
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /**
     * 查询所有商家下车辆总库存
     */
    public function getRepertoryNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT sum(repertory_num) as repertory_num FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    //获取售卖车辆列表
    public function getSearchGoodsRepertoryByAttr($parames){
        $sql = " SELECT *,min(retail_price) as min_retail_price FROM ".$this->tablename." WHERE status=0 AND repertory_num>0 AND sell_type=2";
        empty($parames['brand_id']) || $sql.=' AND brand_id= '.$parames['brand_id'];
        empty($parames['brand_name']) || $sql.=' AND brand_name like "%'.$parames['brand_name'].'%"';
        $sql.=" GROUP BY total_goods_id ";
        if($parames['the_price']==1){
            $sql.="ORDER BY min_retail_price";
        }elseif($parames['the_price']==2){
            $sql.="ORDER BY min_retail_price DESC";
        }
        return $this->getCacheResultArray($sql);
    }

    /**
     * 根据条件获得某个品牌下商品信息
     */
    public function getBrandGoodsByAttr($parames,$type=0){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        empty($type) || $where.=' AND repertory_num>0 ';
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 ".$where." GROUP BY total_goods_id ";
        $arr=$this->getCacheResultArray($sql,$where);
        return $arr;
    }
}
?>