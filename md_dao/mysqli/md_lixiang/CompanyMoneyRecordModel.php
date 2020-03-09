<?php
class CompanyMoneyRecordModel extends Db_Model{
    protected $tables=array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_company_money_record');
        $this->log->log_debug('CompanyMoneyRecordModel  model be initialized');
    }
 

    /**
     * 添加单条修改交易数据记录
     */
    public function saveCompanyMoneyRecord($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 修改单条集团数据
     */
    public function updateCompanyMoneyByAttr($data){
        //         $data['update_time']=time();
        //         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres=array('company_id'=>$data['company_id']);
        unset($data['company_id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }





}



