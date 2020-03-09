<?php
header("content-type:text/html;charset=utf-8");
class MalfunctionPivotModel extends DB_Model
{
    protected $tables = array(
    );

    public function __construct()
    {
        parent::__construct('md_survey', 'md_malfunction_pivot');
        $this->log->log_debug('MalfunctionPivotModel  model be initialized');
    }


    public function addMalPivot($data)
    {
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        return $this->insert($data);
    }

    /**
     * 获取单条信息
     */
    public function getMalfunctionByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC ";
        return $this->getCacheRowArray($sql);
    }

    /**
     * 获取多条信息
     */
    public function getMalfunctionsByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC";
        return $this->getCacheResultArray($sql);
    }

    /**
     * 修改数据
     */
    public function updateMalfunctionByAttr($data){
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
     * 添加单条数据
     */
    public function saveMalfunction($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $this->insert($data);
        return $this->lastInsertId();
    }

    //根据id获取单条故障表信息
    public function getMalfunctionInfo($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /*后台修改故障信息
     * */
    public function editMalPivotData($data,$wheres)
    {
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }else{
            return false;
        }
    }



    /**
     * 根据where条件修改数据
     */
    public function updatePivotByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }





    /**
     * 获取多条信息倒序
     */
    public function getPivotByAttrOrderBy($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC ";
        return $this->getCacheResultArray($sql);
    }




}
