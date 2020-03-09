<?php
class PledgeOrderModel extends Db_Model {
    protected $tables = array(
        'user'=>'md_lixiang.md_user'
    );

    public function __construct() {
        parent::__construct('md_rent_car','md_pledge_order');
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
     * 根据属性获取该用户押金订单信息
     */
    public function getPledgeOrderByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename."  WHERE 1=1 AND status < 2 AND pay_status=1  ".$where;
        return $this->getCacheRowArray($sql);
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
        $sql=" SELECT P.id,P.pledge_money,P.order_sn,U.name,U.mobile,P.user_type,P.pledge_money_status,P.apply_recede_time FROm 
        ".$this->tablename." AS P LEFT JOIN ".$this->tables['user']." AS u ON U.id=P.user_id WHERE P.status < 2".$where." ORDER BY P.id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }

    public function getPledgeOrder($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND status < 2 AND pay_status=2  ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
    /**
     * 根据条件获取多条条商家数据分页
     */
    public function getPledgeOrderAllList($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND status < 2 AND pay_status=2  ".$where." ORDER BY id DESC "." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }


}
