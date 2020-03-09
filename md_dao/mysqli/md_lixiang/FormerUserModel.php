 <?php
class FormerUserModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_former_user');
        $this->log->log_debug('FormerUserModel  model be initialized');
    }


    /**
     * [getUserInfo]
     * 获取1.0用户信息
     */
    public function getUserInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    //插入1.0用户表数据(单条)
    public function insertUserInfo($parames){
        $sql=" INSERT IGNORE INTO ".$this->tablename."(`id`,`name`,`mobile`,`idcard`,`create_time`,`create_date`) VALUES ";
        $sql.='("'.$parames['id'].'","'.$parames['name'].'","'.$parames['mobile'].'","'.$parames['idcard'].'","'.time().'","'.date('Y-m-d H:i:s',time()).'")';
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }
    
    //插入1.0用户表数据(批量)
    public function inserUserInfos($parames){
        $sql=" INSERT IGNORE INTO ".$this->tablename."(`id`,`name`,`mobile`,`idcard`,`create_time`,`create_date`) VALUES ";
        foreach($parames as $k=>$v){
            $sql.='("'.$v['id'].'","'.$v['name'].'","'.$v['mobile'].'","'.$v['idcard'].'","'.time().'","'.date('Y-m-d H:i:s',time()).'"),';
        }
        $sql=substr($sql, 0,-1);
        $this->write_db->query($sql);
        return $this->write_db->affected_rows();
    }
    
    //查询1.0用户表中所有id
    public function getUserIds(){
        $sql=" SELECT id FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }
}

