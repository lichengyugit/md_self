<?php
class IdCardModel extends Db_Model{
    protected $tables = array(

    );
    public function __construct() {
        parent::__construct($this->dbname,'md_idcard');
        $this->log->log_debug('IdCardModel  model be initialized');
    }

    /**
     * 添加用户身份证信息
     */
    public function addIdCard($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $insert=$this->insert($data);
        $row = $this->lastInsertId();
        return $row;
    }
    
    /**
     * 根据属性获取单用户身份信息
     */
    public function getUserIdCardByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据条件获取数据数量
     */
    public function getIdCardByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }


    /**
     * 根据ID修改用户身份信息
     */
    public function updateUserIdCard($data){
        $wheres=array('id'=>$data['id']);
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
     * in查询
     */
    public function getIn($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=','.$v;
        } 
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id IN (".substr($where, 1).")";
        return $this->getCacheResultArray($sql);
    }


    /**
     * 根据条件获得所有用户列表
     */
    public function getAllIdCardByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     * 根据条件修改信息
     * */
    public function editIdCardData($data,$where)
    {
        $update=$this->update($data, $where);
        if($update >0 ){
            return $update;
        }
        else{
            return false;
        }
    }

}
