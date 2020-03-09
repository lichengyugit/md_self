<?php
class DepositRefundModel extends Db_Model {
    protected $tables = array(
       'user'=>'md_lixiang.md_user',
       'pledge_order'=>'md_lixiang.md_pledge_order',
    );

    public function __construct() {
        parent::__construct($this->dbname,'md_deposit_refund');
        $this->log->log_debug('DepositRefundModel  model be initialized');
    }

    /**
     * 批量插入数据
     */
    public function insertInfos($parames){
        $sql=" INSERT INTO ".$this->tablename."(`user_id`,`order_id`,`order_sn`,`battery_num`,`recede_money`,`type`,`create_time`,`create_date`) VALUES ";
        foreach($parames as $k=>$v){
            $sql.='("'.$v['userId'].'","'.$v['orderId'].'","'.$v['orderSn'].'","'.$v['batteryNum'].'","'.$v['recedeMoney'].'","'.$v['type'].'","'.time().'","'.date('Y-m-d H:i:s').'","'.$v['pay_type'].'"),';
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
     * 根据条件获取用户押金电池中间表信息
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
     * 根据条件删除退押金记录
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

    /**
     * 根据条件获取用户押金电池中间表单条信息
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
     * 根据条件获取用户数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE status = 0 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }


    /**
     * 根据条件获取用户数量  
     */
    public function getNumByAttrCondition($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }


    /**
     * 用户退押金列表检索
     */
    public function AllDepositList($limit,$data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT us.id,us.name,us.card_number,us.mobile,dr.order_sn,dr.recede_money,dr.create_date,dr.id refundid from md_deposit_refund dr left join md_user us on dr.user_id=us.id where 1 = 1 ".$where." ORDER BY dr.create_time DESC LIMIT ".$limit;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 根据条件获取用户数量
     */
    public function getSearchNumByAttr($data,$search){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM md_deposit_refund dr  left join md_user us on dr.user_id=us.id where  1 = 1 ".$where." and CONCAT(IFNULL(us.name,''),IFNULL(us.card_number,''),IFNULL(us.mobile,'')) like '%".$search."%'";
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /**
     * 用户退押金列表检索
     */
    public function SearchAllDepositList($limit,$data,$search){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT us.id,us.name,us.card_number,us.mobile,dr.order_sn,dr.recede_money,dr.create_date,dr.id refundid from md_deposit_refund dr left join md_user us on dr.user_id=us.id where  1 = 1 ".$where." and CONCAT(IFNULL(us.name,''),IFNULL(us.card_number,''),IFNULL(us.mobile,''),IFNULL(dr.order_sn,'')) like '%".$search."%' ORDER BY dr.create_time DESC LIMIT ".$limit;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 根据属性获取该用户退押金信息
     */
    public function getDepositRefundByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /**
     * 用户退押金列表检索
     */
    public function AllExcelDepositList($data,$search,$time){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }

        $sql="SELECT dr.create_date dcreate_date,st.site_name,us.name,dr.recycle_site_name,us.card_number,us.mobile,dr.recycle_people,us.create_date ucreate_date,dr.recycle_date,dr.refund_reason,dr.battery_num,dr.recede_money,dr.order_sn from md_deposit_refund dr left join md_user us on dr.user_id=us.id LEFT JOIN md_site st on us.site_id=st.id where 1 = 1 ".$where.$time." AND CONCAT(IFNULL(us.name,''),IFNULL(us.card_number,''),IFNULL(us.mobile,''),IFNULL(dr.order_sn,'')) like '%".$search."%' ORDER BY dr.create_time DESC";
        return $this->getCacheResultArray($sql);
    }




    /*
     * 连表user
     * 根据条件查询用户已退款数据
      * */
    public function getUserPledOrderRefundData($limit='',$data)
    {
        $where='';
        if(isset($data['user_flag'])){
            $where.=' AND U.user_flag='.$data['user_flag'];
        }
        if(!empty($data['create_time'])){
            $str=preg_split('/\s-\s/',$data['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where .=' AND DR.create_time>='.$strTime.' AND DR.create_time<='.$endTime;
        }
        if(!empty($data['agree_time'])){
            $str=preg_split('/\s-\s/',$data['agree_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where .=' AND PL.agree_time>='.$strTime.' AND PL.agree_time<='.$endTime;
        }
        $sql="SELECT U.name,U.card_number,U.mobile,U.user_flag,DR.* FROM ".$this->tablename." AS DR LEFT JOIN ".$this->tables['user']. " AS U ON U.id=DR.user_id LEFT JOIN ".$this->tables['pledge_order']."
        AS PL ON PL.id=DR.order_id WHERE  DR.pledge_status=2  ".$where." ORDER BY DR.id ".$limit;
        return $this->getCacheResultArray($sql);
    }
    /*
       * 根据条件获取订单id
       * */
    public function getPledgeOrderId($parames){
        $where="";
        foreach($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT $this->tablename.id FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


   /*
    * 删除数据
    * */
   public function deleteData($data)
   {
       $return = $this->delete($data);
       if($return){
           return true;
       }
       else{
           return false;
       }
   }


}

