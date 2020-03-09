<?php
class StorageBatteryRecordModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_battery_record');
        $this->log->log_debug('StorageRecordModel  model be initialized');
    }


    //   -------------[增:]   
    /**
     * [addRecord 根据data新增数据]
     * @return [type] [bool]
     */
    public function addRecord($data){
    	$data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
    	$insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * [deleteRecord 根据条件删除数据]
     * @return [type] [bool]
     */
    public function deleteRecord($data){
        return $this->delete($data);
    }


    /**
     * 更改记录
     */
    public function updateWheresRecord($data,$where){
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }


    //   -------------[查:]   
       
    /**
     * [selectRule 查询库存多条数据]
     * @return [type] [arr]
     */
    public function selectRule($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status < 2".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }


    /**
     * [QueryAccordingOrder 根据订单查询记录]
     * @return [type] [arr]
     */
    public function QueryAccordingOrder($data){
        $sql='SELECT * FROM md_storage_battery_record WHERE (details_status = 0 or details_status = 3) AND order_id in (SELECT id FROM md_storage_order WHERE type = 2 AND attr_type = 1 AND order_status = 0 AND user_id = '.$data.')';
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }

    /**
     * [QueryAccordingLikeOrder 根据订单记录下的电池编号模糊查询记录]
     * @return [userID] [value]
     * @return [search] [value]
     */
    public function QueryAccordingLikeOrder($userID,$search){
        $sql='SELECT * FROM md_storage_battery_record WHERE (details_status = 0 or details_status = 3) AND order_id in (SELECT id FROM md_storage_order WHERE type = 2 AND attr_type = 1 AND order_status = 0 AND user_id = '.$userID.") AND CONCAT(IFNULL(battery_num,'')) LIKE %".$search."% ORDER BY create_time DESC";
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }




    /**
     * 检查电池编号是否存在表内
     */
    public function inspectBatteryNum($parames){
      $sql=' SELECT battery_num FROM '.$this->tablename.' WHERE details_status=0 AND status<2 AND battery_num='.'"'.$parames.'"';
      $result=$this->getCacheRowArray($sql);
      if(!empty($result)){
        return true;
      }else{
        return false;
      }
    }



    /**
     * [selectRule 查询库存多条数据]
     * @return [type] [arr]
     */
    public function selectAllotRecord($data,$limit){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM md_storage_battery_record WHERE status < 2".$where." ORDER BY create_time DESC Limit ".$limit;
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
     * [AdminAllotDimSearch 根据条件模糊查询]
     * @return [doStatus] [操作类型 0正在派单 1已完成]
     * @return [time] [时间范围]
     * @return [search] [搜索内容]
     */
    public function AdminAllotDimSearch($doStatus,$time,$search,$limit){
        $sql="SELECT * FROM md_storage_battery_record WHERE status<2 ".$doStatus.$time." AND CONCAT(IFNULL(battery_num,''),IFNULL(user_name,''),IFNULL(site_name,'')) LIKE '%".$search."%' ORDER BY create_time DESC Limit ".$limit;
        return $this->getCacheResultArray($sql);
    }


    /**
     * [getAdminAllotDimSearchNum 根据条件模糊查询统计数量]
     * @return [doStatus] [操作类型 0正在派单 1已完成]
     * @return [time] [时间范围]
     * @return [search] [搜索内容]
     */
    public function getAdminAllotDimSearchNum($doStatus,$time,$search){
        $sql="SELECT count(1) as c FROM md_storage_battery_record WHERE status<2 ".$doStatus.$time." AND CONCAT(IFNULL(battery_num,''),IFNULL(user_name,''),IFNULL(site_name,'')) LIKE '%".$search."%' ORDER BY create_time DESC";
        return $this->getCacheRowArray($sql)['c'];
    }

















}





