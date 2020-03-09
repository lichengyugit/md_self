<?php

class CompanyModel extends Db_Model{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_company');
        $this->log->log_debug('CompanyModel  model be initialized');
    }

    /**
     * 根据条件获得所有集团列表
     */
    public function getAllCompanyByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /*
     * 根据条件获取集团数量
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
     * 根据条件获取单条集团数据
     */
    public function getCompanyInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 保存单条集团数据
     */
    public function saveCompany($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 修改单条集团数据
     */
    public function updateCompanyById($data){
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
    
    /**
     * wherein查询获取集团数据
     */
    public function getCompanyWhereIn($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND id  in( ".$where." )  ";
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据where条件修改集团数据
     */
    public function updateCompanyByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }


    /**
     * index页面获取列表数据
     */
    public function indexgetAllCompayByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['company']='集团列表';
        return $arr;
    }
    
    /**
     * 获得所有集团列表
     */
    public function getAllCompany(){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ";
        return $this->getCacheResultArray($sql);
    }

    /**
     * wherein查询获取集团数据status等于0
     */
    public function getCompanyWhereInApi($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 AND id  in( ".$where." )  ";
        return $this->getCacheResultArray($sql);
    }

    /*
     * 根据条件查询集团数据
     * */
    public function getCompanyAllDatas($parames)
    {
        $where='';
        if(isset($parames['company_name']) && $parames['company_name']!=''){
            $where.=" name LIKE '%".$parames['company_name']."%'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` = 0 ".$where." ORDER BY id DESC";
        return $this->getCacheResultArray($sql,$where);
    }
}
