<?php
class ProvinceModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_province');
        $this->log->log_debug('ProvinceModel  model be initialized');
    }
    
    
    /**
     * 根据类型获得所有省市
     */
    public function getAllProvince($name){
        $where="";
        if($name){
            $where.=" AND CityName like '%".$name."%'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据ID获取省市信息
     */
    public function getProvinceById($ProvinceID){
        $sql = " SELECT * FROM ".$this->tablename." WHERE ProvinceID = ".$ProvinceID;
        return $this->getCacheRowArray($sql);
    }


    /**
     * 根据名称获取省市ID
     */
    public function getProvinceId($name){
        $sql=" SELECT * FROM ".$this->tablename." WHERE ProvinceName = '$name'";
        $row=$this->getCacheRowArray($sql);
        return $row['ProvinceID'];
    }
}
?>