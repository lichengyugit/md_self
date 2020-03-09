<?php
header("content-type:text/html;charset=utf-8");
class RepairTeamModel extends DB_Model
{
    protected $tables = array(
    );

    public function __construct()
    {
        parent::__construct('md_survey', 'md_repair_team');
        $this->log->log_debug('repairTeamModel  model be initialized');
    }


    /*
     * 添加维修队
     * */
    public function addRepairTeam($data)
    {
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        return $this->insert($data);
    }

   
   //根据id获取单条站点表信息
   public function getSurveyInfo($parames)
   {      
       $parames['site_id']=$parames['id'];
       unset($parames['id']);
       $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
       }
       $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
       return $this->getCacheRowArray($sql);
   }

   //根据条件获取多条数据
    public function getTeamInfoAll($data)
    {
        $where='';
        if(isset($data['team_name']) && $data['team_name']!=''){
            $where.=" AND team_name LIKE "."'%". trim($data['team_name']) ."%'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE 1=1".$where." ORDER BY id DESC";
        return $this->getCacheResultArray($sql);
    }


    //按条件查询多条数据
    public function getTeamData($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }


   /*
    * 后台维修队列表
    * */
   public function getTeamBackList($limit='',$data)
   {
       $where='';
       empty($data['status']) || $where.=" AND status < 2";
       if(isset($data['team_name']) && $data['team_name']!=''){
           $where.=" AND team_name LIKE "."'%". trim($data['team_name']) ."%'";
       }
       $sql=" SELECT * FROM ".$this->tablename." WHERE 1=1".$where." ORDER BY id DESC ".$limit;
       return $this->getCacheResultArray($sql);
   }

    /**
     * 修改管理员数据
     */
    public function updateTeamData($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }






}
