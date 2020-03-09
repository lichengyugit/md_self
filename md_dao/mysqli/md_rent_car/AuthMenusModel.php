<?php
class AuthMenusModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_rent_car', 'md_auth_menus');
        $this->log->log_debug('AuthMenusModel  model be initialized');
    }


    /*
     *  根据条件获取菜单所有数据
     */
    public function getAllAuthMenus($parames){
        $where="";
        if(!empty($parames)){
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = ".$v;
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     *  根据条件获取菜单条数据
     */
    public function getOneAuthMenus($parames){
        $where="";
        if(!empty($parames)){
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = ".$v;
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /*
     *  根据条件获取sort排序后数据
     */
    public function getAllAuthMenusOrder($parames){
        $where="";
        if(!empty($parames)){
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = ".$v;
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where.'order by sort asc';
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

    /**
     * 添加子栏目
     */
    public function saveMenu($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    

    /**
     * 修改权限数据
     */
    public function updateMenuByAttr($data){
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



}
