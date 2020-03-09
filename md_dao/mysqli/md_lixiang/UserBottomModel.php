 <?php
class UserBottomModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_user_bottom');
        $this->log->log_debug('UserBottomModel  model be initialized');
    }


    /**
     * [getUserInfo]
     * 获取1.0用户信息
     */
    public function getUserBottomInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 添加用户底托订单
     */
    public function insertUserBottom($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d,H:i:s',$data['create_time']);
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 更改用户底托订单状态
     */
    public function updateBottonByAttr($data){
        $wheres=array('user_id'=>$data['id']);
        unset($data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
}

