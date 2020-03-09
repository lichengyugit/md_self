 <?php
header("content-type:text/html;charset=utf-8");
class SurveyTeamModel extends DB_Model
{
    protected $tables = array(
        'user'=>'md_lixiang.md_user'
    );

    public function __construct()
    {
        parent::__construct('md_survey', 'md_survey_team');
        $this->log->log_debug('SurveyInfoModel  model be initialized');
    }


    //   -------------[增:]   
    
    /**
     * [addSurveyTeam 新增工程队]
     * @return [type] [bool]
     */
    public function addSurveyTeam($data){
    	$data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
    	$insert=$this->insert($data);
        return $this->lastInsertId();
    }










 
    //   -------------[删:]   


    //   -------------[改:]   

     /**
     * [addSurveyTeam 修改工程队]
     * @return [type] [bool]
     */
    public function updateSurveyTeam($data){
    	$wheres=array('id'=>$data['id']);
      	unset($data['id']);
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
     	$update=$this->update($data, $wheres);
     	if($update){
           return true;
     	}else{
           return 2;
      	}
    }











    //   -------------[查:]   

    /**
     * [selectSurveyTeam 查询工程队表全部数据]
     * @return [type] [arr]
     */
    public function selectSurveyTeam($data,$limit=''){
    	$where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql=" SELECT * FROM ".$this->tablename." WHERE status <> 2".$where." ORDER BY id DESC".$limit;
        $arr=$this->getCacheResultArray($sql);
        return $arr;
    }

//    /*
//     * 连表查询工程队成员(md_user)
//     * */
//     public function getTeamUserData()
//     {
//         $sql="SELECT * FROM ".$this->tablename." AS T LEFT JOIN ".$this->tables['user']." AS U ON T.id=U.attr_id ";
//     }


}
