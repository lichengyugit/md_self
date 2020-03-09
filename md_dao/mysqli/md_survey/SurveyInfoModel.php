 <?php
header("content-type:text/html;charset=utf-8");
class SurveyInfoModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_survey', 'md_survey_info');
        $this->log->log_debug('SurveyInfoModel  model be initialized');
    }

    //根据条件获取信息
   public function getSurveyInfo($parames)
   {  
       $parames['project_id']=$parames['id'];
       unset($parames['id']);
       $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
       }
       $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
       return $this->getCacheRowArray($sql);
   }
   
   //修改勘测站点状态
   public function updateSurveyState($data)
   {   
       $wheres=array('project_id'=>$data['id']);
       unset($data['id']);
       $data['create_time']=time();
       $data['create_date']=date('Y-m-d H:i:s',time());
       $update=$this->update($data, $wheres);
       if($update){
           return $update;
       }else{
           return false;
       }
   }
   
   //添加勘测信息
   public function addSurveyInfo($data)
   {
       $data['create_time']=time();
       $data['create_date']=date('Y-m-d H:i:s',time());
       $insert=$this->insert($data);
       if($insert){
           return $insert;
       }else{
           return false;
       }

   }
   
//     //根据站点id查询对应站点信息
//     public function getSiteInfo($parames)
//     {
//         $where="";
//         foreach ($parames as $k=>$v){
//             $where.=" AND ".$k." = ".$v;
//         }
//         $sql = " SELECT * FROM ".$this->tablename." WHERE status=0".$where;
//         return $this->getCacheRowArray($sql);
//     }
   

    /*
    **   根据关联工程表获取指定id任务表详情页信息
    */
   public function getSurveyDetails($parames){
      $where="";
       foreach ($parames as $k=>$v){
           $where.=" AND ".$k." = ".$v;
       }
      $sql=" SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
      $arr=$this->getCacheResultArray($sql);
      return $arr;
   }


    /*
    **  修改状态
     */
   public function updataState($parames,$wheres){
      $parames['create_time']=time();
      $parames['create_date']=date('Y-m-d H:i:s',time());
      $update=$this->update($parames,$wheres);
      if($update){
        return true;
      }else{
        return false;
      }
   }





     
}
