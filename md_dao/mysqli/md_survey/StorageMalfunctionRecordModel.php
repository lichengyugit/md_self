<?php
class StorageMalfunctionRecordModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_survey', 'md_storage_malfunction_record');
        $this->log->log_debug('StorageMalfunctionRecordModel  model be initialized');
    }


    /**
     * 获取单条信息
     */
    public function getMalfunctionRecordByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 获取多条信息
     */
    public function getMalfunctionsRecordByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 修改数据
     */
    public function updateMalfunctionRecordByAttr($data){
        $wheres['id']=$data['id'];
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

     * 根据条件修改数据
     */
    public function editRecordData($data,$where){
        $update=$this->update($data, $where);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }


    /**
     * 添加单条数据
     */
    public function saveMalRecordfunction($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $this->insert($data);
        return $this->lastInsertId();
    }



    /*
     * 批量插入数据
     * */
    public function bashSaveImage($data)
    {
        $rs=$this->insertBatch($data);
        if($rs){
            return $rs;
        }else{
            return false;
        }
    }

    /**
     *  批量插入数据
     */
    public function saveMalRecordBatch($data){
        $this->insertBatch($data);
        return $this->affectedRows();
    }


}

