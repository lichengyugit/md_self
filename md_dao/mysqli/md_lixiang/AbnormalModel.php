<?php
class AbnormalModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_abnormal');
        $this->log->log_debug('AbnormalModel  model be initialized');
    }
    
    
    
    public function addLog($data){
        $data['create_time']=time();
        $data['create_date']=date("Y-m-d H:i:s",time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
}
?>