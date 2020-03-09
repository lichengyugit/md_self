<?php
class DistrictModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_district');
        $this->log->log_debug('DistrictModel  model be initialized');
    }
    
    
    
    /**
     * 根据类型获得所有区县
     */
    public function getAllDistrict($name){
        $where="";
        if($name){
            $where.=" AND CityName like '%".$name."%'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据ID获取区县信息
     */
    public function getDistrictById($DistrictID){
        $sql = " SELECT * FROM ".$this->tablename." WHERE DistrictID = ".$DistrictID;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据属性获取区县信息
     */
    public function getOneDistrictByAttr($parames){
        $where="";
        foreach($parames as $k=>$v){
            if(is_array($v)){
                $v=implode(",", $v);
                $where.= " AND ".$k." in (".$v.")";
            }else{
                $where.= " AND ".$k." = '".$v."'";
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据名称获取区县ID
     */
    public function getDistrictId($name,$CityID){
        $sql = " SELECT * FROM ".$this->tablename." WHERE DistrictName = '$name' AND CityID=$CityID ";
        $row=$this->getCacheRowArray($sql);
        return $row['DistrictID'];
    }
}
?>