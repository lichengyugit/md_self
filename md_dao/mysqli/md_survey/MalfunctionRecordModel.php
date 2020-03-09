<?php
class MalfunctionRecordModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_survey', 'md_malfunction_record');
        $this->log->log_debug('MalfunctionRecordModel  model be initialized');
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
     * 获取多条信息  指定所需数据
     */
    public function getMalfunctionsRecordRequiredAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT failure_cause FROM ".$this->tablename." WHERE 1=1 ".$where;
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
    public function bashSaveRecord($data)
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


    public function delRecordData($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" DELETE FROM ".$this->tablename." WHERE 1=1 ".$where;
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }



    /*
     * 分组获取多条信息
     * */
    public function getGroupingRecord($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT id,pivot_id,type,GROUP_CONCAT(distinct(failure_cause)) AS failure_cause,GROUP_CONCAT(distinct(attr_failure)) AS attr_failure FROM ".$this->tablename." WHERE 1=1 ".$where." GROUP BY pivot_id";
        return $this->getCacheRowArray($sql);
    }

}

