<?php
class CardPaymentModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_card_payment');
        $this->log->log_debug('CardPaymentModel  model be initialized');
    }


    /**
     * 新增月卡订单
     */
    public function insertCardPayment($data){
        $time=time();
        $data['create_date']=date("Y-m-d H:i:s",$time);
        $data['create_time']=$time;
        $data['balance_deduction']=$data['price']-$data['payment_amount'];
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 根据条件查询月卡订单
     */
    public function getCardConfigByAttr($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where.' ORDER BY create_time desc';
        return $this->getCacheResultArray($sql);
    }

    /**
     * 根据条件获取月卡数量
     */
    public function getCardConfigNumByAttrNum($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }


    /**
     * 收银订单金额搜索
     */
    public function StatisticsSearch($strTime,$endTime,$type=''){
        $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime.$type;
        $sql="SELECT user_type,payment_amount FROM md_card_payment WHERE pay_status=1 ".$str;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 根据条件查询月卡订单带分页
     */
    public function getCardConfigByAttrs($limit,$data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where.' ORDER BY create_time DESC LIMIT '.$limit;
        return $this->getCacheResultArray($sql);
    }

    /**
      * 获取所有订单信息
      */
    public function getMonthCardOrder($limit='',$wheres='')
    {
        $where='';
        $str='';
        empty($wheres['card_type'])   || $where.=" AND card_type =".$wheres['card_type'];
        if(isset($wheres['pay_status']) && $wheres['pay_status']==0){
            $where.=" AND pay_status =".$wheres['pay_status'];
        }
        if(isset($wheres['user_type'])&& $wheres['user_type']!=''){
            $where.=" AND user_type =".$wheres['user_type'];
        }
        if(!empty($wheres['start_time'])){
            $str=preg_split('/\s-\s/',$wheres['start_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND start_time >='.$strTime.' AND start_time <='.$endTime;
        }
        if(!empty($wheres['over_time'])){
        $str=preg_split('/\s-\s/',$wheres['over_time']);
        $strTime=strtotime($str[0]);
        $endTime=strtotime($str[1]);
        $where.=' AND  over_time >='.$strTime.' AND over_time <='.$endTime;
        }
        if(!empty($wheres['create_time'])){
        $str=preg_split('/\s-\s/',$wheres['create_time']);
        $strTime=strtotime($str[0]);
        $endTime=strtotime($str[1]);
        $where.=' AND  create_time >='.$strTime.' AND create_time <='.$endTime;
        }
        if(!empty($wheres['input_data'])){
            if(is_numeric($wheres['input_data'])){
                $where.=" AND user_mobile = ".$wheres['input_data'];
            }else{
                $where.=" AND user_name = "."'".trim($wheres['input_data'])."'";
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where.' ORDER BY id desc '.$limit;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 多功能订单统计  FlexibleSearch
     * @param $type         统计类型
     * @param $strTime      开始时间
     * @param $endTime      结束时间
     * @param $UserType     用户类型
     */
    public function FlexibleSearch($type,$strTime,$endTime,$UserType=''){
        $TimeRange=' AND create_time>'.$strTime.' AND create_time<'.$endTime.$UserType;
        $sql="SELECT DATE_FORMAT(create_date,'".$type."') as time,sum(payment_amount) money FROM md_card_payment WHERE pay_status=1 ".$TimeRange." GROUP BY time";
        return $this->getCacheResultArray($sql);
    }









}







