<?php
header("content-type:text/html;charset=utf-8");
class XiuOrderInfoModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct('md_xiuche', 'md_xiu_order_info');
        $this->log->log_debug('md_xiu_order_info  model be initialized');
    }

    public function addOrder($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    /**
     * 根据条件查找单条服务信息
     * @param $parames      查询维修服务条件
     * @return bool|mixed
     */
    public function selectOrderInfoRow($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /**
     * 修改订单信息
     * @param $data     修改订单信息参数
     * @param $wheres   修改订单条件
     * @return bool     返回参数
     */
    public function updateOrderInfoByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update >0){
            return true;
        }else{
            return false;
        }
    }


    //修改订单
    public function updateOrderByAttr($data,$where){
        $update=$this->update($data, $where);
        return $update;
    }








}
