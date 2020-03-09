<?php
header("content-type:text/html;charset=utf-8");
class ShopTranstionModel extends Db_Model{
    protected $tables = array(
        'merchant'=>'md_lixiang.md_merchant',
    );
    public function __construct() {
        parent::__construct('md_shop', 'md_vehicle');
        $this->log->log_debug('ShopTranstionModel  model be initialized');
    }


    /*
     * 购买车辆订单添加与订单分配给最近商家
     * @parames                 [
     *                           order_sn                           订单编号
     *                           pay_status                         支付状态
     *                           order_status                       订单状态
     *                           user_id                            用户id
     *                           user_mobile                        用户手机号
     *                           user_name                          用户名称
     *                           lease_amount                       总金额
     *                           discounts_amount                   优惠后总金额
     *                           pay                                实际付款
     *                           pay_time                           付款时间
     *                           receiver_location                  收货地址
     *                           receiver_name                      收货人名称
     *                           receiver_mobile                    收货人手机号
     *                           post_status                        物流状态
     *                           post_number                        物流编号
     *                           merchant_id                        商家id
     *                           merchant_name                      商家名称
     *                           merchant_location                  商家地址
     *                           pickup_way                         提货方式
     *                           status                             状态
     *                           goods_num                          商品数量
     *                           unit_price                         商品单价
     *                           unit_discounts                     优惠后单价
     *                           brand_id                           品牌id
     *                           brand_name                         品牌名称
     *                           brand_series_id                    系列id
     *                           brand_series_name                  系列名称
     *                           brand_series_type_id               型号id
     *                           brand_series_type_name             型号名称
     *                           goods_color                        商品颜色
     *                           goods_tension                      商品电压
     *                           ]
     *
     * @return            bool
     * */
    public function buyVehicleOrderDispose($parames)
    {
        $this->write_db->trans_begin();
        try{

            //修改商家库存数量
            $upGoodsRepertoryStatus = M_Mysqli_Class('md_shop','GoodsRepertoryModel')->update('repertory_num-'.$parames['goods_num'],'id='.$parames['goods_repertory_id']);
            if($upGoodsRepertoryStatus < 1){
                throw new \Exception('商家库存表更新失败');
            }
            //添加订单数据
            $buyOrderData=[
                'order_sn'=>$parames['order_sn'],
                'pay_status'=>$parames['pay_status'],
                'order_status'=>$parames['order_status'],
                'goods_repertory_id'=>$parames['goods_repertory_id'],
                'user_id'=>$parames['user_id'],
                'user_mobile'=>$parames['mobile'],
                'user_name'=>$parames['name'],
                'lease_amount'=>$parames['lease_amount'],
                'discounts_amount'=>$parames['lease_amount'],
                'pay'=>$parames['pay'],
                'pay_ment'=>$parames['pay_ment'],
                'pay_time'=>time(),
                'receiver_location'=>0,
                'receiver_name'=>0,
                'receiver_mobile'=>0,
                'post_status'=>0,
                'post_number'=>0,
                'merchant_id'=>$parames['merchant_id'],
                'merchant_name'=>$parames['merchant_name'],
                'merchant_location'=>$parames['merchant_location'],
                'pickup_way'=>1,
                'status'=>0,
            ];
            $buyOrderSaveStatus = M_Mysqli_Class('md_shop','BuyOrderModel')->saveBuyOrder($buyOrderData);
            if($buyOrderSaveStatus < 1){
                throw new \Exception('购车订单表添加失败');
            }

            $buyOrderInfoData=[
                'order_id'                =>$buyOrderSaveStatus,
                'goods_num'               =>$parames['goods_num'],
                'unit_price'              =>$parames['unit_price'],
                'unit_discounts'          =>$parames['unit_price'],
                'brand_id'                =>$parames['brand_id'],
                'brand_name'              =>$parames['brand_name'],
                'brand_series_id'         =>$parames['brand_series_id'],
                'brand_series_name'       =>$parames['brand_series_name'],
                'brand_series_type_id'    =>$parames['brand_series_type_id'],
                'brand_series_type_name'  =>$parames['brand_series_type_name'],
                'goods_color'             =>$parames['goods_color'],
                'goods_tension'           =>$parames['goods_tension'],
                'status'                  =>0,
            ];
            $buyOrderInfoSaveStatus = M_Mysqli_Class('md_shop','BuyOrderInfoModel')->saveBuyInfoOrder($buyOrderInfoData);
            if($buyOrderInfoSaveStatus < 1){
                throw new \Exception('购车订单商品表添加失败');
            }
            $mem = new Memcache();
            $mem->addserver(MEMCACHE_IP,MEMCACHE_PORT);
            $mem->delete($parames['order_sn']);
            //成功提交,返回true
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;

        }
    }

    /**
     * 取消租车订单方法
     * @param $orderInfo[order_id  pay] 订单表信息 array
     * @param  $reserveInfo[……] 预定表信息
     * @param  $walletInfo[……]  用户钱包表信息
     * @param  $walletLogInfo[……] 用户钱包log表信息
     */
    public function cancelRentOrder($orderInfo,$reserveInfo,$walletInfo,$walletLogInfo){
        $this->write_db->trans_begin();
        try{
            //更改租车订单状态
            $updateOrder=M_Mysqli_Class('md_shop','RentOrderModel')->updateRentOrderByAttr(['order_status'=>4],$orderInfo);
            if($updateOrder < 1 ){
                throw new \Exception('订单状态修改失败');
            }

            //删除车辆预定表信息
            $del=M_Mysqli_Class('md_shop','VehicleReserveModel')->delectInfoByAttr($reserveInfo);
            if($del < 1 ){
                throw new \Exception('车辆预定表信息删除失败');
            }

            //用户钱包表添加金额
            $updateWallet=M_Mysqli_Class('md_shop','UserWalletModel')->actionUserWalletByAttr($walletInfo);
            if($updateWallet < 1 ){
                throw new \Exception('用户钱包表更新失败');
            }

            //添加钱包log表
            $addWalletLog=M_Mysqli_Class('md_shop','UserWalletLogModel')->addUserWalletLog($walletLogInfo);
            if($addWalletLog < 1 ){
                throw new \Exception('用户钱包表log新增失败');
            }

            //成功提交,返回true
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;

        }
    }


    /*
     * 用户租车订单下单
     * @parames     [
     *               order_sn                      订单编号
     *               pay_status                    支付状态
     *               order_status                  订单状态
     *               user_id                       用户id
     *               user_name                     用户名称
     *               user_mobile                   用户手机号
     *               get_vehicle_time              取车时间
     *               return_vehicle_time           还车时间
     *               lease                         总金额
     *               discounts                     优惠后总金额
     *               pay                           实际付款金额
     *               pay_ment                      支付方式
     *               pay_time                      支付时间
     *               pay_ment                      支付类型
     *               merchant_id                   商家id
     *               merchant_name                 商家名称
     *               merchant_location             商家地址
     *               goods_repertory_id            商家库存id
     *               brand_id                      品牌id
     *               brand_name                    品牌名称
     *               brand_series_id               系列id
     *               brand_series_name             系列名称
     *               status
     *               ]
     * @return    bool
     * */
    public function userPlaceAnRentOrder($parames)
    {
        $this->write_db->trans_begin();
        try{
            $saveRentData=[
                'order_sn'               =>$parames['order_sn'],
                'pay_status'             =>2,
                'order_status'           =>1,
                'user_id'                =>$parames['user_id'],
                'user_name'              =>$parames['name'],
                'user_mobile'            =>$parames['mobile'],
                'get_vehicle_time'       =>strtotime($parames['get_vehicle_time']),
                'return_vehicle_time'    =>strtotime($parames['return_vehicle_time']),
                'lease'                  =>$parames['lease'],
                'discounts'              =>$parames['lease'],
                'pay'                    =>$parames['lease'],
                'pay_time'               =>time(),
                'pay_ment'               =>1,
//                'pay_ment'               =>$parames['pay_ment'],
                'merchant_id'            =>$parames['merchant_id'],
                'merchant_name'          =>$parames['merchant_name'],
                'merchant_location'      =>$parames['merchant_location'],
                'brand_id'               =>$parames['brand_id'],
                'brand_name'             =>$parames['brand_name'],
                'brand_series_id'        =>$parames['brand_series_id'],
                'brand_series_name'      =>$parames['brand_series_name'],
                'status'                 =>0,
            ];
            //添加租车订单表信息
            $addRentOrderData=M_Mysqli_Class('md_shop','RentOrderModel')->saveRentOrder($saveRentData);
            if($addRentOrderData < 1 ){
                throw new \Exception('租车订单表添加失败');
            }
            $saveVehicleData=[
                  'merchant_id'       =>$parames['merchant_id'],
                  'goods_repertory_id'=>$parames['goods_repertory_id'],
                  'user_id'           =>$parames['user_id'],
                  'atart_time'        =>$saveRentData['get_vehicle_time'],
                  'end_time'          =>$saveRentData['return_vehicle_time'],
                  'status'            =>0,
            ];
            //添加车辆预定信息
            $addVehicleData=M_Mysqli_Class('md_shop','VehicleReserveModel')->saveVehicleData($saveVehicleData);
            if($addVehicleData < 1 ){
                throw new \Exception('租车预定表添加失败');
            }
            $mem = new Memcache();
            $mem->addserver(MEMCACHE_IP,MEMCACHE_PORT);
            $mem->delete($parames['order_sn']);
            //成功提交,返回true
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;

        }
    }

    /*
     * 用户取消购车订单
     * $walletUpData  钱包信息            [
     *                                      balance                 总余额
     *                                      total_balance           历史总余额
     *                                   ]
     *
     * $walletLogAddData  钱包log信息    [
     *                                     user_id                    用户id
     *                                     wallet_id                  钱包id
     *                                     amount                     操作金额
     *                                     before_balance             操作前余额
     *                                     after_balance              操作后余额
     *                                     type                       操作类型
     *                                     primary_id                 操作表id
     *                                     income_type                流水类型  2支出   1收入
     *                                  ]
     * */
    public function userCancelBuyOrder($walletUpData,$walletLogAddData,$goodsRepertoryId)
    {$this->write_db->trans_begin();
        try{
            //修改订单信息
            $buyOrderStatus=M_Mysqli_Class('md_shop','BuyOrderModel')->updateBuyOrderByAttr(['order_status'=>3],['id'=>$walletLogAddData['primary_id']]);
            if($buyOrderStatus < 1 ){
                throw new \Exception('购车订单表修改失败');
            }

            //修改用户钱包信息
            $updateWallet=M_Mysqli_Class('md_shop','UserWalletModel')->actionUserWalletByAttr($walletUpData);
            if($updateWallet < 1 ){
                throw new \Exception('用户钱包表更新失败');
            }

            //添加钱包log表
            $addWalletLog=M_Mysqli_Class('md_shop','UserWalletLogModel')->addUserWalletLog($walletLogAddData);
            if($addWalletLog < 1 ){
                throw new \Exception('用户钱包表log添加失败');
            }

            //添加钱包log表
            $goodsRepertoryData=M_Mysqli_Class('md_shop','BuyOrderInfoModel')->getBuyOrderInfosByAttrOne(['order_id'=>$walletLogAddData['primary_id']]);
            $goodsRepertoryStatus=M_Mysqli_Class('md_shop','GoodsRepertoryModel')->update('repertory_num+'.$goodsRepertoryData['goods_num'],'id='.$goodsRepertoryId);
            if($goodsRepertoryStatus < 1 ){
                throw new \Exception('商家库存表修改库存失败');
            }
            //成功提交,返回true
            $this->write_db->trans_commit();
            return true;
            }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;

        }
    }
}