<?php
header("content-type:text/html;charset=utf-8");
class SurveyProjectModel extends DB_Model
{
    protected $tables = array(
//        'site'=>'test_md_lixiang.md_site'
        'site'=>'md_lixiang.md_site'
    );

    public function __construct()
    {
        parent::__construct('md_survey', 'md_survey_project');
        $this->log->log_debug('SurveyInfoModel  model be initialized');
    }

   //根据条件获取工程表所有信息
   public function getProjectAll($parames)
   {
       $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = '".$v."'";
       }
       $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where."order by id desc";
       return $this->getCacheResultArray($sql);
   }
   
   //根据id获取单条站点表信息
   public function getSurveyInfo($parames)
   {      
       $parames['site_id']=$parames['id'];
       unset($parames['id']);
       $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
       }
       $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
       return $this->getCacheRowArray($sql);
   }
   
   //修改任务表状态
   public function updateSurveyState($data)
   {
       $wheres=array('site_id'=>$data['id']);
       unset($data['id']);
       $data['create_time']=time();
       $data['create_date']=date('Y-m-d H:i:s',time());
       $update=$this->update($data, $wheres);
       if($update){
           return $update;
       }else{
           return false;
       }
   }

   /*
   **   根据id将状态改为需勘测
    */
   public function insertState($parames){
       $parames['create_time']=time();
       $parames['create_date']=date('Y-m-d H:i:s',time());
      $update=$this->insert($parames);
      if($update){
        return true;
      }else{
        return false;
      }
   }


    /*
    **  根据条件获取 勘测已完成 需要审核 工程表信息
     */
   public function getProjectAgree($parames)
   {
       $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
       }
       $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
       $arr=$this->getCacheResultArray($sql);
       $id=array();
       foreach ($arr as $key => $value) {
            $id[$key]=$value['site_id'];
       }
       $ID=implode(',', $id);
       return $ID;
   }

    /*
    **  根据条件获取 施工已完成 需要审核 工程表信息
     */
   public function getProjectAgreeConc($parames)
   {
       $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
       }
       $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 AND state=2 ".$where;
       $arr=$this->getCacheResultArray($sql);
       $id=array();
       foreach ($arr as $key => $value) {
            $id[$key]=$value['site_id'];
       }
       $ID=implode(',', $id);
       return $ID;
   }

   /*
   **根据工程id获取站点id
    */
  public function getSiteId($date)
  {
    $parames['id']=$date['id'];
    $where="";
    foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
    }
    $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
    return $this->getCacheRowArray($sql);
  }

   /*
    **  修改状态
     */
   public function updataState($parames,$wheres){
       $parames['create_time']=time();
       $parames['create_date']=date('Y-m-d H:i:s',time());
      $update=$this->update($parames,$wheres);
      if($update){
        return true;
      }else{
        return false;
      }
   }

    /*
     *  获取勘测需审核列表
     */
    public function getAgreeDetails($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        $arr=$this->getCacheResultArray($sql,$where);
        return $arr;
    }

    /**
     * wherein查询获取集团数据
     */
    public function getSiteWhereIn($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND site_id  in( ".$where." )  ";
        return $this->getCacheResultArray($sql);
    }

    /*
     * 根据条件获取站点数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = "."'".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /*
     * 勘测系统列表连表(site)  接口用
     * */
    public function getProJoinSiteList($parames,$page=1,$pageSize=10)
    {
        $where='';
        empty($parames['user_id']) || $where.=" AND user_id=".$parames['user_id'];
        empty($parames['operation_type']) || $where.=" AND operation_type=".$parames['operation_type'];
        empty($parames['attr_id']) || $where.=" AND P.team_id=".$parames['attr_id'];
        empty($parames['site_name']) || $where.=" AND  site_name LIKE"."'%". $parames['site_name'] ."%'";
        if(isset($parames['search_state']) && $parames['search_state']!=''){
            if($parames['search_state']=='10'){
                $where.=" AND S.state=0";
            }elseif($parames['search_state']=='11'){
                $where.=" AND S.state=5";
            }elseif($parames['search_state']=='12'){
                $where.=" AND ISNULL(P.audit) AND S.state=1";
            }else{
                $where.=" AND P.audit=".$parames['search_state'];
            }
        }
//        if(!empty($parames['state'])){
//            if($parames['state']==2){
//                $where.=" AND P.state=2 AND P.audit!=9";
//            }else{
             empty($parames['state']) || $where.=" AND P.state in(".$parames['state'].")";
//            }
//        }
        $sql = " SELECT COUNT(1) FROM ".$this->tables['site']." AS S LEFT JOIN ".$this->tablename." AS P ON S.id=P.site_id WHERE S.status=0 ".$where;   //  查询站点符合条件总数
        $row = $this->getCacheRowArray($sql)['COUNT(1)'];
        $numpages = ceil($row/$pageSize);          //计算总页数:向上取整；
        $page  = empty($page)? 1:$page;                 //页码
        //判断页码越界
        if($page>$numpages){
            $page=$numpages;
        }
        if($page<1){
            $page=1;
        }
        $pagesize = ($page-1) * $pageSize; //起始条数
        $sql2=" SELECT S.*,P.state AS prostate,P.audit,P.survey_state FROM ".$this->tables['site']." AS S LEFT JOIN ".$this->tablename." AS P ON S.id=P.site_id WHERE S.status=0 ".$where . " ORDER BY S.id DESC LIMIT ".$pagesize.','.$pageSize;
        $siteData=$this->getCacheResultArray($sql2);
        $data=[
            'siteData'=>$siteData,  //站点数据
            'pageSize'=>$numpages,  //总页数
            'size'    =>$row,       //总条数
        ];
        return $data;
    }

}
