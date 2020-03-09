<?php
class SiteModel extends Db_Model{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_site');
        $this->log->log_debug('SiteModel  model be initialized');
    }

    /**
     * 根据条件获得所有站点列表
     */
    public function getAllSiteByAttr($limit,$parames){

        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where.' ORDER BY ID DESC'." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
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
    
    /**
     * 根据条件获取单条站点数据
     */
    public function getSiteInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /**
     * 根据条件获取单条站点数据
     */
    public function getSiteAllotInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 保存单条站点数据
     */
    public function saveSite($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
//        if($insert){
//            return $this->lastInsertId();
//        }else{
//            return false;
//        }
    }
    
    /**
     * 修改单条站点数据
     */
    public function updateSiteById($data){
        $wheres=array('id'=>$data['id']);
        unset($data['id']);
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }else{
            return false;
        }
    }
    
    /**
     * wherein查询获取集团数据
     */
    public function getSiteWhereIn($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND id  in( ".$where." ) "."order by field(`id`,$where)";
        $rs=$this->getCacheResultArray($sql);
       if($rs){
           return $rs;
       }else{
           return false;
       }
    }
    
    /**
     * 根据where条件修改站点数据
     */
    public function updateSiteByAttr($data,$wheres){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 根据条件获取单条站点数据(连表)
     */
    public function getSiteInfoAndAgentByAttr($parames){
        $sql = " SELECT s.*,a.id as aid,a.user_name FROM ".$this->tablename." AS s LEFT JOIN md_admin AS a ON s.agent_id=a.id WHERE 1=1 AND s.id=".$parames;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据条件获取站点数据
     */
    public function getSiteInfo($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` < 2 ".$where."order by id desc";
        return $this->getCacheResultArray($sql,$where);
    }


    /**
     * index页面获取列表数据
     */
    public function indexgetAllSiteByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['site']='站点列表';
        return $arr;
    }


   /**
     * 根据条件获得所有站点列表
     */
    public function getAllSiteAgreeByAttr($parames,$limit=''){
      if($parames){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND id IN(".$parames.")"." ORDER BY ID DESC".$limit;
        return $this->getCacheResultArray($sql);
      }else{
        return '';
      }
    }
   /**
     * 根据条件获得所有站点列表数量
     */
    public function getAllcontSiteAgreeByAttr($parames){
      if($parames){
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 AND id IN(".$parames.")";
        return $this->getCacheResultArray($sql)[0]['c'];
      }else{
        return '';
      }
    }


    /**
      * 根据ID修改站点配置
     */
    public function updateSurvey($data){
        $wheres=array('id'=>$data['id']);
        unset($data['id']);
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    /**
     * wherein查询获取站点数据
     */
    public function getSitesWhereIn($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND agent_id  in( ".$where." )  ";
        return $this->getCacheResultArray($sql);
    }




    /**
     * 连表模糊查询站点数据
     */
    public function tableQuery($data,$LIMIT=''){
        $like=trim($data['select']);
        unset($data['select']);
        $str='';
        if(array_key_exists('site_status',$data)){
            $str.=' AND site_status='.$data['site_status'];
        }
        if(!empty($data['operation_type'])){
            $str.=' AND operation_type='.$data['operation_type'];
        }
        if(isset($data['state']) && $data['state']!=''){
            $str.=" AND state=".$data['state'];
        }
        if(isset($data['company_id']) && $data['company_id']!=''){
            $str.=" AND company_id=".$data['company_id'];
        }
        $sql="SELECT id,site_name,location,site_status,status,open_time,business_start_time,business_end_time,status,mobile,site_principal,operation_type,company_id FROM md_site WHERE status < 2 ".$str." AND CONCAT(IFNULL(site_name,'"."'),IFNULL(location,'')) LIKE '%".$like."%'"." ORDER BY id DESC ".$LIMIT;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 根据搜索条件获取站点数据数量
     */
    public function getSearchCountSiteByAttr($data){
        $like=trim($data['select']);
        unset($data['select']);
        $str='';
        if(array_key_exists('site_status',$data)){
            $str.=' AND site_status='.$data['site_status'];
        }
        if(!empty($data['operation_type'])){
            $str.=' AND operation_type='.$data['operation_type'];
        }
        if(isset($data['state']) && $data['state']!=''){
            $str.=" AND state=".$data['state'];
        }
        if(isset($data['company_id']) && $data['company_id']!=''){
            $str.=" AND company_id=".$data['company_id'];
        }
        $sql="SELECT count(1) as c FROM md_site WHERE status < 2  ".$str." AND CONCAT(IFNULL(site_name,'"."'),IFNULL(location,'')) LIKE '%".$like."%'";
        return $this->getCacheRowArray($sql)['c'];
    }
       
    /**
     * 根据条件获得多条微信主页站点数据
     */
    public function getMoreSiteByAttr($parames){
        $type=0;
        if(isset($parames['longitude']) && $parames['longitude']==1){
            unset($parames['longitude']);
            $type=1;
        }
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        if($type){
            $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 AND longitude is not null ".$where;
        }else{
            $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0  ".$where;
        }
        return $this->getCacheResultArray($sql,$where);
    }


    /*
     * 根据条件获取站点信息
     */
    public function getSiteData($limit='',$parames='')
    {
        $where='';
        if(!empty($parames['create_time'])){
            $str=preg_split('/\s-\s/',$parames['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND create_time>='.$strTime.' AND create_time<='.$endTime;
        }
        empty($parames['company_id']) || $where.=" AND company_id=".$parames['company_id'];
        empty($parames['site_name']) || $where .= " AND site_name LIKE "."'%". trim($parames['site_name']) ."%'" ;
        empty($parames['site_status']) || $where .= " AND site_status =".$parames['site_status'] ;
        $sql = "SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }


    /*
     *   根据条件连表查询勘测列表
     */
    public function getSurveySiteList($limit='',$parames='',$search=''){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT st.id,st.site_name,st.site_status,st.location,st.mobile,st.status,st.state from md_site st left join md_survey.md_survey_project pr on st.id = pr.site_id where st.status = 0 ".$where.$search." ORDER BY st.create_time DESC LIMIT ".$limit;
        return $this->getCacheResultArray($sql);
    }

    /*
     *   根据条件连表查询勘测列表数量
     */
    public function getSurveySiteListNum($parames='',$search=''){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql="SELECT count(1) as c FROM md_site st left join md_survey.md_survey_project pr on st.id = pr.site_id where st.status = 0 ".$where.$search;
        return $this->getCacheRowArray($sql)['c'];
    }



    /*
     *  查询勘测系统出库耗材订单
     */
    public function getConsOutbound($limit='',$parames=''){
      $where="";
      foreach ($parames as $k=>$v){
          $where.=" AND ".$k." = '".$v."'";
      }
      $sql="SELECT st.id site_id,od.id order_id,st.site_name,st.location,st.set_site_number,st.set_battery_number,st.site_principal,st.mobile,from_unixtime(st.open_time,'%Y-%m-%d %H:%i:%s') open_time,st.create_date,od.order_status FROM md_site st left join md_storage_order od on st.id = od.site_id where st.status = 0 ".$where." ORDER BY st.create_time DESC LIMIT ".$limit;
      return $this->getCacheResultArray($sql);
    }


    /*
     *  查询勘测系统出库耗材订单
     */
    public function getConsOutboundNum($parames=''){
      $where="";
      foreach ($parames as $k=>$v){
          $where.=" AND ".$k." = '".$v."'";
      }
      $sql="SELECT count(1) as c FROM md_site st left join md_storage_order od on st.id = od.site_id where st.status = 0 ".$where;
      return $this->getCacheRowArray($sql)['c'];
    }



    /**
     * 根据条件获得所有站点列表
     */
    public function getAllSiteLikeByAttr($limit,$parames='',$search=''){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where.$search.' ORDER BY ID DESC'." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     * 根据条件获取站点数量
     */
    public function getNumLikeByAttr($parames='',$search=''){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = "."'".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where.$search;
        return $this->getCacheRowArray($sql,$where)['c'];
    }


}


