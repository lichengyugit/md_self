 <?php
class RegisterModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_register');
        $this->log->log_debug('RegisterModel  model be initialized');
    }

    /**
     * 获取单条信息
     */
    public function getRegByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 获取多条信息
     */
    public function getRegsByAttr($parames,$limit=''){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where.' ORDER BY id DESC LIMIT '.$limit;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 修改数据
     */
    public function updateRegByAttr($data){
        $wheres['id']=$data['id'];
        unset($data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    /**
     * 添加单条数据
     */
    public function saveReg($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 根据条件获取商家入驻数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }





    /**
     * 连表模糊查询用户数据
     */
    public function tableQuery($data,$LIMIT=''){
        $like=$data['select'];
        unset($data['select']);
        if(!empty($data['time'])){
            $str=preg_split('/\s-\s/',$data['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
        }else{
            $str='';
        }
        $sql="SELECT * FROM md_register WHERE 1=1 ".$str." AND CONCAT(IFNULL(shop_name,'"."'),IFNULL(mobile,''),IFNULL(card_number,''),IFNULL(name,''),IFNULL(location,'')) LIKE '%".$like."%'"." ORDER BY id DESC ".$LIMIT;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 根据搜索条件获取用户数据数量
     */
    public function getSearchCountBatteryByAttr($data){
        $like=$data['select'];
        unset($data['select']);
        if(!empty($data['time'])){
            $str=preg_split('/\s-\s/',$data['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
        }else{
            $str='';
        }
        $sql="SELECT count(1) as c FROM md_register WHERE 1=1 ".$str." AND CONCAT(IFNULL(shop_name,'"."'),IFNULL(mobile,''),IFNULL(card_number,''),IFNULL(name,''),IFNULL(location,'')) LIKE '%".$like."%'";
        return $this->getCacheRowArray($sql)['c'];
    }









}

