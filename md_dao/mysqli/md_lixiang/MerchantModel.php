<?php
class MerchantModel extends Db_Model{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_merchant');
        $this->log->log_debug('MerchantModel  model be initialized');
    }

    /**
     * 根据条件获取单条商家数据
     */
    public function getMerchantInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /**
     * 根据条件获取单条商家数据后台
     */
    public function getMerchantInfoBy($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }
    
    /**
     * 根据条件获取多条条商家数据
     */
    public function getMerchantInfosByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 保存单条商家数据
     */
    public function saveMerchant($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
    
    /**
     * 修改单条商家数据
     */
    public function updateMerchantById($data){
//         $data['update_time']=time();
//         $data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres=array('id'=>$data['id']);
        unset($data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update ;
        }
        else{
            return false;
        }
    }
    /**
     * 根据条件获取商家数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /*
     * 根据条件模糊查询与实际范围查询数据
     * */
     public function getMerchantDataAll($data='',$limit='')
     {
         $where='';
         if(!empty($data['create_time'])){
             $str=preg_split('/\s-\s/',$data['create_time']);
             $strTime=strtotime($str[0]);
             $endTime=strtotime($str[1]);
             $where.='AND create_time>='.$strTime.' AND create_time<='.$endTime;
         }
         if(!empty($data['input_data'])) {
             if (is_numeric($data['input_data'])) {
                 $where .= " AND mobile LIKE " . "'" . $data['input_data'] . "%'";
             } else {
                 $where .= " AND name LIKE " . "'" . $data['input_data'] . "%'";
             }
         }
         $sql="SELECT * FROM ".$this->tablename." WHERE status < 2 ". $where ." ORDER BY id DESC ".$limit;
         return $this->getCacheResultArray($sql);
     }

     /*
      * 根据条件获取有商家的市区
      * */
     public function getCityInMerchantData($data='',$limit='',$groupBy='')
     {
         $where='';
         if(!empty($data['city_name']) && $data['city_name'] != ''){
             $where.=" AND city_name LIKE ". "'%" . trim($data['city_name']) . "%'";
         }

         if(!empty($data['merchant_name']) && $data['merchant_name'] != ''){
             $where.=" AND name LIKE ". "'%" . trim($data['merchant_name']) . "%'";
         }
         empty($data['type'])        || $where.=" AND type       =".$data['type'];
         empty($data['city_id'])     || $where.=" AND city_id    =".$data['city_id'];
         empty($data['district_id']) || $where.=" AND district_id=".$data['district_id'];
         $sql="SELECT * FROM ".$this->tablename." WHERE status = 0 ".$where.$groupBy." ORDER BY id DESC ".$limit;
         return $this->getCacheResultArray($sql);
     }
    /**
     * 根据条件获取多条条商家数据分页
     */
    public function getMerchantList($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where." ORDER BY id DESC "." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }


}
