<?php
header("content-type:text/html;charset=utf-8");
class MalfunctionModel extends DB_Model
{
    protected $tables = array(
        'pivot'=>'md_survey.md_malfunction_pivot',
        'record'=>'md_survey.md_malfunction_record',
    );

    public function __construct()
    {
        parent::__construct('md_survey', 'md_malfunction');
        $this->log->log_debug('MalfunctionModel  model be initialized');
    }


    public function addMalfunctionData($data)
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
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
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

    /*
     * pivot表与故障表
     * */
    public function getMalfunctionDataCount($limit='',$parames)
    {
        $where='';
        empty($parames['state'])             || $where.=" AND M.state                =".$parames['state'];
        empty($parames['malfunction_state']) || $where.=" AND M.malfunction_state    =".$parames['malfunction_state'];
        empty($parames['course_status'])     || $where.=" AND P.course_status        =".$parames['course_status'];
        empty($parames['fault_level'])       || $where.=" AND M.fault_level          =".$parames['fault_level'];
        empty($parames['team_id'])           || $where.=" AND P.team_id              =".$parames['team_id'];
        if(isset($parames['malfunction_status']) && $parames['malfunction_status']!=''){
            if($parames['malfunction_status']==5){
                $where.=" AND P.malfunction_status  =".$parames['malfunction_status']." AND M.state=1 AND M.malfunction_state=2 AND P.course_status=1";
            }else{
                $where.=" AND P.malfunction_status  =".$parames['malfunction_status'];
            }
        }
        if(!empty($parames['malfunction_date'])){
            $str=preg_split('/\s-\s/',$parames['malfunction_date']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND M.malfunction_time>='.$strTime.' AND M.malfunction_time<='.$endTime;
        }
        if(!empty($parames['servicing_date'])){
            $str=preg_split('/\s-\s/',$parames['servicing_date']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND P.servicing_time>='.$strTime.' AND P.servicing_time<='.$endTime;
        }

        if(isset($parames['input_data']) && $parames['input_data']!=''){
            $data=trim($parames['input_data']);
            if(preg_match("/^[0-9a-zA-Z]{3,10}$/", $data)){
                $where.=' AND M.cabinet_num='."'".$data."'";
            }else{
                $where.=" AND P.admin_user_name LIKE"."'%". $data ."%'";
            }
        }

        $sql="SELECT COUNT(1) FROM ".$this->tables['pivot']." WHERE id IN( SELECT MAX(P.id) FROM ".$this->tablename." AS M LEFT JOIN ".$this->tables['pivot']." AS P ON P.malfunction_id=M.id 
        WHERE M.status=0 ".$where." GROUP BY M.id)";
//        $sql=" SELECT COUNT(1) FROM ".$this->tablename." AS M LEFT JOIN ".$this->tables['pivot']." AS P ON M.id=P.malfunction_id WHERE P.id IN () ".$where." GROUP BY M.id";
        return $this->getCacheRowArray($sql)['COUNT(1)'];
    }

    /*
     * 历程表与故障表  故障原因表
     * */
    public function getMalfunctionDataAll($limit='',$parames)
    {
        $where='';
        empty($parames['state'])             || $where.=" AND M.state                =".$parames['state'];
        empty($parames['record_type'])       || $where.=" AND R.type                =".$parames['record_type'];
        empty($parames['malfunction_state']) || $where.=" AND M.malfunction_state    =".$parames['malfunction_state'];
        empty($parames['course_status'])     || $where.=" AND P.course_status        =".$parames['course_status'];
        empty($parames['fault_level'])       || $where.=" AND M.fault_level          =".$parames['fault_level'];
        empty($parames['team_id'])           || $where.=" AND P.team_id              =".$parames['team_id'];
        if(isset($parames['malfunction_status']) && $parames['malfunction_status'] != ''){
            if($parames['malfunction_status']==5){
                $where.=" AND P.malfunction_status  =".$parames['malfunction_status']." AND M.state=1 AND M.malfunction_state=2 AND P.course_status=1";
            }else{
                $where.=" AND P.malfunction_status  =".$parames['malfunction_status'];
            }

        }
        if(!empty($parames['malfunction_date'])){
            $str=preg_split('/\s-\s/',$parames['malfunction_date']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND M.malfunction_time>='.$strTime.' AND M.malfunction_time<='.$endTime;
        }
        if(!empty($parames['servicing_date'])){
            $str=preg_split('/\s-\s/',$parames['servicing_date']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND P.servicing_time>='.$strTime.' AND P.servicing_time<='.$endTime;
        }

        if(isset($parames['input_data']) && $parames['input_data']!=''){
            $data=trim($parames['input_data']);
            if(preg_match("/^[0-9a-zA-Z]{3,10}$/", $data)){
                $where.=' AND M.cabinet_num='."'".$data."'";
            }else{
                $where.=" AND P.admin_user_name LIKE"."'%". $data ."%'";
            }
        }
        $sql="SELECT M.*,P.id as pivot_id,P.malfunction_status,P.servicing_date,P.course_soure,P.results_described,P.team_id,P.team_name,P.admin_id,P.admin_user_name,M.fault_level,GROUP_CONCAT(distinct(R.failure_cause)) AS failure_cause,GROUP_CONCAT(distinct(R.attr_failure)) AS attr_failure
         FROM ".$this->tablename." AS M LEFT JOIN ".$this->tables['pivot']." AS P ON P.malfunction_id=M.id LEFT JOIN ".$this->tables['record']." AS R ON R.pivot_id=P.id 
        WHERE P.id IN (
        SELECT MAX(P.id) FROM ".$this->tablename." AS M LEFT JOIN ".$this->tables['pivot']." AS P ON P.malfunction_id=M.id WHERE M.status=0 ".$where.
        " GROUP BY M.id) AND R.status=0 GROUP BY M.id DESC ".$limit;
//        $sql=" SELECT M.*,P.id as pivot_id,P.malfunction_status,P.servicing_date,P.course_soure,P.results_described,P.team_id,P.team_name,P.admin_id,P.admin_user_name,M.fault_level,GROUP_CONCAT(distinct(R.failure_cause)) AS failure_cause,GROUP_CONCAT(distinct(R.attr_failure)) AS attr_failure
//        FROM ".$this->tablename."  AS M LEFT JOIN ".$this->tables['pivot']." AS P ON M.id=P.malfunction_id  LEFT JOIN ".$this->tables['record']." AS R ON R.pivot_id=P.id
//        WHERE M.status=0 ".$where." GROUP BY M.id  ORDER BY M.id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }


    /*后台修改故障信息
     * */
    public function editMalData($data,$wheres)
    {
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }else{
            return false;
        }
    }


    /*
     *   根据条件连表查询故障 历程信息
     *  [field] 查询字段
     *  [parames] 查询判断
     */
     public function getMalConnectData($field,$parames){
         $where="";
         foreach ($parames as $k=>$v){
             $where.=" AND ".$k." = ".$v;
         }
        $sql="select ".$field." from md_survey.md_malfunction ml left join md_survey.md_malfunction_pivot pi on ml.id = pi.malfunction_id where 1=1 ".$where." ORDER BY pi.id DESC";
         return $this->getCacheResultArray($sql);
     }


    /*
       * pivot表与故障表 故障原因   单条
       * */
    public function getMalfunctionDataInfo($parames)
    {
        $where='';
        empty($parames['pivot_id'])     || $where.=" AND P.id   =".$parames['pivot_id'];
        empty($parames['mal_id'])       || $where.=" AND M.id   =".$parames['mal_id'];
        empty($parames['record_type'])  || $where.=" AND R.type   =".$parames['record_type'];
        empty($parames['state'])        || $where.=" AND M.state   =".$parames['state'];
        empty($parames['fault_level'])  || $where.=" AND M.fault_level   =".$parames['fault_level'];
        empty($parames['team_id'])      || $where.=" AND P.team_id =".$parames['team_id'];
        if(isset($parames['malfunction_status']) && $parames['malfunction_status']!=''){
            $where.=" AND P.malfunction_status  =".$parames['malfunction_status'];
        }
        if(!empty($parames['malfunction_date'])){
            $str=preg_split('/\s-\s/',$parames['malfunction_date']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND M.malfunction_time>='.$strTime.' AND M.malfunction_time<='.$endTime;
        }
        if(!empty($parames['servicing_date'])){
            $str=preg_split('/\s-\s/',$parames['servicing_date']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND P.servicing_date>='.$strTime.' AND P.servicing_date<='.$endTime;
        }


        if(isset($parames['input_data']) && $parames['input_data']!=''){
            $data=trim($parames['input_data']);
            if(preg_match("/^[0-9a-zA-Z]{3,10}$/", $data)){
                $where.=' AND M.cabinet_num='."'".$data."'";
            }else{
                $where.=" AND P.admin_user_name LIKE"."'%". $data ."%'";
            }
        }
        $sql=" SELECT M.*,P.id as pivot_id,P.malfunction_status,P.servicing_date,P.course_soure,P.results_described,P.team_id,P.team_name,P.admin_id,P.admin_user_name,M.fault_level,GROUP_CONCAT(distinct(R.failure_cause)) AS failure_cause,GROUP_CONCAT(distinct(R.attr_failure)) AS attr_failure 
        FROM ".$this->tablename."  AS M LEFT JOIN ".$this->tables['pivot']." AS P ON M.id=P.malfunction_id  LEFT JOIN ".$this->tables['record']." AS R ON R.pivot_id=P.id
        WHERE M.status=0 AND R.status=0 ".$where;
        return $this->getCacheRowArray($sql);
    }



}


