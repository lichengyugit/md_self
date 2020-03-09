<?php
class PledgeOrderModel extends Db_Model {
    protected $tables = array(
          'user'=>'md_lixiang.md_user'
    );

    public function __construct() {
        parent::__construct($this->dbname,'md_pledge_order');
        $this->log->log_debug('PledgeOderModel  model be initialized');
    }

    /**
     * 增加押金订单
     */
    public function addOrder($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * 根据用户id查询是否存在未支付的押金订单
     */
    public function getOrder($id){
        $sql=" SELECT * FROM ".$this->tablename." WHERE user_id=? AND status=0 ";
        $row=$this->getCacheRowArray($sql,$id);
        return $row;
    }

    /**
     * 根据用户id更改订单信息
     */
    public function updateOrder($data){
        $wheres=array('user_id'=>$data['user_id'],'status'=>0);
        unset($data['user_id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    /**
     * 根据条件更改订单信息
     */
    public function updateOrderByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据条件获得所有退押金用户
     */
    public function getAllPledgeOrderByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where.' ORDER BY apply_recede_time DESC'." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 根据条件获得所有退押金用户
     */
    public function AllPledgeOrderByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1".$where.' ORDER BY apply_recede_time DESC'." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    

    /**
     * 根据属性获取该用户押金订单信息
     */
    public function getPledgeOrderByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    

    /**
     * 根据订单id更改订单信息
     */
    public function updateOrderById($data){
        $wheres=array('id'=>$data['id']);
        unset($data['user_id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }



    /**
     * 根据条件获取用户数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE 1=1 ".$where;

        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /**
     * 所有押金总和
     */
    public function CountPledgeOrderSum(){
        $sql = "SELECT pledge_money FROM ".$this->tablename." WHERE pledge_money_status=0 AND pay_status=1";
        return $this->getCacheResultArray($sql);
    }

    /**
     * 用户退押金数量
     */
    public function getSearchNumByAttr($data){
        $like=$data['select'];
        unset($data['select']);
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT count(1) as c from ( md_pledge_order left join md_user on md_pledge_order.user_id=md_user.id ) left join md_idcard on md_pledge_order.user_id=md_idcard.user_id where 1=1".$where." AND CONCAT(IFNULL(md_idcard.name,'"."'),IFNULL(md_user.mobile,''),IFNULL(md_user.card_number,'')) like '%".$like."%'";
        return $this->getCacheRowArray($sql)['c'];
    }


    /**
     * 用户退押金检索
     */
    public function getSearchDeDepositList($limit,$data){
        $like=$data['select'];
        unset($data['select']);
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT md_pledge_order.id,md_pledge_order.pledge_money,md_pledge_order.order_sn,md_pledge_order.user_id,md_pledge_order.pledge_money_status,md_pledge_order.apply_recede_time,md_idcard.name,md_user.user_name,md_user.mobile,md_user.card_number,md_pledge_order.status from ( md_pledge_order left join md_user on md_pledge_order.user_id=md_user.id ) left join md_idcard on md_pledge_order.user_id=md_idcard.user_id where 1=1 ".$where." AND CONCAT(IFNULL(md_idcard.name,'"."'),IFNULL(md_user.mobile,''),IFNULL(md_user.card_number,'')) like '%".$like."%' ORDER BY apply_recede_time DESC LIMIT ".$limit;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 用户退押金检索
     */
    public function AllDepositList($limit,$data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT md_pledge_order.id,md_pledge_order.pledge_money,md_pledge_order.order_sn,md_pledge_order.user_id,md_pledge_order.pledge_money_status,md_pledge_order.apply_recede_time,md_idcard.name,md_user.user_name,md_user.mobile,md_user.card_number,md_pledge_order.status from ( md_pledge_order left join md_user on md_pledge_order.user_id=md_user.id ) left join md_idcard on md_pledge_order.user_id=md_idcard.user_id where 1=1 ".$where." ORDER BY apply_recede_time DESC LIMIT ".$limit;
        return $this->getCacheResultArray($sql);
    }



    /**
     * 根据条件获取多个押金订单信息
     * @param $field 字段名
     * @param $parame 条件
     */
    public function getPledgeOrderByIn($field,$parames){
        $where="";
        foreach($parames as $k=>$v){
            $where.="'".$v."',";
        }
        $where=substr($where, 0,-1);
        $sql = " SELECT * FROM ".$this->tablename." WHERE ". $field ." IN(".$where.")";
        return $this->getCacheResultArray($sql);
    }

    /*
     * 连表user
     * 根据条件查询用户交押金数据
     * */
    public function getUserPledOrderData($limit='',$data)
    {
        $where='';
        if(isset($data['user_flag'])){
            $where.=' AND U.user_flag='.$data['user_flag'];
        }
        if(!empty($data['create_time'])){
            $str=preg_split('/\s-\s/',$data['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where .=' AND PL.pay_time>='.$strTime.' AND PL.pay_time<='.$endTime;
        }
        $sql="SELECT U.name,U.card_number,U.mobile,U.user_flag,PL.* FROM ".$this->tablename." AS PL LEFT JOIN ".$this->tables['user']. " AS U ON U.id=PL.user_id 
        WHERE PL.status=0 AND PL.pledge_money_status=0 AND PL.pay_status=1 ".$where." ORDER BY PL.id ".$limit;
        return $this->getCacheResultArray($sql);
    }


    /*
     * 根据条件获取所有订单
     * */
    public function getPledgeOrderAll($parames){
        $where="";
        foreach($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." order by id desc";
        return $this->getCacheResultArray($sql);
    }

    public function getPledgeOrderListData($parames,$limit)
    {
        $where='';
        if(isset($parames['user_type']) && $parames['user_type']!=''){$where.=" AND P.user_Type=".$parames['user_type'];}
        if(isset($parames['pay_type'])  && $parames['pay_type']!='' ){$where.=" AND P.pay_type =".$parames['pay_type']; }
        if(isset($parames['pledge_money_status']) && $parames['pledge_money_status']!=''){$where.=" AND P.pledge_money_status=".$parames['pledge_money_status'];}
        if(!empty($parames['pay_time'])){
            $str=preg_split('/\s-\s/',$parames['pay_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where .=' AND P.pay_time>='.$strTime.' AND P.pay_time<='.$endTime;
        }
        if(!empty($parames['apply_recede_time'])){
            $str=preg_split('/\s-\s/',$parames['apply_recede_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where .=' AND P.apply_recede_time>='.$strTime.' AND P.apply_recede_time<='.$endTime;
        }
        if(isset($parames['input_data']) && $parames['input_data']!=''){
            $inputData=trim($parames['input_data']);
            if(preg_match('/^[\x7f-\xff]+$/',$inputData)){
                $where.=" AND  U.name LIKE "."'%". $inputData ."%'";
            }elseif(substr($inputData,0,2)=='CP'){
                $where.=" AND P.order_sn="."'".$inputData."'";
            }elseif (preg_match("/^1[345678]{1}\d{9}$/",$inputData)){
                $where.=" AND U.mobile=".$inputData;
            }
        }
        $sql=" SELECT P.id,P.pledge_money,P.order_sn,U.name,U.mobile,P.user_type,P.pay_type,P.pledge_money_status,P.apply_recede_time,P.pay_date,P.is_bottom FROm 
        ".$this->tablename." AS P LEFT JOIN ".$this->tables['user']." AS u ON U.id=P.user_id WHERE P.status < 2 AND P.pay_status=1 ".$where." ORDER BY P.id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }

}
