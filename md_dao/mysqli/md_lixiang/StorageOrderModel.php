 <?php
class StorageOrderModel extends DB_Model
{
    protected $tables = array(
        'storage_survey_record'=>'md_lixiang.md_storage_survey_record'
    );

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_order');
        $this->log->log_debug('StorageOrderModel  model be initialized');
    }





    //   -------------[增:]   
    /**
     * [addOrder 根据data新增数据]
     * @return [type] [bool]
     */
    public function addOrder($data){
      $data['create_date']=date("Y-m-d H:i:s",time());
      $data['create_time']=time();
      $insert=$this->insert($data);
      return $this->lastInsertId();
    }





    //   -------------[查:]   
    /**
     * [QueryUserOrder  手机调拨 查找人员的订单]
     * @return [type] [bool]
     */
    public function QueryUserOrder($data){
      $sql='select * from md_storage_order where type = 2 and attr_type = 1 and order_status = 0 and user_id = '.$data;
      $arr=$this->getCacheResultArray($sql);
      return $arr;
    }


    /**
     * [QueryUserOrder  手机调拨 查找人员的订单]
     * @return [type] [bool]
     */
    public function QueryOrderInformation($data,$limit){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql='select * from md_storage_order where status < 2 '.$where.' ORDER BY create_time DESC Limit '.$limit;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }


    /*
     * 根据条件获取出库记录数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = "."'".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }





    /**
     * 更改记录
     */
    public function updateWheresStorageOrder($data,$where){
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }


    /**
     * [AdminAllotDimSearch 根据条件模糊查询]
     * @return [doType] [操作类型 1人员 2站点]
     * @return [doStatus] [操作类型 0正在派单 1已完成]
     * @return [time] [时间范围]
     * @return [search] [搜索内容]
     */
    public function AdminAllotDimSearch($doType,$doStatus,$time,$search,$limit){
        $sql="SELECT * FROM md_storage_order WHERE status<2 ".$doType.$doStatus.$time." AND CONCAT(IFNULL(creator_name,''),IFNULL(user_name,''),IFNULL(site_name,'')) LIKE '%".$search."%' ORDER BY create_time DESC Limit ".$limit;
        return $this->getCacheResultArray($sql);
    }

    /**
     * [getAdminAllotDimSearchNum 根据条件模糊查询统计数量]
     * @return [doStatus] [操作类型 0正在派单 1已完成]
     * @return [time] [时间范围]
     * @return [search] [搜索内容]
     */
    public function getAdminAllotDimSearchNum($doType,$doStatus,$time,$search){
        $sql="SELECT count(1) as c FROM md_storage_order WHERE status<2 ".$doStatus.$time." AND CONCAT(IFNULL(creator_name,''),IFNULL(user_name,''),IFNULL(site_name,'')) LIKE '%".$search."%' ORDER BY create_time DESC";
        return $this->getCacheRowArray($sql)['c'];
    }


    /**
     * 根据条件删除订单信息
     */
    public function delectInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " DELETE FROM ".$this->tablename." WHERE 1=1 ".$where;
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }



    //勘测系统搜索派单机柜
    public function getSurveyCabinetData($parames)
    {
        $where='';
        if(isset($parames['cabinet_number']) && $parames['cabinet_number']!=''){
            $where.=" AND R.code LIKE "."'%". $parames['cabinet_number'] ."%'";
        }
        empty($parames['site_id']) || $where.=" AND O.site_id=".$parames['id'];
        $sql=" SELECT R.*,O.id as orderId FROM ".$this->tablename." AS O LEFT JOIN ".$this->tables['storage_survey_record'] ." AS R ON O.id=R.order_id
         WHERE O.status=0 AND O.order_platform=2 AND O.type=1 AND O.order_status=0".$where.' ORDER BY O.id';
        return $this->getCacheResultArray($sql);
    }
    /**
     * [selectRule 查询单条数据]
     * @return [type] [arr]
     */
    public function getRecordOrderRowData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
}
