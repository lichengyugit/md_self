<?php
class AdvertConfigModel extends DB_Model 
{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname,'md_advert_config');
        $this->log->log_debug('AdvertConfigModel  model be initialized');
    }



    /*
         获取所有配置
     */
    public function getAllAdvertConfig($limit,$parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;

        $arr=$this->getCacheResultArray($sql,$where);

        return $arr;
    }

    


    /*
         新增优惠券配置
     */
    public function addAdvertConfig($data)
    {
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }





    /**
      * 根据条件获取优惠券数量
     */
    public function getAdvertConfigNumByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }

   


   /**
      *  根据条件获取所有配置
     */
    public function getAllAdvertConfigs($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }



    /**
      * 根据ID修改优惠券配置
     */
    public function updateAdvert($data){
        $wheres=array('id'=>$data['id']);
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


