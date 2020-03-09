<?php
class UserTokenModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_user_token');
        $this->log->log_debug('UserTokenModel  model be initialized');
    }
    
    
    
    /**
     * 获取单条token信息
     */
    public function getTokenByAttr($user_id,$user_name){
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id = ? AND user_name = ?";
        return $this->getCacheRowArray($sql,array($user_id,$user_name));
    }
    
    /**
     * 验证token
     */
    public function checkToken($user_id,$token){
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id = ? AND token = ?";
        return $this->getCacheRowArray($sql,array($user_id,$token));
    }
    
    /**
     * 修改用户token
     */
    public function updateUserToken($data){
        if(isset($data['id'])){
            $wheres=array('id'=>$data['id']);
            unset($data['id']);
        }else{
            $wheres=array('user_id'=>$data['userId']);
            unset($data['userId']);
        }
        $time=time();
        $data['update_time']=$time;
        $data['update_date']=date('Y-m-d H:i:s',$time);
        $data['over_time']=$time+600;
        $update=$this->update($data, $wheres);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * 添加用户token
     */
    public function addUserToken($data){
        $time=time();
        $data['create_time']=$time;
        $data['create_date']=date('Y-m-d H:i:s',$time);
        $data['over_time']=$time+600;
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 获取所有token
     */
    public function getTokenList(){
        $sql = " SELECT * FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }
}
?>