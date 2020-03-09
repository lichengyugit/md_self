<?php
class PledgeBatteryModel extends Db_Model {
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname,'md_pledge_battery');
        $this->log->log_debug('PledgeBatteryModel  model be initialized');
    }

    /**
     * 批量插入数据
     */
    public function insertInfos($parames){
        $sql=" INSERT INTO ".$this->tablename."(`user_id`,`pledge_order_id`,`battery_num`,`create_time`,`create_date`) VALUES ";
        foreach($parames as $k=>$v){
            $sql.='("'.$v['userId'].'","'.$v['pledgeOrderId'].'","'.$v['batteryNum'].'","'.time().'","'.date('Y-m-d H:i:s').'"),';
        }
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }
    
    /**
     * 增加单条数据
     */
    public function addData($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * 根据条件获取多条用户押金电池中间表信息
     */
    public function getMoreInfoByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据条件更改数据
     */
    public function updateInfoById($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据电池编号获取多个电池状态
     */
    public function getMoreInfoByBatteryNum($parames,$elseWhere=''){
        $where="";
        foreach($parames as $k=>$v){
            $where.="'".$v."',";
        }
        $where=substr($where, 0,-1);
        if($elseWhere){
            $wheres="";
            foreach($elseWhere as $k1=>$v1){
                $wheres.=" AND ".$k1." = '".$v1."'";
            }
            $sql = " SELECT * FROM ".$this->tablename." WHERE battery_num IN(".$where.") ".$wheres;
        }else{
            $sql = " SELECT * FROM ".$this->tablename." WHERE battery_num IN(".$where.")";
        }
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * where in 更改中间表状态
     * @param batteryArr 电池数组
     * @param update 更改信息
     * @param elseWhere 其他条件
     */
    public function updateInWhereBattery($batteryArr,$update,$elseWhere=''){
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
        if($elseWhere){
            foreach($elseWhere as $k=>$v){
                $wheres.=$k."=".$v.",";
            }
            $wheres=substr($wheres, 0,-1);
            $sql = " UPDATE ".$this->tablename." SET ".$updateData."  WHERE battery_num IN(".$where.") AND ".$wheres;
        }else{
            $sql = " UPDATE ".$this->tablename." SET ".$updateData."  WHERE battery_num IN(".$where.")";
        }
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }
    
    /**
     * 根据条件获取单条用户押金电池中间表信息
     */
    public function getInfoByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /**
     * 根据条件删除电池编号
     */
    public function delectInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " DELETE FROM ".$this->tablename." WHERE `status`=0 ".$where;
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }


}
