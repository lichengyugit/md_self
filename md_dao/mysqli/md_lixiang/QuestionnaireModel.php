<?php
class QuestionnaireModel extends Db_Model{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_questionnaire');
        $this->log->log_debug('QuestionnaireModel  model be initialized');
    }

    /**
     * 根据条件获得所有集团列表
     */
    public function getAllQuestionnaireByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /*
     * 根据条件获取集团数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
    
    /**
     * 根据条件获取单条集团数据
     */
    public function getQuestionnaireInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 保存单条集团数据
     */
    public function saveQuestionnaire($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 修改单条集团数据
     */
    public function updateQuestionnaireById($data){
//         $data['update_time']=time();
//         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres=array('id'=>$data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * wherein查询获取集团数据
     */
    public function getQuestionnaireWhereIn($where){
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 AND id  in( ".$where." )  ";
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据where条件修改集团数据
     */
    public function updateQuestionnaireByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
}