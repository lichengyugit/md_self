 <?php
class StorageMeterialModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_meterial');
        $this->log->log_debug('StorageMeterialModel  model be initialized');
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

    /**
     * 更新出库库存
     */
    public function updateMeterialNum($parames,$data){
      $sql=' UPDATE '.$this->tablename.' SET stock=stock-'.$parames.' WHERE code='.'"'.$data.'"';
      $result=$this->write_db->query($sql);
      return $this->write_db->affected_rows();
    }
    /**
     * 更新仓库入库库存
     */
    public function updateMeterialNumPlus($parames,$data){
      $sql=' UPDATE '.$this->tablename.' SET stock=stock+'.$parames.' WHERE id='.'"'.$data.'"';
      $result=$this->write_db->query($sql);
      return $this->write_db->affected_rows();
    }








    //   -------------[查:]   
       
	/**
     * [selectMeterial 查询库存数据]
     * @return [type] [arr]
     */
    public function selectMeterial($data,$limit){
    	$where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where.' ORDER BY ID DESC'." LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }
    /**
     * [selectMeterial 查询库存数据]
     * @return [type] [arr]
     */
    public function selectMeterialNot($data){
      $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }


    /**
     * 检查耗材编号是否存在表内 以及是否有库存
     */
    public function inspectMeterialNum($parames){
      $sql=' SELECT code FROM '.$this->tablename.' WHERE code='.'"'.$parames.'"'.' AND stock>0';
      $result=$this->getCacheRowArray($sql);
      if(!empty($result)){
        return true;
      }else{
        return false;
      }
    }

    /**
      * 根据条件获取库存耗材种类数量
     */
    public function getMeterConfigNumByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /**
     * 根据条件获取库存耗材种类数量
     */
    public function getMeterNumByInfo($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." =". '"'.$v.'"';
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }



}
