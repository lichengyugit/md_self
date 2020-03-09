<?php
header("content-type:text/html;charset=utf-8");
class BrandModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct('md_xiuche', 'md_brand');
        $this->log->log_debug('BrandModel  model be initialized');
    }


    /**
     * 根据条件查找品牌信息
     * @params $data  查询品牌条件
     * @return  data
     */
    public function selectBrandData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }










}
