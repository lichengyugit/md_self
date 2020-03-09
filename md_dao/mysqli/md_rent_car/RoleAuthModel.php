<?php
class RoleAuthModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_rent_car', 'md_role_auth');
        $this->log->log_debug('RoleAuthModel  model be initialized');
    }
    
    public function updateRoleAuthByAttr($data){    // 修改角色权限
//         $data['update_time']=time();
//         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
           $wheres=array('role_id'=>$data['id']);
          unset($data['id']);
         // var_dump($wheres);die;
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据条件删除权限
     */
    public function delRoleAuthByAttr($where){
        $del=$this->deleteBath($where);
        return $del;
    }
    
    /**
     * 批量新增权限
     */
    public function saveRoleAuth($data){
        $save = $this->insertBatch($data);
        return $save;
    }
    
    /**
     * 根据条件获取数据
     */
    public function getRoleAuthByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
}
