<?php
class RoleModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_rent_car', 'md_role');
        $this->log->log_debug('RoleModel  model be initialized');
    }


    /**
     * 根据条件获得所有角色列表
     */
    public function getAllRoleByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     * 根据条件获取角色数量
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
     * 显示指定权限数据
     */
    public function getRoleInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = "."'".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }


    /**
     * 保存单条权限数据
     */
    public function save($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }

    /**
     * 修改权限
     */
    public function updateRoleByAttr($data){
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
    

     /*
      * 新增优惠券配置
     */
    public function addRoleConfig($data)
    {
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }



    /**
     * 根据条件获取所有角色数据
     */
    public function getAllsRoleByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status`<2 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

    /**
     * 根据条件获取单条角色数据
     */
    public function getOneRoleByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 'status'<2 ".$where;
        return $this->getCacheRowArray($sql);
    }

    

}
