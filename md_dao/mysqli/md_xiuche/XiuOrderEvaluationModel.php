<?php
header("content-type:text/html;charset=utf-8");
class XiuOrderEvaluationModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct('md_xiuche', 'md_xiu_order_evaluation');
        $this->log->log_debug('md_xiu_order_evaluation  model be initialized');
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










}
