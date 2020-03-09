<?php
class PictureModel extends DB_Model {
    protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_picture');
        $this->log->log_debug('PictureModel  model be initialized');
    }
    
    
    //添加图片信息
    public function saveImage($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s');
        $insert=$this->insert($data);
        if($insert){
            return $insert;
        }else{
            return false;
        }

    }

    public function bashSaveImage($data)
    {
        $rs=$this->insertBatch($data);
        if($rs){
            return $rs;
        }else{
            return false;
        }
    }
    
    //按条件查询多条数据
    public function getImageInfo($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
         $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }




    //-------------------------------------

    //按条件查询单条数据
    public function getImageInfoAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 AND platform=4 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /**
     * 勘测系统查询单条图片
     */
    public function getImageOne($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 AND platform=3 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }

    /**
     * 修改图片信息
     */
    public function updatePic($data,$wheres)
    {
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }else{
            return false;
        }
    }

    /**
     * wherein查询获取图片数据
     */
    public function getPicWhereIn($where,$colunm,$parames){
        $wheres='';
        foreach ($parames as $k=>$v){
            $wheres.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 $wheres AND platform=4 AND $colunm  in( ".$where." ) "."order by field($colunm,$where)";
        $rs=$this->getCacheResultArray($sql);
        if($rs){
            return $rs;
        }else{
            return false;
        }
    }


}
?>
