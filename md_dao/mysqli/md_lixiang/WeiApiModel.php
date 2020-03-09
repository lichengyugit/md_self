<?php
class WeiApiModel extends DB_Model {
    /*protected $tables = array(
            //'user' => 'cro.sx_xiu_service' 
    );*/

    public function __construct() {
        parent::__construct($this->dbname, 'md_user_wallet');
        $this->log->log_debug('WeiApiModel  model be initialized');
    }
    
    /**
     * 用户余额购买月卡
     * @param $orderInfo 月卡订单信息
     * @param $cardRecord 月卡表信息
     * @param $updateWallet 钱包表更改信息
     * @param $log 钱包日志表信息
     * @return bool
     */
    public function balancePayCard($orderInfo,$cardRecord,$updateWallet,$log){
        $this->write_db->trans_begin();
        try{
            //向月卡订单表插入数据
            $cardId = M_Mysqli_Class('md_lixiang','CardPaymentModel')->insertCardPayment($orderInfo);
            //如果添加失败抛出异常
            if(!$cardId){
                throw new \Exception('月卡订单表添加失败');
            }
            
            if(isset($cardRecord['id'])){//这是用户续费月卡
               $updateCard = M_Mysqli_Class('md_lixiang','UserCardModel')->updateUserCard($cardRecord);
               if(!$updateCard){
                   throw new \Exception('用户月卡表更新失败');
               }
            }else{//这是用户新购买月卡
               $insertCard = M_Mysqli_Class('md_lixiang','UserCardModel')->insertUserCard($cardRecord);
               if(!$insertCard){
                   throw new \Exception('用户月卡表添加失败');
               }
            }
            //更改用户钱包金额
            //$updateMoney=$this->update(['user_id'=>$orderInfo['user_id']], $updateWallet);
            $updateMoney = M_Mysqli_Class('md_lixiang','UserWalletModel')->actionUserWalletByAttr($updateWallet);
            if(!$updateMoney){
                throw new \Exception('更改用户余额失败');
            }
            //添加钱包操作日志
            $log['primary_id']=$cardId;
            $insertLog=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->addUserWalletLog($log);
            if(!$insertLog){
                throw new \Exception('添加钱包日志失败');
            }
            //更改用户表用户状态
            M_Mysqli_Class('md_lixiang','UserModel')->updateUser(['id'=>$orderInfo['user_id'],'id_card'=>1,'card_type'=>$orderInfo['card_id']]);
            //成功提交,返回true
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            //var_dump($e->getMessage());die;
            return false;
        }
    }
    
    /**
     * 多颗电池集团支付押金
     * @param $pledgeOrderInfo 押金订单信息
     * @param $batterys 用户绑定电池信息
     * @return bool
     */
    public function companyPayPledgeOrder($pledgeOrderInfo,$batterys){
        $this->write_db->trans_begin();
        try{
            //向押金订单表插入数据
            $pledgeOrderId = M_Mysqli_Class('md_lixiang','PledgeOrderModel')->addOrder($pledgeOrderInfo);
            //如果添加失败抛出异常
            if(!$pledgeOrderId){
                throw new \Exception('押金订单表添加失败');
            }
            
            //拼接中间表信息
            $arr=[];
            foreach ($batterys as $k=>$v){
                $arr[$k]['userId']=$pledgeOrderInfo['user_id'];
                $arr[$k]['pledgeOrderId']=$pledgeOrderId;
                $arr[$k]['batteryNum']=$v;
            }
            //向中间表插入数据
            $pledgeBatteryId=M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->insertInfos($arr);
            //如果添加失败抛出异常
            if(!$pledgeBatteryId){
                throw new \Exception('押金电池中间表添加失败');
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
    
    /**
     * 绑定电池和添加押金电池中间表事务
     * @param $batteryInfo 绑定电池信息
     * @param $correlationTable 中间表数据
     * @return bool
     */
    public function correlation($batteryInfo,$correlationTable){
        $this->write_db->trans_begin();
        try{
            //更新电池状态
            $batteryStatus = M_Mysqli_Class('md_lixiang', 'BatteryModel')->updateBattery($batteryInfo);
            //如果添加失败抛出异常
            if(!$batteryStatus){
                throw new \Exception('电池表更新失败');
            }
        
            //向中间表插入数据
            $pledgeBatteryId=M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->addData($correlationTable);
            //如果添加失败抛出异常
            if(!$pledgeBatteryId){
                throw new \Exception('押金电池中间表添加失败');
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
    
    /**
     * 多颗电池用户申请退电池
     * @param $batteryArr 需解绑电池的数组
     * @param $userId 用户id
     * @param $inserData 需要写入退押金表中的信息
     * @return bool
     */
    public function unbindBattery($batteryArr,$userId,$inserData){
        $this->write_db->trans_begin();
        try{
            //更新电池状态
            $batteryStatus = M_Mysqli_Class('md_lixiang', 'BatteryModel')->updateInWhereBattery($batteryArr,['battery_status'=>7]);
            //如果添加失败抛出异常
            if(count($batteryArr)!=$batteryStatus){
                throw new \Exception('电池表更新失败');
            }
        
            //更改中间表数据
            $pledgeBatteryId=M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->updateInWhereBattery($batteryArr,['battery_status'=>1],['user_id'=>$userId]);
            //如果添加失败抛出异常
            if(count($batteryArr)!=$pledgeBatteryId){
                throw new \Exception('押金电池中间表更新失败');
            }
            //向退押金表添加数据
            $deposit=M_Mysqli_Class('md_lixiang','DepositRefundModel')->insertInfos($inserData);
            if(count($inserData)!=$deposit){
                throw new \Exception('退押金表添加失败');
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
    
    /**
     * 多颗电池用户取消申请退电池
     * @param $batteryNum 电池编号
     * @param $userId userId
     * @return bool
     */
    public function cancelUnbindBattery($batteryNum,$userId){
        $this->write_db->trans_begin();
        try{
            //更新电池状态
            $batteryStatus = M_Mysqli_Class('md_lixiang', 'BatteryModel')->updateBattery(['battery_num'=>$batteryNum,'battery_status'=>0]);
            //如果添加失败抛出异常
            if(!$batteryStatus){
                throw new \Exception('电池表更新失败');
            }
            //更改中间表数据
            $pledgeBatteryId=M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->updateInfoById(['battery_status'=>0],['battery_num'=>$batteryNum,'user_id'=>$userId]);
            //如果添加失败抛出异常
            if(!$pledgeBatteryId){
                throw new \Exception('押金电池中间表更新失败');
            }
            //删除退押金表数据
            $deposit=M_Mysqli_Class('md_lixiang','DepositRefundModel')->delectInfoByAttr(['battery_num'=>$batteryNum,'user_id'=>$userId]);
            if(!$deposit){
                throw new \Exception('退押金表删除失败');
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
    
    /**
     * 单颗电池用户申请和取消申请退押金
     * @param $pledgeData 押金表数据
     * @param $depositData 押金退还表数据
     * @param $type 类型 1是用户申请退 2是取消申请退
     */
    public function userApplyRecedeMoney($pledgeData,$depositData,$type=1){
        $this->write_db->trans_begin();
        try{
            //更新电池状态
            $pledgeStatus = M_Mysqli_Class('md_lixiang', 'PledgeOrderModel')->updateOrderById($pledgeData);
            //如果添加失败抛出异常
            if(!$pledgeStatus){
                throw new \Exception('押金表更新失败');
            }
            if($type==1){
                //退押金表添加数据
                $deposit=M_Mysqli_Class('md_lixiang','DepositRefundModel')->addData($depositData);
            }else{
                //删除退押金表数据
                $deposit=M_Mysqli_Class('md_lixiang','DepositRefundModel')->delectInfoByAttr($depositData);
            }
            if(!$deposit){
                throw new \Exception('退押金表添加失败');
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
    
    /**
     * 集团资金池支付用户押金
     * @param $userId 用户id
     * @param $pledgeMoney 押金金额
     * @param $balanceMoney 集团资金池支付完此次后剩余金额
     * @param $companyId 集团id
     */
    public function companyUserPayPledge($userId,$pledgeMoney,$balanceMoney,$companyId){
        $this->write_db->trans_begin();
        try{
            //添加资金池操作记录
            $record = M_Mysqli_Class('md_lixiang', 'CompanyMoneyRecordModel')->saveCompanyMoneyRecord(['company_id'=>$companyId,'amount'=>$pledgeMoney,'type'=>2,'user_id'=>$userId]);
            //如果添加失败抛出异常
            if(!$record){
                throw new \Exception('记录表更新失败');
            }
                //更改资金池剩余金额
            $balance=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->updateCompanyMoneyByAttr(['company_id'=>$companyId,'balance'=>$balanceMoney]);
            
            if($balance<1){
                throw new \Exception('余额更新失败');
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
    
    /**
     * 注册用户
     * @param $userInfo
     */
    public function regNewUser($userInfo){
        $this->write_db->trans_begin();
        try{
            //添加用户表记录
            $userId = M_Mysqli_Class('md_lixiang', 'UserModel')->addUser($userInfo);
            //如果添加失败抛出异常
            if(!$userId){
                throw new \Exception('用户表插入失败');
            }
            //添加用户钱包表
            $wallet=M_Mysqli_Class('md_lixiang','UserWalletModel')->addWallet(['user_id'=>$userId]);
            
            if(!$wallet){
                throw new \Exception('钱包表插入失败');
            }
            $this->write_db->trans_commit();
            return $userId;
        }catch (\Exception  $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            //var_dump($e->getMessage());die;
            return false;
        }
    }
    
    /**
     * 魔力后台导入用户押金信息
     * @param $depositArr 插入退押金记录表数据 array
     */
    public function synchroUserPledgeInfo($depositArr){
        $this->write_db->trans_begin();
        try{
            //新增退押金表记录
            $deposit=M_Mysqli_Class('md_lixiang','DepositRefundModel')->addData($depositArr);
            
            if(!$deposit){
                throw new \Exception('退押金表插入失败');
            } 
            //新增退押金表记录
            $pledge=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr(['status'=>2,'pledge_money_status'=>2,'apply_recede_time'=>time()],['id'=>$depositArr['order_id']]);
            
            if(!$pledge){
                throw new \Exception('押金表更新失败');
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
    
    /**
     * 魔力后台导入用户月卡信息
     * @param $cardArr 插入月卡购买表数据 array
     * @param $type 新增用户月卡表或更新用户月卡表
     */
    public function synchroUserMonthCardInfo($cardArr,$type){
        $this->write_db->trans_begin();
        try{
            //新增月卡购买表记录
            $cardLog=M_Mysqli_Class('md_lixiang','CardPaymentModel')->insertCardPayment($cardArr);
        
            if(!$cardLog){
                throw new \Exception('月卡购买订单表插入失败');
            }
            //新增或更新用户月卡表记录
            if($type){
                $card=M_Mysqli_Class('md_lixiang','UserCardModel')->updateUserCard(['id'=>$type,'over_time'=>$cardArr['over_time']]);
            }else{
                $card=M_Mysqli_Class('md_lixiang','UserCardModel')->insertUserCard(['user_id'=>$cardArr['user_id'],'over_time'=>$cardArr['over_time']]);
            }
        
            if(!$card){
                throw new \Exception('用户月卡表更新失败');
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
