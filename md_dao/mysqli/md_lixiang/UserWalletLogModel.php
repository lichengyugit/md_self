<?php
class UserWalletLogModel extends DB_Model {
    protected $tables = array(
            'user' => 'md_lixiang.md_user'
    );

    public function __construct() 
    {
        parent::__construct($this->dbname, 'md_user_wallet_log');
        $this->log->log_debug('UserWalletLogModel  model be initialized');
    }
    
    /**
     * 添加用户余额操作日志
     */
    public function addUserWalletLog($data)
    {
        $data['create_time'] = time();
        $data['create_date'] = date('Y-m-d H:i:s');
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }


    /**
     * [getAllUserWalletLog description]  获取用户钱包流水信息
     * @param  [type]  $parames  [description]
     * @param  integer $page     [description]
     * @param  integer $pageSize [description]
     * @return [type]            [description]
     */
     public function getAllUserWalletLog($parames,$page=1, $pageSize=7)
    {
        $where="";
        foreach($parames as $k=>$v){
            $where.= " AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename;   //  查询用户账单总记录条数
        $row = $this->getCacheRowArray($sql);
        $numpages = ceil(count($row )/$pageSize);          //计算总页数:向上取整；
        $page  = empty($page)? $page:1    ;                 //页码

        // $pagesize = ($page-1) * $pageSize; //起始条数

        //判断页码越界
        if($page>$numpages)  $page=$numpages;
        if($page<1)         $page=1;

        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC LIMIT ?,?";
        return $this->getCacheResultArray($sql,array(($page - 1) * $pageSize, $pageSize));
    }



    public function getUserWalletLogInfoById($id){
        $sql = " SELECT * FROM ".$this->tablename." WHERE id=".$id;
        return $this->getCacheRowArray($sql);
    }

    public function getUserWalletLogInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    //根据条件获取多条数据
    public function getUserWalletLogsInfoByAttr($parames,$type=1){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;

        if($type==2){
            $time=date('Y-m',time());
            $where=$where." AND create_date like '".$time."%'";
            $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        }
        return $this->getCacheResultArray($sql);
    }
    
    /*
     * 根据条件获取钱包流水数量
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
     * 根据条件获得所有钱包列表
     */
       public function getAllUserWalletLogPage($limit,$parames){
           $where="";
           foreach ($parames as $k=>$v){
               $where.=" AND ".$k." = ".$v;
           }
           $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
           return $this->getCacheResultArray($sql,$where);
       }
       
       //根据条件获取多条数据
       public function getUserWalletLogByLike($parames,$time){
           $where="";
           foreach ($parames as $k=>$v){
               $where.=" AND ".$k." = ".$v;
           }
           $sql = " SELECT * FROM ".$this->tablename." WHERE create_date LIKE "."'".$time."'".$where;
           return $this->getCacheResultArray($sql);
       }
       
       //查询用户倒数第一条钱包记录
       public function getUserWalletLogDESCByAttr($parames){
           $where="";
           foreach ($parames as $k=>$v){
               $where.=" AND ".$k." = ".$v;
           }
           $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC LIMIT 1";
           return $this->getCacheRowArray($sql);
       }
       
       /**
        * 分页获取用户钱包日志记录
        * @param 查询条件 $parames
        * @param number $page 页数
        * @param number $pageSize 每页显示条数
        */
       
       public function getAllWalletLogPage($parames,$page=1, $pageSize=10)
       {
            $where="";
            foreach($parames as $k=>$v){
                $where.= " AND ".$k." = '".$v."'";
            } 
           $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;   //  查询用户日志
           $row = $this->getCacheResultArray($sql);
           $numpages = ceil(count($row )/$pageSize);          //计算总页数:向上取整；
           $page  = empty($page)? 1:$page;                 //页码
           //判断页码越界
           if($page>$numpages){
               $page=$numpages;
           }
           if($page<1){
               $page=1;
           }
           $pagesize = ($page-1) * $pageSize; //起始条数
           $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where." ORDER BY id DESC LIMIT ?,?";
           $arr=$this->getCacheResultArray($sql,array($pagesize, $pageSize));
           $arr['numpages']=$numpages;
           return $arr;
       }


    /*
     * 钱包日志与用户表连表
     * */
     public function getWalletAndUserData($limit='',$paramer='')
     {
         $where='';
         empty($paramer['user_id']) || $where.=" AND WL.user_id=".$paramer['user_id'];
         empty($paramer['income_type']) || $where.=" AND WL.income_type=".$paramer['income_type'];
         empty($paramer['type']) || $where.=" AND WL.type=".$paramer['type'];
         if(!empty($paramer['create_time'])){
             $str=preg_split('/\s-\s/',$paramer['create_time']);
             $strTime=strtotime($str[0]);
             $endTime=strtotime($str[1]);
             $where.=' AND WL.create_time>='.$strTime.' AND WL.create_time<='.$endTime;
         }
         $sql = " SELECT WL.*,U.name,U.mobile FROM ".$this->tablename." AS WL LEFT JOIN ".$this->tables['user']." AS U ON WL.primary_id=U.id WHERE WL.status=0 ".$where." ORDER BY WL.id DESC ".$limit;
         return $this->getCacheResultArray($sql);
     }

    /*
    * 钱包日志(如用添加条件 勿动结构)
    * */
    public function getWalletLogData($limit='',$paramer='')
    {
        $where='';
        empty($paramer['user_id']) || $where.=" AND user_id=".$paramer['user_id'];
        empty($paramer['income_type']) || $where.=" AND income_type=".$paramer['income_type'];
        empty($paramer['type']) || $where.=" AND type=".$paramer['type'];
        if(!empty($paramer['create_time'])){
            $str=preg_split('/\s-\s/',$paramer['create_time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $where.=' AND create_time>='.$strTime.' AND create_time<='.$endTime;
        }
        $sql = " SELECT * FROM ".$this->tablename."  WHERE status=0 ".$where." ORDER BY id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }
}
?>