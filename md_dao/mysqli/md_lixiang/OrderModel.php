<?php
class OrderModel extends DB_Model
{
    protected $tables = array(
    'order_info'=>'md_lixiang.md_order_info',
    'cabinet'=>'md_lixiang.md_cabinet'
     );

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_order');
        $this->log->log_debug('OrderModel  model be initialized');
    }



    /**
     * 根据属性获取该用户订单信息
     */
    public function getOrderByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
          return $this->getCacheRowArray($sql);

    }


    /**
     * 显示用户可选支付方式
     *
     */
    public function  paytype()
    {
        $sql = " SELECT * FROM ".$this->tablename;
        return $this->getCacheRowArray($sql);
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function getTopUpInfoById($id){
        $sql = " SELECT * FROM ".$this->tablename." WHERE id=".$id;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据条件获取订单数量
     */
    public function getCountOrderByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
                                                                                                                                                                                                                                                                                                                                                   
    /**
     * 根据条件获得所有订单列表
     */
    public function getAllOrder($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM  ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 根据id修改订单状态
     */
    public function updateOrder($parames){
        $where['id']=$parames['id'];
        unset($parames['id']);
        $update=$this->update($parames, $where);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    public function getAllOrderPage($parames,$page=1, $pageSize=10)
    {
       /*  $where="";
        foreach($parames as $k=>$v){
            $where.= " AND ".$k." = '".$v."'";
        } */
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id=".$parames;   //  查询用户账单总记录条数
        $row = $this->getCacheResultArray($sql);
        $numpages = ceil(count($row )/$pageSize);          //计算总页数:向上取整；
        $page  = empty($page)? 1:$page;                 //页码              
        //判断页码越界
        if($page>$numpages){
            $page=$numpages;
        }
        if($page<1){         
            $page=1;
        }
        $pagesize = ($page-1) * $pageSize; //起始条数
        $sql = " SELECT o.*,i.location,i.pay FROM ".$this->tablename." AS o INNER JOIN md_order_info AS i ON o.id=i.order_id  WHERE o.user_id=".$parames." ORDER BY o.id DESC LIMIT ?,?";
         $arr=$this->getCacheResultArray($sql,array($pagesize, $pageSize));
         $arr['numpages']=$numpages;
         return $arr;
    }
    
    /**
     * 根据条件获取用户当天订单数量
     */
    public function getUserOrderNum($parames,$type=1){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $date=strtotime(date('Y-m-d',time()));
        //$sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 AND `pay_date` like "."'".$date."%'".$where;
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 AND `order_status` in(2,3) AND `pay_ment` in(1,2,4)  AND `create_time` >= ".$date.$where;
        if($type==2){
            $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 AND `order_status` in(2,3)  AND `create_time` >= ".$date.$where;
        }
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /**
     * 新增订单
     */
    public function insertOrder($data){
        $time=time();
        $data['create_date']=date("Y-m-d H:i:s",$time);
        $data['create_time']=$time;
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
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
     * 查询用户倒数第二条订单
     */
    public function getUserDescTwoOrder($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE order_status=2 ".$where." ORDER BY id DESC LIMIT 0,1";
        return $this->getCacheRowArray($sql);
    }

    /**
     * index页面获取列表数据
     */
    public function indexgetAllOrderByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC  LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['order']='订单列表';
        return $arr;
    }

    /**
     * 连表模糊查询用户数据
     */
    public function returntable($data,$LIMIT=''){
        $like=$data['select'];
        unset($data['select']);
        if(!empty($data['time'])){
            $str=preg_split('/\s-\s/',$data['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
        }else{
            $str='';
        }
        if(array_key_exists('order_type',$data)){
            $str.=' AND order_type='.$data['order_type'];
        }
        if(array_key_exists('order_status',$data)){
            $str.=' AND order_status='.$data['order_status'];
        }
        if(array_key_exists('pay_status',$data)){
            $str.=' AND pay_status='.$data['pay_status'];
        }
        if(array_key_exists('pay_ment',$data)){
            $str.=" AND pay_ment='".$data['pay_ment']."'";
        }
        $sql="SELECT id,order_sn,user_name,battery_id,cabinet_id,order_status,pay_status,pay,pay_ment,create_date,pay_date,user_id FROM md_order WHERE 1=1 ".$str." AND CONCAT(IFNULL(order_sn,'"."'),IFNULL(battery_id,''),IFNULL(cabinet_id,''),IFNULL(user_name,''),IFNULL(user_mobile,'')) LIKE '%".$like."%' ORDER BY id DESC ".$LIMIT;
        $Total=$this->getCacheResultArray($sql);
        return $Total;die;
    }

    /**
     * 根据搜索条件获取订单数量
     */
    public function getSearchCountOrderByAttr($data){
        $like=$data['select'];
        unset($data['select']);
        if(!empty($data['time'])){
            $str=preg_split('/\s-\s/',$data['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
        }else{
            $str='';
        }
        if(array_key_exists('order_type',$data)){
            $str.=' AND order_type='.$data['order_type'];
        }
        if(array_key_exists('order_status',$data)){
            $str.=' AND order_status='.$data['order_status'];
        }
        if(array_key_exists('pay_status',$data)){
            $str.=' AND pay_status='.$data['pay_status'];
        }
        if(array_key_exists('pay_ment',$data)){
            $str.=" AND pay_ment='".$data['pay_ment']."'";
        }
        $sql="SELECT count(1) as c FROM md_order WHERE 1=1 ".$str." AND CONCAT(IFNULL(order_sn,'"."'),IFNULL(battery_id,''),IFNULL(cabinet_id,''),IFNULL(user_name,''),IFNULL(user_mobile,'')) LIKE '%".$like."%' ORDER BY id DESC ";
        return $this->getCacheRowArray($sql)['c'];
    }
       

    /**
     * 收银订单金额搜索
     */
    public function StatisticsSearch($strTime,$endTime,$type=''){
        $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime.$type;
        $sql="SELECT order_type,pay FROM md_order WHERE pay_status=2 AND (pay_ment!=0 or pay_ment!=3) AND order_status=3".$str;
        return $this->getCacheResultArray($sql);
    }


    /*
   * 查询历史订单 年月
    * @parames     $where[
    *                    cabinet_number    机柜编号
    *                    startTime         开始时间戳
    *                    endTime           结束时间戳
    *                    ]
   */
//    public function HistoryOrder($parames,$startTime,$endTime){
//        $where='';
//        if(!empty($parames['cabinet_number'])){
//            $where.=" AND cabinet_id=" ."'". $parames['cabinet_number'] ."'";
//        }
//        empty($parames['company_id']) || $where .= " AND cabinet_id in (SELECT cabinet_number FROM ".$this->tables['cabinet']." WHERE company_id=".$where['company_id']." and status=0)";
//        $sql=" SELECT create_date FROM ".$this->tablename." WHERE status=0 AND order_status  BETWEEN 2 AND 3 ".$where." AND create_time BETWEEN ".$startTime." AND ".$endTime;
//        return $this->getCacheResultArray($sql);exit;
//    }

//    /*
//    * 查询历史订单 年月
//     * @parames     $where[
//     *                    cabinet_number    机柜编号
//     *                    the_date          所需时间格式
//     *                    max_data          日期最大值
//     *                    con_date          日期条件格式
//     *                    date              日期条件    如2018   201810  20181011
//     *                    ]
//    */
    public function HistoryOrder($where){
        $wheres='';
        if(!empty($where['cabinet_number'])){
            $wheres.=" AND cabinet_id=" ."'". $where['cabinet_number'] ."'";
        }
        if(empty($where['max_data'])){ $where['max_data']=""; }

        empty($where['company_id']) || $wheres .= " AND cabinet_id in (SELECT cabinet_number FROM ".$this->tables['cabinet']." WHERE company_id=".$where['company_id']." and status=0)";
//        $sql=" SELECT count(id) as order_now,create_date FROM ".$this->tablename." WHERE status=0 AND order_status BETWEEN 2 AND 3".$where." GROUP BY create_date";
        $sql="SELECT count(1) as order_now,date_format(create_date,'".$where['the_date']."') dates".$where['max_data']." FROM ".$this->tablename."
        WHERE date_format(create_date,'".$where['con_date']."')=".$where['date']."
        AND order_status > 1".$wheres." AND order_status < 4 AND status=0 GROUP BY dates order by dates asc";
        return $this->getCacheResultArray($sql);exit;
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
        $sql="SELECT DATE_FORMAT(create_date,'".$type."') as time,sum(pay) money FROM md_order WHERE pay_status=2 AND order_status=3 ".$TimeRange." GROUP BY time";
        return $this->getCacheResultArray($sql);
    }

    
    /**
     * 用户三个月内订单列表
     * @param number $parames 用户Id
     * @param number $time 时间
     * @param number $page    页数
     * @param number $pageSize 
     */
    public function getUserOrderList($parames,$time,$page=1, $pageSize=10)
    {
        /*  $where="";
         foreach($parames as $k=>$v){
         $where.= " AND ".$k." = '".$v."'";
         } */
        $sql = " SELECT COUNT(1) FROM ".$this->tablename." WHERE create_time > ?  AND user_id = ?";   //  查询用户账单总记录条数
        $row = $this->getCacheRowArray($sql,array($time,$parames))['COUNT(1)'];
        $numpages = ceil($row/$pageSize);          //计算总页数:向上取整；
        $page  = empty($page)? 1:$page;                 //页码
        //判断页码越界
        if($page>$numpages){
            $page=$numpages;
        }
        if($page<1){
            $page=1;
        }
        $start = ($page-1) * $pageSize; //起始条数
        $sql = " SELECT o.*,i.location,i.pay FROM ".$this->tablename." AS o INNER JOIN md_order_info AS i ON o.id=i.order_id  WHERE o.user_id=? AND o.create_time>? ORDER BY o.id DESC LIMIT ?,?";
        $arr=$this->getCacheResultArray($sql,array($parames,$time,$start, $pageSize));
        $arr['numpages']=$numpages;
        return $arr;
    }



   /*
    * 根据时间获取订单信息
    * */
   public function getCondOrderData($startTime,$endTime)
   {
       $sql=" SELECT sum(pay) AS money FROM ".$this->tablename." WHERE status=0 AND pay_status=2 AND order_status=3 AND pay_ment!=0 AND create_time BETWEEN ".$startTime." AND ".$endTime;
       return $this->getCacheRowArray($sql)['money'];
   }


   /*
    * 订单列表与搜索
    * */
    public function getOrderList($parames,$limit='')
    {
        $where='';
        $whereAttr='';
        if(!empty($parames['user_id'])){
            $where.=" AND O.user_id=".$parames['user_id'];
            $whereAttr.=" AND user_id=".$parames['user_id'];
        }
        if(!empty($parames['order_status'])){
            $where.=" AND O.order_status=".$parames['order_status'];
            $whereAttr.=" AND order_status=".$parames['order_status'];
        }
        if(!empty($parames['pay_status'])){
            $where.=" AND O.pay_status=".$parames['pay_status'];
            $whereAttr.=" AND pay_status=".$parames['pay_status'];
        }
//        empty($parames['order_status']) || $where.=" AND O.order_status=".$parames['order_status']; $whereAttr.=" AND order_status=".$parames['order_status'];
//        empty($parames['pay_status']) || $where.=" AND O.pay_status=".$parames['pay_status'];       $whereAttr.=" AND pay_status=".$parames['pay_status'];
        if(isset($parames['order_type']) && $parames['order_type']!=''){
            $where    .=" AND O.order_type=".$parames['order_type'];
            $whereAttr.=" AND order_type=".$parames['order_type'];
        }
        if(isset($parames['pay_ment']) && $parames['pay_ment']!=''){
            $where    .=" AND O.pay_ment=".$parames['pay_ment'];
            $whereAttr.=" AND pay_ment=".$parames['pay_ment'];
        }
        if(isset($parames['company_id']) && $parames['company_id']!=''){
            $where    .=" AND O.company_id=".$parames['company_id'];
            $whereAttr.=" AND company_id=".$parames['company_id'];
        }
        if(!empty($parames['create_time'])){
            $str=preg_split('/\s-\s/',$parames['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where    .=' AND O.create_time>='.$strTime.' AND O.create_time<='.$endTime;
            $whereAttr.=' AND   create_time>='.$strTime.' AND create_time<='.$endTime;
        }
        if(isset($parames['input_data']) && $parames['input_data']!=''){
            $inputData=trim($parames['input_data']);
            if(preg_match('/^[\x7f-\xff]+$/',$inputData)){
                $where    .=" AND  user_name = "."'".$inputData."'";
                $whereAttr.=" AND  user_name = "."'". $inputData ."'";
            }elseif(preg_match( '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{13}$/',$inputData)){
                $where    .=" AND O.battery_id="."'".$inputData."'";
                $whereAttr.=" AND battery_id="."'".$inputData."'";
            }elseif (preg_match('/^(\b[0-9a-zA-Z]{8}\b[^0-9a-zA-Z]?)+$/',$inputData)){
                $where    .=" AND O.cabinet_id="."'".$inputData."'";
                $whereAttr.=" AND cabinet_id="."'".$inputData."'";
            }elseif(substr($inputData,0,2)=='XO'){
                $where    .=" AND O.order_sn="."'".$inputData."'";
                $whereAttr.=" AND order_sn="."'".$inputData."'";
            }elseif(preg_match("/^1[345678]{1}\d{9}$/",$inputData)){
                $where    .=" AND user_mobile=".$inputData;
                $whereAttr.=" AND user_mobile=".$inputData;
            }
        }
        $sql=" SELECT O.*,OI.return_battery_id FROM ".$this->tablename." AS O LEFT JOIN ".$this->tables['order_info']." AS OI ON O.id=OI.order_id WHERE
         O.id<=(SELECT id FROM ".$this->tablename." WHERE status<2 ".$whereAttr." ORDER BY id DESC LIMIT ".$limit.",1) AND O.status<2  ".$where." ORDER BY O.id DESC LIMIT 15";
        return $this->getCacheResultArray($sql);
    }

    /*
    * 订单列表数量
    * */
    public function getOrderCount($parames)
    {
        $where='';
        empty($parames['order_status']) || $where.=" AND order_status=".$parames['order_status'];
        empty($parames['user_id'])    || $where.=" AND user_id=".$parames['user_id'];
        empty($parames['pay_status']) || $where.=" AND pay_status=".$parames['pay_status'];
        if(isset($parames['order_type']) && $parames['order_type']!=''){
            $where.=" AND order_type=".$parames['order_type'];
        }
        if(isset($parames['pay_ment']) && $parames['pay_ment']!=''){
            $where.=" AND pay_ment=".$parames['pay_ment'];
        }
        if(isset($parames['company_id']) && $parames['company_id']!=''){
            $where.=" AND company_id=".$parames['company_id'];
        }
        if(!empty($parames['create_time'])){
            $str=preg_split('/\s-\s/',$parames['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND create_time>='.$strTime.' AND create_time<='.$endTime;
        }
        if(isset($parames['input_data']) && $parames['input_data']!=''){
            $inputData=trim($parames['input_data']);
            if(preg_match('/^[\x7f-\xff]+$/',$inputData)){
                $where.=" AND  user_name = "."'".$inputData."'";
            }elseif(preg_match( '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{13}$/',$inputData)){
                $where.=" AND battery_id="."'".$inputData."'";
            }elseif (preg_match('/^(\b[0-9a-zA-Z]{8}\b[^0-9a-zA-Z]?)+$/',$inputData)){
                $where.=" AND cabinet_id="."'".$inputData."'";
            }elseif(substr($inputData,0,2)=='XO'){
                $where.=" AND order_sn="."'".$inputData."'";
            }elseif(preg_match("/^1[345678]{1}\d{9}$/",$inputData)){
                $where.=" AND user_mobile=".$inputData;
            }
        }
        $sql=" SELECT count(1) FROM ".$this->tablename."  WHERE status<2  ".$where;
        return $this->getCacheRowArray($sql)['count(1)'];
    }
}





















