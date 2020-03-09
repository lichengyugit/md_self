<?php
header("content-type:text/html;charset=utf-8");
class TestModel extends DB_Model
{
    protected $tables = array(
    );

    public function __construct()
    {
        parent::__construct('md_rentcar', 'md_pledge_order');
        $this->log->log_debug('TestModel  model be initialized');
    }


    public function addMalfunctionData($data)
    {
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        return $this->insert($data);
    }

    /**
     * 获取单条信息
     */
    public function getMalfunctionByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 获取多条信息
     */
    public function getMalfunctionsByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC";
        return $this->getCacheResultArray($sql);
    }

    /**
     * 修改用户信息
     */
    public function updateUserByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update >0){
            return true;
        }else{
            return false;
        }
    }



}


