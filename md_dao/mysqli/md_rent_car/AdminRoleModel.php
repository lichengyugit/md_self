<?php
class AdminRoleModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_rent_car', 'md_admin_role');
        $this->log->log_debug('AdminRoleModel  model be initialized');
    }
    
    /**
     * 根据条件获取单条数据
     */
    public function getAdminRoleByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 修改管理员角色
     */
    public function updateAdminRoleByAttr($data){
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
     * 保存单条权限数据
     */
    public function save($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }

    
}    
