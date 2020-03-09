<?php
class AuthModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_rent_car', 'md_auth');
        $this->log->log_debug('AuthModel  model be initialized');
    }


    /**
     * 根据条件获得显示权限列表
     */
    public function getAllAuthByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }


    /*
     * 根据条件获取权限数量
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
     * 根据条件权限数据
     */
    public function getAuthInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }


    /**
     * 修改权限数据
     */
    public function updateAuthByAttr($data){
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
     * 显示可用的顶级权限数据
     */
    public function getRoleInfo(){

        $sql = " SELECT * FROM ".$this->tablename ." WHERE `status` < 2 and pid = 0";
        return $this->getCacheResultArray($sql);
    }

    /**
     * 显示可用且不是顶级权限的权限数据
     */
    public function getRoleChildInfo(){

        $sql = " SELECT * FROM ".$this->tablename ." WHERE `status` < 2 and pid <> 0";
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 添加权限
     */
    public function saveAuth($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 根据条件权限数据(除逻辑删除外)
     */
    public function getAuthInfonNoDel($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status<2 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * where IN 获取数据
     */
    public function getAuthWhereIn($parames){
        $sql=" SELECT * FROM ".$this->tablename." WHERE id IN( ".$parames.")";
        return $this->getCacheResultArray($sql);
    }

    /*
     *  根据条件获取菜单条数据
     */
    public function getOneAuth($parames){
        $where="";
        if(!empty($parames)){
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = ".$v;
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
//        var_dump($sql);die;
        return $this->getCacheRowArray($sql,$where);
    }


    /**
     * 查找手机端权限信息
     */
    public function getPlatfromAuthInfo(){
        $sql = " SELECT * FROM ".$this->tablename ." WHERE `status` < 2 and platform = 1";
        return $this->getCacheResultArray($sql);
    }

    /**
     * 查找手机端仓储用户登录权限信息
     */
    public function getStoragePlatfromAuthInfo(){
        $sql = " SELECT * FROM ".$this->tablename ." WHERE `status` < 2 and platform = 2";
        return $this->getCacheResultArray($sql);
    }



}
