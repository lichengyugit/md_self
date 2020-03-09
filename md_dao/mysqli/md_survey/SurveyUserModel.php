 <?php
header("content-type:text/html;charset=utf-8");
class SurveyUserModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_survey', 'md_survey_staff');
        $this->log->log_debug('SurveyInfoModel  model be initialized');
    }




    //   -------------[增:]   
    
    /**
     * [addSurveyUser 新增工程队人员]
     * @return [type] [bool]
     */
    public function addSurveyUser($data){
    	$data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
    	$insert=$this->insert($data);
        return $this->lastInsertId();
    }








 
    //   -------------[删:]   


    //   -------------[改:]   


    //   -------------[查:]   



     
}
