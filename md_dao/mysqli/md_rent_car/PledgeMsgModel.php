<?php
class PledgeMsgModel extends Db_Model {
    protected $tables = array(
    );

    public function __construct() {
        parent::__construct('md_rent_car','md_pledge_msg');
        $this->log->log_debug('PledgeMsgModel  model be initialized');
    }

    /**
     * 增加
     */
    public function addOrderMsg($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }



    /**
     * 更改
     */
    public function updateMsgByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    

    /**
     * 单条信息
     */
    public function getPledgeMsgByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /*
     * 多条信息
     * */
    public function getPledgeMsgAll($parames){
        $where="";
        foreach($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." order by id desc";
        return $this->getCacheResultArray($sql);
    }


}
