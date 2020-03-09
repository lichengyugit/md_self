<?php
class CabinetModel extends DB_Model 
{
    protected $tables = array(
      'order'=>'md_lixiang.md_order',
       'order_info'=>'md_lixiang.md_order_info',
       'site'=>'md_lixiang.md_site',
       'company'=>'md_lixiang.md_company',
    );

    public function __construct() {
        parent::__construct($this->dbname,'md_cabinet');
        $this->log->log_debug('CabinetModel  model be initialized');
    }

    /**
     * 获取用户附近机柜位置信息
     */
    public function postLatitude($parames,$type=1,$cabinetType,$esbType)
    {
         //  " AND action = actionGetDistance AND lng = 121.523393 AND lat = 31.272592 AND radius = 2000"
               $radius  = $parames['radius'];
               $data[0]     = $parames['longitude'];
               $data[1]     = $parames['latitude'];
        if($cabinetType==0){
            $where='AND operation_type=1 AND esb_type='.$esbType;
        }else{
            $where='AND esb_type='.$esbType;
        }
        $sql= "SELECT * FROM ".$this->tablename."      
                    WHERE
                     status = 0 ".$where."
                    AND latitude > ? - 1/111*?
                    AND latitude < ? + 1/111*?
                    AND longitude > ? - 1/111*?
                    AND longitude < ? + 1/111*?
                    ORDER BY
                        ACOS(
                            SIN((? * 3.1415) / 180) * SIN((latitude * 3.1415) / 180) + COS((? * 3.1415) / 180) * COS((latitude * 3.1415) / 180) * COS(
        (? * 3.1415) / 180 - (longitude * 3.1415) / 180
                            )
                        ) * 6380 ASC";//
        if($type==1){
            $sql.=" Limit 0,10";
        }
         return $this->getCacheResultArray($sql,array($data[1],$radius,$data[1],$radius,$data[0],$radius,$data[0],$radius,$data[1],$data[1],$data[0]));
    }

    /**
     *  根据条件获取机柜站点
     */
    public  function getSearh($parames,$userType=1,$esbType)
    {  
        $where="";
          //var_dump($parames);die;
        if($parames)
          {
            $where.=" AND cabinet_name like '%".$parames."%' AND esb_type=".$esbType." OR status<2 AND location like '%".$parames."%' AND esb_type=".$esbType;
          }
         if($userType==0){
             $where=" AND operation_type=1 AND esb_type=".$esbType." AND cabinet_name like '%".$parames."%' OR status<2 AND operation_type=1 AND esb_type=".$esbType." AND location like '%".$parames."%'";
         }
         $sql = " SELECT * FROM ".$this->tablename." WHERE status<2 ".$where;
         return $this->getCacheResultArray($sql);

    }
     /**
     * 根据条件获取机柜数量
     */
    /* public function getBoxNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    } */
    
    /**
     * 根据条件获得所有机柜列表
     */
    public function getAllCabinetByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC  LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 根据条件获取集团数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
    
    /**
     * 根据条件获取单条集团数据
     */
    public function getCabinetInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 保存单条集团数据
     */
    public function saveCabinet($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    /**
     * 保存单条集团数据
     */
    public function saveCabinets($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 修改单条集团数据
     */
    public function updateCabinetById($data){
        //         $data['update_time']=time();
        //         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres=array('id'=>$data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * wherein查询获取集团数据
     */
    public function getCabinetWhereIn($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND id  in( ".$where." )  ";
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据where条件修改集团数据
     */
    public function updateCabinetByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据条件获取机柜信息
     */
     public function getAllBoxByAttr($parames){
        $where='';
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM  ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheResultArray($sql);
    } 
    
    /**
     * 根据条件获取机柜信息
     */
    public function getBoxByAttr($parames){
        $where='';
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据ID修改用户信息
     */
    public function updateBox($data){
        $wheres=array('id'=>$data['id']);
        unset($data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    public function getAllBox(){
        $sql="SELECT * FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }

    /**
     * index页面获取列表数据
     */
    public function indexgetAllCabinetByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['cabinet']='机柜列表';
        return $arr;
    }
	
    /**
     * 查询所有机柜编号
     */
    public function getCabinetNum(){
        $sql = " SELECT cabinet_number FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 批量插入机柜数据
     */
    public function insertCabinets($parames){
    $sql=" INSERT IGNORE INTO ".$this->tablename."(`cabinet_number`,`create_time`,`create_date`,`cabinet_name`,`longitude`,`latitude`,`location`,`cabinet_type`,`status`) VALUES ";
        foreach($parames as $k=>$v){
            if($v['port']=='9'){
            	$sql.='("'.$v['cabinetNumber'].'","'.time().'","'.date("Y-m-d H:i:s",time()).'","'.$v['cabinetName'].'","'.$v['longitude'].'","'.$v['latitude'].'","'.$v['location'].'","2","1"),';
            }elseif($v['port']=='12'){
            	$sql.='("'.$v['cabinetNumber'].'","'.time().'","'.date("Y-m-d H:i:s",time()).'","'.$v['cabinetName'].'","'.$v['longitude'].'","'.$v['latitude'].'","'.$v['location'].'","1","1"),';
            }
        }
        $sql=substr($sql,0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }

    /**
     * 检查机柜编号是否存在表内
     */
    public function inspectCabinetNum($parames,$data){
      $sql=' SELECT cabinet_number FROM '.$this->tablename.' WHERE cabinet_number='.'"'.$parames.'"'.' AND cabinet_type='.'"'.$data.'"';
      $result=$this->getCacheRowArray($sql);
      if(!empty($result)){
        return true;
      }else{
        return false;
      }
    }

    /**
     * 更改单个机柜状态
     */
    public function updateCabinet($data){
        if(isset($data['cabinet_number'])){
            $where['cabinet_number']=$data['cabinet_number'];
            unset($data['cabinet_number']);
        }elseif (isset($data['id'])){
            $where['id']=$data['id'];
            unset($data['id']);
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
        if(array_key_exists('cabinet_type',$data)){
            $str.=' AND cabinet_type='.$data['cabinet_type'];
        }
        if(array_key_exists('operation_type',$data)){
            $str.=' AND operation_type='.$data['operation_type'];
        }
        $sql="SELECT * FROM md_cabinet WHERE 1=1 ".$str." AND CONCAT(IFNULL(cabinet_number,'"."'),IFNULL(cabinet_name,'')) LIKE '%".$like."%' ORDER BY id DESC ".$LIMIT;
        $Total=$this->getCacheResultArray($sql);
        return $Total;die;
    }



    /**
     * 连表模糊查询机柜数据
     */
    public function tableQuery($data,$LIMIT=''){
        $like=$data['select'];
        unset($data['select']);
        $str='';
        if(array_key_exists('site_status',$data)){
            $str.=' AND site_status='.$data['site_status'];
        }
        $sql="SELECT * FROM md_cabinet WHERE status < 2 ".$str." AND CONCAT(IFNULL(cabinet_number,'"."'),IFNULL(cabinet_name,''),IFNULL(location,'')) LIKE '%".$like."%'"." ORDER BY id DESC ".$LIMIT;
        return $this->getCacheResultArray($sql);
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
        if(array_key_exists('cabinet_type',$data)){
            $str.=' AND cabinet_type='.$data['cabinet_type'];
        }
        if(array_key_exists('operation_type',$data)){
            $str.=' AND operation_type='.$data['operation_type'];
        }
        $sql="SELECT count(1) as c FROM md_cabinet WHERE 1=1 ".$str." AND CONCAT(IFNULL(cabinet_number,'"."'),IFNULL(cabinet_name,'')) LIKE '%".$like."%' ORDER BY id DESC ";
        return $this->getCacheRowArray($sql)['c'];
    }




    /**
     * 根据时间获取对应的的订单数量
     *@parames     limit      起始位置,显示条数
     *@parames     start_date 开始时间
     *@parames     end_date   结束时间
     *@parames     where      条件
     */
    public function getCabinetData($limit='',$start_date,$end_start,$wheres='')
    {
        $where='';
        if(!empty($wheres['cabinet_number'])){

            if(preg_match("/^[0-9a-zA-Z]{3,10}$/", $wheres['cabinet_number'])){
                $where.=" AND cabinet_number="."'". $wheres['cabinet_number'] ."'";
            }else{
                $where.=" AND  cabinet_name LIKE"."'%". $wheres['cabinet_number'] ."%'";
            }
        }
        empty($wheres['cabinet_type'])   || $where .= " AND cabinet_type  =" . $wheres['cabinet_type'];
        empty($wheres['operation_type']) || $where .= " AND operation_type=" . $wheres['operation_type'];
        empty($wheres['company_id']) || $where .= " AND company_id=" . $wheres['company_id'];
//        $sql="SELECT c.*,o.quantity_number,o.cabinet_id,o.pay_num,o.pay_ment0,o.pay_ment1,o.pay_ment2,o.pay_ment3,o.pay0,o.pay1,o.pay2,o.pay3
//        FROM ".$this->tablename."  AS c LEFT JOIN
//        ( SELECT  count(1) as quantity_number,cabinet_id,SUM(pay)/100 as pay_num ,
//         sum(case when pay_ment = 0 then 1 else 0 end)  as pay_ment0 ,
//         sum(case when pay_ment = 1 then 1 else 0 end)  as pay_ment1 ,
//         sum(case when pay_ment = 2 then 1 else 0 end)  as pay_ment2 ,
//         sum(case when pay_ment = 3 then 1 else 0 end) as  pay_ment3 ,
//         sum(if(pay_ment=0,pay/100,0)) as pay0,
//         sum(if(pay_ment=1,pay/100,0)) as pay1,
//         sum(if(pay_ment=2,pay/100,0)) as pay2,
//         sum(if(pay_ment=3,pay/100,0)) as pay3
//         FROM ".$this->tables['order']."
//        where status=0 and order_status > 1 and order_status!=4 and create_time between ".$start_date." and  ".$end_start." GROUP BY cabinet_id)
//        AS o ON c.cabinet_number=o.cabinet_id WHERE c.status=0  ".$where." ORDER BY quantity_number DESC " .$limit;
        $sql="SELECT c.cabinet_number,c.cabinet_name,c.operation_type,c.cabinet_type,c.status,o.quantity_number,o.pay_ment0,o.pay_ment1,o.pay_ment2,o.pay_ment3
        FROM ".$this->tablename."  AS c LEFT JOIN
        ( SELECT  count(1) as quantity_number,cabinet_id ,
         sum(case when pay_ment = 0 then 1 end)  as pay_ment0 ,
         sum(case when pay_ment = 1 then 1 end)  as pay_ment1 ,
         sum(case when pay_ment = 2 then 1 end)  as pay_ment2 ,
         sum(case when pay_ment = 3 then 1 end)  as pay_ment3
         FROM ".$this->tables['order']."
        where status=0 and order_status > 1 and order_status!=4 and create_time between ".$start_date." and  ".$end_start." GROUP BY cabinet_id)
        AS o ON c.cabinet_number=o.cabinet_id WHERE c.status=0  ".$where." ORDER BY quantity_number DESC " .$limit;
        return $this->getCacheResultArray($sql);
    }

    /*
     * 根据条件获取机柜数量
     * */
    public function getCabinetSum($wheres)
    {
        $where='';
        if(!empty($wheres['cabinet_number'])){
            $where=trim($wheres);
            if(preg_match("/^[0-9a-zA-Z]{3,10}$/", $where)){
                $where.=" AND cabinet_number="."'". $where ."'";
            }else{
                $where.=" AND  cabinet_name LIKE"."'%". $where ."%'";
            }
        }
        empty($wheres['cabinet_type'])   || $where .= " AND cabinet_type  =" . $wheres['cabinet_type'];
        empty($wheres['operation_type']) || $where .= " AND operation_type=" . $wheres['operation_type'];
        empty($wheres['company_id']) || $where .= " AND company_id=" . $wheres['company_id'];
        $sql=" SELECT COUNT(1) FROM ".$this->tablename." WHERE status = 0 ".$where;
        return $this->getCacheRowArray($sql)['COUNT(1)'];
    }


    /*
     * 连表站点
     * */
    public function getCabinetSiteData($limit='',$where)
    {

        //and cabinet_name='宣桥宣中D' company_id=3 and
//        $sql="SELECT *,GROUP_CONCAT(cabinet_number SEPARATOR  ',') as new_cabinet_number FROM md_cabinet WHERE  status = 0".$where."  GROUP BY cabinet_name ORDER BY id DESC".$limit;
        $sql="SELECT S.* FROM ".$this->tablename." AS C LEFT JOIN ".$this->tables['site']." AS S ON C.site_id=S.id WHERE S.status=0 ".$where." GROUP BY C.site_id ORDER BY S.id DESC".$limit;
        return $this->getCacheResultArray($sql);
    }



     /*
      * 连表集团
      * */
     public function getCabinetCompanyData($limit='',$parames='')
     {
         $where='';
         empty($parames['cabinet_type']) || $where.=" AND C.cabinet_type=".$parames['cabinet_type'];
         empty($parames['operation_type']) || $where.=" AND C.operation_type=".$parames['operation_type'];
         empty($parames['company_id']) || $where.=" AND CM.id=".$parames['company_id'];
         if(!empty($parames['create_time'])){
             $str=preg_split('/\s-\s/',$parames['create_time']);
             $strTime=strtotime($str[0]);
             $endTime=strtotime($str[1]);
             $where.=' AND C.create_time>='.$strTime.' AND C.create_time<='.$endTime;
         }
         if(!empty($parames['input_data'])){
             if(preg_match("/^[0-9a-zA-Z]{3,10}$/", $parames['input_data'])){
                 $where.=" AND C.cabinet_number="."'". $parames['input_data'] ."'";
             }else{
                 $where.=" AND  C.cabinet_name LIKE"."'%". trim($parames['input_data']) ."%'";
             }
         }
         if(isset($parames['status']) && $parames['status']!=''){
             $where.=" AND C.status=".$parames['status'];
         }else{
             $where.=" AND C.status < 2";
         }
         $sql="SELECT C.*,CM.name FROM ".$this->tablename." AS C LEFT JOIN ".$this->tables['company']." AS CM ON C.company_id=CM.id WHERE 1=1 ".$where." ORDER BY C.id DESC". $limit;
         return $this->getCacheResultArray($sql);
     }

     /**
      * 根据条件获取机柜信息
      */
     public function getCabinetsByAttr($parames){
         $where='';
         foreach ($parames as $k=>$v){
             $where.=" AND ".$k." = '".$v."'";
         }
         $sql = " SELECT * FROM  ".$this->tablename." WHERE `status` = 0 ".$where;
         return $this->getCacheResultArray($sql);
     }






}

