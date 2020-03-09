<?php
header("content-type:text/html;charset=utf-8");
class MalfunctionServeModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct('md_xiuche', 'md_malfunction_serve');
        $this->log->log_debug('md_malfunction_serve  model be initialized');
    }


    /**
     * 根据条件查找服务信息
     * @params $parames  查询维修服务条件
     * @return  data
     */
    public function selectServeData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 根据条件获取单条服务信息
     * @param $parames      查询订单条件
     * @return bool|mixed
     */
    public function selectServeRow($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }








}
