<?php
class CompanyConfigModel extends Db_Model{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_company_config');
        $this->log->log_debug('CompanyConfigModel  model be initialized');
    }

    /**
     * 查询集团配置表中押金金额
     */
    public function getPledgeMoney($id){
        $sql=" SELECT pledge_money,is_pledge FROM ".$this->tablename." WHERE company_id=? ";
        $row=$this->getCacheRowArray($sql,$id);
        return $row['pledge_money'];
    }
    
    /**
     * 根据条件获得所有集团配置列表
     */
    public function getAllCompanyConfigByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /*
     * 根据条件获取集团配置数量
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
     * 根据条件获取单条集团配置数据
     */
    public function getCompanyConfigInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 保存单条集团配置数据
     */
    public function saveCompanyConfig($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 根据where条件修改单条集团配置数据
     */
    public function updateCompanyConfigByAttr($data,$wheres){
        //         $data['update_time']=time();
        //         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        //$wheres=array('id'=>$data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据id修改单条数据
     */
    public function updateCompanyConfigById($data){
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
