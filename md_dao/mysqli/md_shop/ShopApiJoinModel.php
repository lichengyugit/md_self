<?php
header("content-type:text/html;charset=utf-8");
class ShopApiJoinModel extends Db_Model{
    protected $tables = array(
        'goods_repertory'=>"md_shop.md_goods_repertory",            //商家库存表
        'picture'        =>"md_lixiang.md_picture",                 //图片表
        'merchant'        =>"md_lixiang.md_merchant",               //商家表
        'vehicle_reserve'=>"md_shop.md_vehicle_reserve",            //车辆预定表
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_vehicle');
        $this->log->log_debug('ShopApiJoinModel  model be initialized');
    }

    /*
     * 商家库存,预定表连表
     * */
    public function repeVehicelPicJoin($data='')
    {
        $where='';
        $wheres='';
        empty($data['goods_type'])        || $where.=" AND gsr.goods_type     =".$data['goods_type'];
        empty($data['sell_type'])         || $where.=" AND gsr.sell_type      =".$data['sell_type'];
        empty($data['brand_id'])          || $where.=" AND gsr.brand_id       =".$data['brand_id'];
        empty($data['brand_series_id'])   || $where.=" AND gsr.brand_series_id=".$data['brand_series_id'];
        empty($data['merchant_id'])       || $where.=" AND gsr.merchant_id    =".$data['merchant_id'];
        empty($data['goods_repertory_id'])|| $where.=" AND gsr.id             =".$data['goods_repertory_id'];
        if(!empty($data['start_time'])){
            $strTime=strtotime($data['start_time']);
            $wheres.=' AND end_time > '.$strTime;
        }
        $sql=" SELECT gsr.id,total_goods_id,brand_id,brand_name,brand_series_id,brand_series_name,repertory_num,day_rent_price,week_rent_price,month_rent_price,half_year_rent_price,year_rent_price,ver_count FROM ".$this->tables['goods_repertory']." 
               AS gsr LEFT JOIN (SELECT goods_repertory_id,count(id) AS ver_count FROM ".$this->tables['vehicle_reserve']." WHERE status=0 ".$wheres." GROUP BY goods_repertory_id)
               AS ver ON ver.goods_repertory_id=gsr.id WHERE gsr.status=0 ".$where." GROUP BY gsr.id DESC";
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     * 商家与库存表连表       查询出该型号有库存的商家
     * */
    public function merchantInventoryJoin($parames)
    {
        $where='';
        empty($parames['total_goods_id']) || $where.=" AND GSR.total_goods_id=".$parames['total_goods_id'];
        empty($parames['sell_type']) || $where.=" AND GSR.sell_type=".$parames['sell_type'];
        $sql="SELECT M.longitude,M.latitude,M.location,M.name,M.id,GSR.id as goods_repertory_id FROM ".$this->tables['merchant']." 
        AS M LEFT JOIN ".$this->tables['goods_repertory']." AS GSR ON GSR.merchant_id=M.id WHERE M.status=0 AND M.type=2 AND GSR.repertory_num > 0 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

}