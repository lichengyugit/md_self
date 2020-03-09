 <?php
class StorageRuleModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_rule');
        $this->log->log_debug('StorageRuleModel  model be initialized');
    }


    //   -------------[增:]   
    /**
     * [addMeterial 根据data新增数据]
     * @return [type] [bool]
     */
    public function addMeterial($data){
    	$data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
    	$insert=$this->insert($data);
        return $this->lastInsertId();
    }










 
    //   -------------[删:]   


    //   -------------[改:]   



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
     *  [selectRuleIn 查询值在字段内的数据]
     */
    public function selectRuleIn($data){
        $sql=" SELECT * FROM ".$this->tablename." WHERE id in(".$data.")";
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }







}
