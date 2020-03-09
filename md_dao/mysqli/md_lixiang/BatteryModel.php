<?php
class BatteryModel extends Db_Model {
    protected $table=array(
         'cabinet'=>'md_lixiang.md_cabinet',
         'user'=>'md_lixiang.md_user',
         'site'=>'md_lixiang.md_site',
    );

    public function __construct() {
        parent::__construct($this->dbname,'md_battery');
        $this->log->log_debug('BatterModel  model be initialized');
    }

    /**
     * 根据电池码获取单条电池信息
     */
    public function getBattery($batteryNum){
        $sql = " SELECT * FROM ".$this->tablename." WHERE battery_num = ?";
        $row=$this->getCacheRowArray($sql,array($batteryNum));
        return $row;
    }
    
    /**
     * 根据绑定用户id获取单条电池信息(连表)
     */
    public function getBatteryByUser($userId){
        $sql= " SELECT b.*,c.cabinet_name FROM ".$this->tablename." AS b LEFT JOIN md_cabinet AS c ON b.cabinet_id=c.id WHERE b.user_id = ?";
        $row=$this->getCacheRowArray($sql,array($userId));
        return $row;
    }
    
    /**
     * 根据电池码获取单条电池信息(连表)
     */
    public function getBatteryByNumber($batteryNum){
        $sql= " SELECT b.*,c.cabinet_name FROM ".$this->tablename." AS b LEFT JOIN md_cabinet AS c ON b.cabinet_id=c.id WHERE b.battery_num = ?";
        $row=$this->getCacheRowArray($sql,array($batteryNum));
        return $row;
    }
    
    /**
     * 更改单个电池状态
     */
    public function updateBattery($data){
        if(isset($data['battery_num'])){
            $where['battery_num']=$data['battery_num'];
            unset($data['battery_num']);
        }elseif (isset($data['id'])){
            $where['id']=$data['id'];
            unset($data['id']);
        }elseif (isset($data['userId'])){
            $where['user_id']=$data['userId'];
            unset($data['userId']);
        }
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * 获取所有电池信息
     */
    public function getAllBattery(){
      $sql= " SELECT b.*,c.box_name FROM ".$this->tablename." AS b LEFT JOIN md_cabinet AS c ON b.box_id=c.id WHERE b.status <> 2";
      $row=$this->getCacheResultArray($sql);
      return $row;
    }
    
    /**
     * 添加电池
     */
    public function addBattery($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 根据条件获取电池数量
     */
    public function getBatteryByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
//        var_dump($sql);die;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
    
    /**
     * 根据条件获得所有电池列表
     */
    public function getAllBatteryPages($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT b.*,c.cabinet_name,i.name FROM ".$this->tablename." AS b LEFT JOIN md_cabinet AS c ON b.cabinet_id=c.id LEFT JOIN md_idcard AS i ON b.user_id=i.user_id WHERE b.status < 2 ".$where.' ORDER BY ID DESC'." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 根据条件获取单条电池信息
     */
    public function getBatteryByAttrs($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }


    /**
     * index页面获取列表数据
     */
    public function indexgetAllBatteryByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['battery']='电池列表';
        return $arr;
    }
    
    /**
     * 根据条件获取多条电池信息
     */
    public function getBatterysByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
             $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 获取所有电池编号
     */
    public function getBatterysNumberByAttr(){
        $sql = " SELECT battery_num FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 批量插入电池数据
     */
    public function insertBatterys($parames){
        $sql=" INSERT IGNORE INTO ".$this->tablename."(`battery_num`,`battery_cells`,`specification`,`battery_manufacturer`,`create_time`,`create_date`) VALUES ";
        foreach($parames as $k=>$v){
            $sql.='("'.$v['batteryNum'].'","'.$v['batteryCells'].'","'.$v['specification'].'","'.$v['batteryManufacturer'].'","'.time().'","'.date("Y-m-d H:i:s",time()).'"),';
        }
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /**
     * 检查电池编号是否存在表内
     */
    public function inspectBatteryNum($parames){
      $sql=' SELECT battery_num FROM '.$this->tablename.' WHERE battery_num='.'"'.$parames.'"';
      $result=$this->getCacheRowArray($sql);
      if(!empty($result)){
        return true;
      }else{
        return false;
      }
    }

    /**
     * 连表模糊查询用户数据
     */
    public function BatteryQuery($data,$LIMIT=''){
        $sql=" SELECT b.*,c.cabinet_name,i.name FROM ".$this->tablename." AS b LEFT JOIN md_cabinet AS c ON b.cabinet_id=c.id LEFT JOIN md_idcard AS i ON b.user_id=i.user_id WHERE b.status < 2 AND CONCAT(IFNULL(battery_num,'"."')) LIKE '%".trim($data)."%' ORDER BY id DESC ".$LIMIT;
        $Total=$this->getCacheResultArray($sql);
        return $Total;
    }

    /**
     * 根据搜索条件获取电池数据数量
     */
    public function getSearchCountBatteryByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" LIKE '%".trim($v)."%'";
        }
        $sql="SELECT count(1) as c FROM md_battery WHERE CONCAT(IFNULL(battery_num,'"."')) ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
       
    /**
     * 多条件更改单个电池状态
     */
    public function updateWheresBattery($data,$where){
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据电池编号获取多个电池状态
     */
    public function getBatterysByNumber($parames){
        $where="";
        foreach($parames as $k=>$v){
            $where.="'".$v."',";
        }
        $where=substr($where, 0,-1);
        $sql = " SELECT * FROM ".$this->tablename." WHERE battery_num IN(".$where.")";
        return $this->getCacheResultArray($sql);
    }

    /**
     * where in 更改电池状态
     * @batteryArr 电池数组
     * @update 更改信息
     */
    public function updateInWhereBattery($batteryArr,$update){
        $where="";
        foreach($batteryArr as $k=>$v){
            $where.="'".$v."',";
        }
        $where=substr($where, 0,-1);
        $updateData="";
        foreach($update as $k=>$v){
            $updateData.=$k."=".$v.",";
        }
        $updateData=substr($updateData, 0,-1);
        $sql = " UPDATE ".$this->tablename." SET ".$updateData."  WHERE battery_num IN(".$where.")";
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /*
     * 根据条件查询电池
     * */
    public function getBatteryData($parames,$limit='')
    {
        $where='';
        if(!empty($parames['select'])){
            if(preg_match( '/^[0-9a-zA-Z]+$/',trim($parames['select']))){
                $where.=" AND B.battery_num ="."'". trim($parames['select']) ."'";
            }else{
                $where.=" AND S.site_name LIKE"."'%". trim($parames['select']) ."%'";
            }

        }
        if(!empty($parames['rent_status'])){
            if($parames['rent_status']==1){
                $where.=" AND B.user_id =0 AND rent_status=1 ";
            }else{
                $where.=" AND B.rent_status=2";
            }
        }
        if(isset($parames['battery_status']) && $parames['battery_status']!=''){
            if($parames['battery_status']==2){
                $where.=" AND cabinet_id=0 AND battery_status=".$parames['battery_status'];
            }elseif($parames['battery_status']==3){
                $where.=" AND cabinet_id > 0 AND battery_status=".$parames['battery_status'];
            }else{
                $where.=" AND battery_status=".$parames['battery_status'];
            }
        }
        if(isset($parames['site_status'])){
            if($parames['site_status']==1){
               $where.=" AND ISNULL(B.site_id)";
            }elseif($parames['site_status']==2){
               $where.=" AND B.site_id IS NOT NULL";
            }
        }
        empty($parames['cabinet_id'])    || $where.=" AND B.cabinet_id=".$parames['cabinet_id'];
        empty($parames['battery_cells']) || $where.=" AND B.battery_cells="."'".$parames['battery_cells']."'";
        empty($parames['specification']) || $where.=" AND B.specification=".$parames['specification'];
        empty($parames['battery_manufacturer']) || $where.=" AND B.battery_manufacturer=".$parames['battery_manufacturer'];
         $sql="SELECT B.*,C.cabinet_name,C.cabinet_number,S.site_name,U.name FROM ".$this->tablename." AS B LEFT JOIN ".$this->table['cabinet']." AS C ON B.cabinet_id=C.id LEFT JOIN ".$this->table['user']." AS U ON B.user_id=U.id 
        LEFT JOIN ".$this->table['site']." AS S ON S.id=B.site_id WHERE B.status=0 ".$where." ORDER BY B.id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }
    


    /**
     * 根据条件获取多条电池信息
     */
    public function getBatteryTakeAwayByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
             $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE battery_status <> 8 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

    /**
     * 根据条件更新站点为空的电池信息
     */
    public function updateBatterySiteWeiapi($parames,$update){
        $where="";
        foreach($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $updateData="";
        foreach($update as $k=>$v){
            $updateData.=$k."=".$v.",";
        }
        $updateData=substr($updateData, 0,-1);
        $sql = " UPDATE ".$this->tablename." SET ".$updateData."  WHERE site_id is null ".$where;
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }







}


