<?php
class RentCarAffairModel extends DB_Model {
    /*protected $tables = array(
            //'user' => 'cro.sx_xiu_service'
    );*/

    public function __construct() {
        parent::__construct('md_rent_car', 'md_order');
        $this->log->log_debug('RentCarAffairModel  model be initialized');
    }

    /**
     * 商户确认车辆归还
     * @param orderId 用户订单表id
     * @param orderUpdata 用户订单表更改数据 array
     * @param carId 归还车辆id
     * @param carUpdata 车辆表更改数据 array
     * @param carLogData 车辆Log表插入数据
     */
    public function checkReturnCar($orderId,$orderUpdata,$carId,$carUpdata,$carLogData){
        $this->write_db->trans_begin();
        try{
            //更新用户订单表
            $order = M_Mysqli_Class('md_rent_car', 'OrderModel')->updateOrderByAttr($orderUpdata,['id'=>$orderId]);
            //如果更新失败抛出异常
            if($order<=0){
                throw new \Exception('订单表更新失败');
            }

            //更新车辆表
            $car=M_Mysqli_Class('md_rent_car','VehicleModel')->updateWheresVehicle($carUpdata,['id'=>$carId]);

            if($car<=0){
                throw new \Exception('车辆表更新失败');
            }

            //插入车辆log表数据
            $insert=M_Mysqli_Class('md_rent_car','VehicleLogModel')->addCarLog($carLogData);
            if($insert<=0){
                throw new \Exception('车辆日志表新增失败');
            }
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            //var_dump($e->getMessage());die;
            return false;
        }
    }

    /*
     * 用户租车下单
     * */
    public function userBuyOrder($upData,$orderData)
    {
        $this->write_db->trans_begin();
        try{
            $OuprderData =  M_Mysqli_Class('md_rent_car', 'OrderModel')->getOrderByAttr(['id'=>$orderData['up_order_id']]);
            if($orderData['order_type']==1){
                $orderUp=[
                    'pay_status'  =>$upData['pay_status'],
                    'order_status'=>$upData['order_status'],
                    'pay_ment'    =>$upData['pay_ment'],
                    'pay'         =>$upData['pay'],
                    'pay_time'    =>$upData['pay_time'],
                    'rent_time'   =>$orderData['extend_time']+$OuprderData['rent_time'],
                ];
            }else{
                $updateOrder=M_Mysqli_Class('md_rent_car', 'OrderModel')->updateOrderByAttr(['order_status'=>3],['id'=>$orderData['up_order_id']]);
                //如果更新失败抛出异常
                if($updateOrder <= 0){
                    throw new \Exception('订单表更新失败');
                }
                $orderUp=[
                    'pay_status'  =>$upData['pay_status'],
                    'order_status'=>$upData['order_status'],
                    'pay_ment'    =>$upData['pay_ment'],
                    'pay'         =>$upData['pay'],
                    'pay_time'    =>$upData['pay_time'],
                    'rent_time'   =>$orderData['extend_time']+$OuprderData['rent_time'],
                ];
            }

            $orderStatus=M_Mysqli_Class('md_rent_car', 'OrderModel')->updateOrderByAttr($orderUp,['id'=>$orderData['id']]);
            //如果更新失败抛出异常
            if($orderStatus <= 0){
                throw new \Exception('订单表更新失败');
            }
            if($orderData['order_type']==1){
                $vehicleData=[
                    'user_id'     =>$orderData['user_id'],
                    'user_name'   =>$orderData['user_name'],
                    'user_mobile' =>$orderData['user_mobile'],
                    'rent_status' =>2,
                    'begin_time'  =>$orderData['begin_time'],
                    'end_time'    =>$orderData['end_time'],
                ];
            }else{
                $vehicleData=[
                    'user_id'     =>$orderData['user_id'],
                    'user_name'   =>$orderData['user_name'],
                    'user_mobile' =>$orderData['user_mobile'],
                    'rent_status' =>2,
                    'end_time'    =>$orderData['end_time'],
                ];
            }

            $vehicleData=M_Mysqli_Class('md_rent_car', 'VehicleModel')->updateWheresVehicle($vehicleData,['id'=>$orderData['vehicle_id']]);
            //如果更新失败抛出异常
            if($vehicleData <= 0){
                throw new \Exception('车辆表更新失败');
            }

            $vehicleLogData=[
                'vehicle_number'=>$orderData['vehicle_number'],
                'vehicle_brand' =>$orderData['vehicle_brand'],
                'vehicle_id'    =>$orderData['vehicle_id'],
                'operator_name' =>$orderData['user_name'],
                'rent_time'     =>$orderData['extend_time'],
                'operator_id'   =>$orderData['user_id'],
                'operator_type' =>1,
            ];
            if($orderData['order_type']==1){
                $vehicleLogData['operation_type']=1;
            }else{
                $vehicleLogData['operation_type']=7;
            }
            $vehicleStatus=M_Mysqli_Class('md_rent_car', 'VehicleLogModel')->addCarLog($vehicleLogData);
            //如果更新失败抛出异常
            if($vehicleStatus <= 0){
                throw new \Exception('车辆log表更新失败');
            }
            $userCardData=M_Mysqli_Class('md_lixiang', 'UserCardModel')->getUserCardByAttr(['user_id'=>$orderData['user_id']]);
            if(time() > $userCardData['over_time'] || empty($userCardData)){
                if($orderData['order_type']==1){
                    $vehicleLogStatus=M_Mysqli_Class('md_lixiang', 'UserModel')->updateUserByAttr(['id_card'=>1,'card_type'=>1],['id'=>$orderData['user_id']]);
                    //如果更新失败抛出异常
                    if($vehicleLogStatus <= 0){
                        throw new \Exception('用户表更新失败');
                    }
                }
                if($orderData['order_type']==1){
                    $userCardStatus=M_Mysqli_Class('md_lixiang', 'UserCardModel')->insertUserCard(['user_id'=>$orderData['user_id'],'over_time'=>$orderData['end_time']]);
                }else{
                    $userCardStatus=M_Mysqli_Class('md_lixiang', 'UserCardModel')->updateUserCardData(['over_time'=>$orderData['end_time']],['user_id'=>$orderData['user_id']]);
                }

                //如果更新失败抛出异常
                if($userCardStatus <= 0){
                    throw new \Exception('用户月卡表添加失败');
                }
            }else{
                $overTime=$orderData['extend_time']*86400;
                $userCardStatus=M_Mysqli_Class('md_lixiang', 'UserCardModel')->updateUserCardData($overTime,$orderData['user_id']);
                if($userCardStatus <= 0){
                    throw new \Exception('用户月卡表添加失败');
                }
            }




            $merchantData=M_Mysqli_Class('md_lixiang', 'MerchantModel')->getMerchantInfoByAttr(['id'=>$orderData['merchant_id']]);
            $merchanWalletData=M_Mysqli_Class('md_rent_car', 'UserWalletModel')->getWalletByUserId(['id'=>$merchantData['attr_id']]);
            $welletData=[
                'balance'      =>$merchanWalletData['balance']+2,             //余额加佣金            改
                'total_balance'=>$merchanWalletData['total_balance']+2,       //总余额               改
                'id'           =>$merchantData['attr_id'],       //总余额
            ];
            $merchanWalletStatus=M_Mysqli_Class('md_rent_car', 'UserWalletModel')->actionUserWalletByAttr($welletData);
            //如果更新失败抛出异常
            if($merchanWalletStatus <= 0){
                throw new \Exception('钱包表更新失败');
            }

            $welletLogData=[
                'user_id'           =>$merchantData['attr_id'],
                'wallet_id'         =>$merchanWalletData['id'],
                'amount'            =>2,                                     //改
                'before_balance'    =>$merchanWalletData['balance'],
                'after_balance'     =>$merchanWalletData['balance']+2,       //改
                'income_type'       =>1,
                'type'              =>3,
                'primary_id'        =>$orderData['user_id'],
            ];
            $merchanWalletLogStatus=M_Mysqli_Class('md_rent_car', 'UserWalletLogModel')->addUserWalletLog($welletLogData);
            //如果更新失败抛出异常
            if($merchanWalletLogStatus <= 0){
                throw new \Exception('钱包log表添加失败');
            }
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            //var_dump($e->getMessage());die;
            return false;
        }
    }
}
?>
