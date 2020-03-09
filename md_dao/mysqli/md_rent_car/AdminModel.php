<?php
class AdminModel extends DB_Model
{
    protected $tables = array(
        'role'=>"md_rent_car.md_role",
        'admin_role'=>"md_rent_car.md_admin_role",
        'repair_team'=>"md_survey.md_repair_team",
    );

    public function __construct()
    {
        parent::__construct('md_rent_car', 'md_admin');
        $this->log->log_debug('AdminModel  model be initialized');
    }

    /**
     * [getAdminInfo]
     * 获取管理员信息 ,验证管理员登陆
     */
    public function getUserInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /**
     * 根据类型获取后台用户信息
     */
    public function getAdminByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr[1];
    }
    

    /**
     * 修改管理员数据
     */
    public function updateAdminByAttr($data){
//         $data['update_time']=time();
//         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres['id']=$data['id'];
        unset($data['id']);
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
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE status<2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }


    /**
     * 根据条件获得所有后台用户列表
     */
    public function getAllAdminByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        return $arr;
    }

    /**
     * 添加单条管理员数据
     */
    public function saveAdmin($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 根据条件获取单条管理员数据
     */
    public function getAdminByAttrOne($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /**
     * index页面获取列表数据
     */
    public function indexgetAllAdminByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['admin']='管理员列表';
        return $arr;
    }

    /**
     *  检索工程队用户
     */
    public function selectInsider($data,$limit=''){
        $sql="SELECT * FROM md_admin WHERE status < 2  AND CONCAT(IFNULL(user_name,'"."'),IFNULL(mobile,'')) LIKE '%".$data."%'"." ORDER BY id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }

   /*
    * 后台管理列表及搜索
    * */
   public function getAdminRoleData($limit='',$data)
   {
       $where='';
       empty($data['role_id']) || $where.=" AND AR.role_id=".$data['role_id'];
       if(isset($data['input_data']) && $data['input_data']!=''){
           if(preg_match("/^1[345789]\d{9}$/", $data['input_data'])){
               $where.=" AND A.mobile=".$data['input_data'];
           }else{
               $where.=" AND A.user_name LIKE"."'%". trim($data['input_data']) ."%'";
           }

       }
       $sql="SELECT A.*,R.name as rlname FROM ".$this->tablename." AS A LEFT JOIN ".$this->tables['admin_role']." AS AR ON A.id=AR.admin_id LEFT JOIN ".$this->tables['role']." AS R ON AR.role_id=R.id
        WHERE A.status < 2 ".$where."  ORDER BY A.id DESC ".$limit;
       return $this->getCacheResultArray($sql);
   }

    /**
     *  根据条件模糊查询后台管理员用户
     */
    public function selectAdminInsider($data){
        $sql="select ad.* from md_admin ad left join md_admin_role ro on ad.id = ro.admin_id where ro.role_id in(13,23,28) and ad.status < 2  AND CONCAT(IFNULL(ad.user_name,''),IFNULL(ad.mobile,'')) LIKE '%".$data."%'";
        return $this->getCacheResultArray($sql);
    }







   /*
    * 根据条件获取维修人员信息
    * */
   public function getAdminInfoData($data)
   {
       $where='';
       if(isset($data['user_flag']) && $data['user_flag']!=''){
           $where.=" AND user_flag=".$data['user_flag'];
       }
       empty($data['attr_type']) || $where.=" AND attr_type=".$data['attr_type'];
       if(isset($data['user_name']) && $data['user_name']!=''){
           $where.=" AND user_name LIKE"."'%". trim($data['user_name']) ."%'";
       }
       $sql=" SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." AND attr_type IS NOT NULL ORDER BY id DESC";
       return $this->getCacheResultArray($sql);
   }

   /*
    * 后台维修人员列表
    * */
   public function getBackData($limit,$data)
   {
       $where='';
       empty($data['attr_type']) || $where.=" AND A.attr_type=".$data['attr_type'];
       empty($data['user_flag']) || $where.=" AND A.user_flag=".$data['user_flag'];
       if(isset($data['user_name']) && $data['user_name']!=''){
           if(preg_match("/^1[345678]{1}\d{9}$/",trim($data['user_name']))){
               $where.=" AND A.mobile=".trim($data['user_name']);
           }else{
               $where.=" AND A.user_name LIKE"."'%". trim($data['user_name']) ."%'";
           }

       }

       $sql=" SELECT A.*,R.team_name FROM ".$this->tablename." AS A LEFT JOIN ".$this->tables['repair_team']." AS R ON A.attr_type=R.id  WHERE A.status < 2 AND A.attr_type > 0 ".$where." ORDER BY A.id DESC".$limit;
       return $this->getCacheResultArray($sql);
   }

}

