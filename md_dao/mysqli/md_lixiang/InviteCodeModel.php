<?php
class InviteCodeModel extends Db_Model{
    protected $tables = array(
       'idCard'=>'md_lixiang.md_idcard',
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_invite_code');
        $this->log->log_debug('InviteCodeModel  model be initialized');
    }

    
    /**
     * 根据条件获取单条数据
     */
    public function getInviteCodeByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".'"'.$v.'"';
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 保存单条数据
     */
    public function saveInviteCode($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 根据条件获取多条数据(非连表)
     */
    public function getInviteCodeByAttrs($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".'"'.$v.'"';
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 根据条件获取多条数据(连表)
     */
    public function getInviteCodesByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT i.*,d.name,d.card_number FROM ".$this->tablename." AS i LEFT JOIN ".$this->tables['idCard']." AS d ON i.user_id=d.user_id WHERE 1=1 ".$where." ORDER BY i.id DESC";
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 根据ID修改用户信息
     */
    public function updateInviteCode($data){
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
     * 根据任意条件修改用户信息
     */
    public function updateWheresInviteCode($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
}
