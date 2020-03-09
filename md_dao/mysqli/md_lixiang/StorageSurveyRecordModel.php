<?php
class StorageSurveyRecordModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_storage_survey_record');
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
    /**
     * [addRecord 根据data新增数据]
     * @return [type] [bool]
     */
    public function addBatchRecord($data){
        $insert=$this->insertBatch($data);
        return $this->lastInsertId();
    }


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
        $sql=" SELECT * FROM ".$this->tablename." WHERE status < 2".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }


    /**
     * [selectSurveyRecord 大B端出库 连表查询订单表下的记录]
     * @return [type] [arr]
     */
    public function selectSurveyRecord($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT re.* FROM md_storage_order od left join md_storage_survey_record re on od.id = re.order_id where od.status < 2 and (od.order_status = 2 or od.order_status = 1) ".$where;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }




   public function getRecordCabinetData($parames)
   {
       $where='';
       if(isset($parames['cabinet_number']) || $parames['cabinet_number']!=''){
           $where.=" AND code LIKE "."'". $parames['cabinet_number'] ."%'";
       }
       empty($parames['state'])   || $where.=' AND state='.$parames['state'];
       empty($parames['site_id']) || $where.=" AND site_id=".$parames['site_id'];
       empty($parames['type']) || $where.=" AND type=".$parames['type'];
       $sql=" SELECT * FROM ".$this->tablename." WHERE status=0 ".$where." ORDER BY id DESC";
       return $this->getCacheResultArray($sql);
   }








}





