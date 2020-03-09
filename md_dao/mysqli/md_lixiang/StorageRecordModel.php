<?php
class StorageRecordModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_record');
        $this->log->log_debug('StorageRecordModel  model be initialized');
    }


    //   -------------[增:]   
    /**
     * [addRecord 根据data新增数据]
     * @return [type] [bool]
     */
    public function addRecord($data){
    	$data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
    	$insert=$this->insert($data);
        return $this->lastInsertId();
    }







 
    //   -------------[删:]   





    //   -------------[改:]   

    /**
     * 更改记录
     */
    public function updateWheresRecord($data,$where){
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }





    /**
     * [updateRecord 修改数据库库存]
     * @return [type] [arr]
     */
    public function updateRecord($data){
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheRowArray($sql);
        return $arr;
    }




    //   -------------[查:]   
       
	/**
     * [selectRule 查询库存多条数据]
     * @return [type] [arr]
     */
    public function selectRule($data){
    	$where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }


    /**
     * [selectRule 查询库存单条数据]
     * @return [type] [arr]
     */
    public function selectRuleOne($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheRowArray($sql);
        return $arr;

    }


    /**
     * [categorySum 出库明细站点id分类]
     * @return [type] [arr]
     */
    public function categorySum(){
        $sql=' SELECT site_id FROM '.$this->tablename.' GROUP BY site_id';
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }

    /**
     * [stoDetails 链表查询]
     * @return [type] [arr]
     */
    public function stoDetails($data){
        $sql='SELECT ru.name,ru.specifications,ru.coding,re.num,re.create_date FROM `md_storage_record` as `re`,`md_storage_rule` as `ru` WHERE re.site_id = '.$data.' AND re.code = ru.coding ';
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }






}
