<?php
header("content-type:text/html;charset=utf-8");
class XiuOrderModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct('md_xiuche', 'md_xiu_order');
        $this->log->log_debug('md_xiu_order  model be initialized');
    }


    public function addOrder($data)
    {
        $data['create_time'] = time();
        $data['create_date'] = date('Y-m-d H:i:s', time());
        $insert = $this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * 根据条件获取多条订单信息
     * @param $parames      查询订单条件
     * @return bool|mixed
     */
    public function selectOrderData($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT * FROM " . $this->tablename . " WHERE 1=1 " . $where . " ORDER BY id DESC";
        return $this->getCacheResultArray($sql);
    }

    /**
     * 根据条件获取单条订单信息
     * @param $parames      查询订单条件
     * @return bool|mixed
     */
    public function selectOrderRow($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT * FROM " . $this->tablename . " WHERE 1=1 " . $where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据多条件获取多条订单信息
     * @param $parames          查询订单条件
     * @param string $sortfile 排序字段名称
     * @param int $time 时间戳
     * @param int $limit 查询条数
     * @return bool|mixed
     */
    public function selectOrderMultiData($parames, $sortfile = 'id', $time, $limit = 15)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT od.*,info.user_location_longitude,info.user_location_latitude,info.service_name,info.service_id,info.user_address,se.icon FROM " . $this->tablename . " od LEFT JOIN md_xiuche.md_xiu_order_info info on od.id = info.order_id LEFT JOIN md_xiuche.md_malfunction_serve se on info.service_id = se.id WHERE od.create_time<" . $time . " " . $where . " ORDER BY " . $sortfile . " DESC Limit " . $limit;
        return $this->getCacheResultArray($sql);
    }

    /**
     * @param $parames      条件
     * @return bool|mixed
     */
    public function selectOrderFormData($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT od.*,info.user_location_longitude,info.user_location_latitude,info.service_name,info.service_id,info.user_address,info.brand_id,info.brand_name,se.icon FROM " . $this->tablename . " od LEFT JOIN md_xiuche.md_xiu_order_info info on od.id = info.order_id LEFT JOIN md_xiuche.md_malfunction_serve se on info.service_id = se.id WHERE 1=1 " . $where ;
        return $this->getCacheResultArray($sql);
    }



    /**
     * 获取修哥所有状态订单列表信息
     * @param $parames      搜索条件
     * @return bool|mixed   返回类型
     */
    public function selectFixOrderListData($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT od.*,info.user_location_longitude,info.user_location_latitude,info.service_name,info.service_id,info.user_address,info.brand_name,se.icon FROM " . $this->tablename . " od LEFT JOIN md_xiuche.md_xiu_order_info info on od.id = info.order_id LEFT JOIN md_xiuche.md_malfunction_serve se on info.service_id = se.id WHERE 1=1 " . $where . " ORDER BY id DESC ";
        return $this->getCacheResultArray($sql);
    }


    /**
     * 获取一条正在进行中的订单
     * @param $parames      获取条件
     * @return bool|mixed   返回类型
     */
    public function selectOnGoingOrder($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT od.*,info.user_location_longitude,info.user_location_latitude,info.service_name,info.service_id,info.user_address,info.brand_name,us.avatar FROM " . $this->tablename . " od LEFT JOIN md_xiuche.md_xiu_order_info info on od.id = info.order_id LEFT JOIN md_lixiang.md_user us on od.fix_id = us.id WHERE (od.order_status = 0 or od.order_status = 1 or od.order_status = 2 or od.order_status = 5) " . $where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据用户 获取用户下订单数量
     * @param $parames      条件(用户)
     * @return bool|mixed
     */
    public function selectOrderAmount($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT count(1) count FROM " . $this->tablename . " WHERE 1=1 " . $where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据条件以及时间查找服务信息
     * @param $parames  查询维修服务条件
     * @param $strtime  开始时间
     * @param $endtime  结束时间
     * @return bool|mixed
     */
    public function selectServeDataTime($parames, $strtime, $endtime)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT * FROM " . $this->tablename . " WHERE 1=1 " . $where . " and pay_time between " . $strtime . " and " . $endtime;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 修改订单信息
     * @param $data     修改订单信息参数
     * @param $wheres   修改订单条件
     * @return bool     返回参数
     */
    public function updateXiuOrderByAttr($data, $wheres)
    {
        $update = $this->update($data, $wheres);
        if ($update > 0) {
            return true;
        } else {
            return false;
        }
    }


    //修改订单
    public function updateOrderByAttr($data, $where)
    {
        $update = $this->update($data, $where);
        return $update;
    }

    //查询进行中的订单,单表
    public function selectCreatingOrder($parames)
    {
        $where = "";
        foreach ($parames as $k => $v) {
            $where .= " AND " . $k . " = '" . $v . "'";
        }
        $sql = " SELECT * FROM " . $this->tablename . " od WHERE (od.order_status = 0 or od.order_status = 1 or od.order_status = 2 or od.order_status = 5) " . $where;
        return $this->getCacheRowArray($sql);
    }





}
