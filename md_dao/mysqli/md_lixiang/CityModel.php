<?php
class CityModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_city');
        $this->log->log_debug('CityModel  model be initialized');
    }
    
    
    
    /**
     * 根据类型获得所有城市
     */
    public function getAllCity($name){
        $where="";
        if($name){
            $where.=" AND CityName like '%".$name."%'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据ID获取城市信息
     */
    public function getCityById($CityID){
        $sql = " SELECT * FROM ".$this->tablename." WHERE CityID = ".$CityID;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据名称获取城市id
     */
    public function getCityId($name,$ProvinceID){
        $sql=" SELECT CityID FROM ".$this->tablename." WHERE CityName = '$name' and ProvinceID = $ProvinceID";
        $row=$this->getCacheRowArray($sql);
        return $row['CityID'];
    }


    /**
     * 根据条件获取所有配置
     */
    public function getAllCityConfig($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }

    
}
?>
