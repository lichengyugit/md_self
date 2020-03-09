<?php
class AuthProjectConfigModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_auth_project_config');
        $this->log->log_debug('AuthMenusModel  model be initialized');
    }


    /*
     *  根据条件获取菜单所有数据
     */
    public function getAllProjectAuth($parames){
        $where="";
        if(!empty($parames)){
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = ".$v;
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }










}
