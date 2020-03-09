 <?php
class StorageAccountModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_account');
        $this->log->log_debug('StorageAccountModel  model be initialized');
    }


    //   -------------[增:]   
    
    /**
     * [addMeterial 根据data新增数据]
     * @return [type] [bool]
     */
    public function addMeterial($data){
    	$data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);

    }

    public function addBashMate($data)
    {
        $rs=$this->insertBatch($data);
        if($rs){
            return $rs;
        }else{
            return false;
        }
    }

 
    //   -------------[删:]   

    //   -------------[改:]   
    public function updateAccount($data,$wheres)
    {
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }


    //   -------------[查:]   
       
	/**
     * [selectAccount 查询多条数据]
     * @return [type] [arr]
     */
    public function selectAccount($data){
    	$where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }

    /**
     * [selectAccount 查询单条数据]
     * @return [type] [arr]
     */
    public function selectAccountOne($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheRowArray($sql);
        return $arr;
    }





}

