<?php
class BankOutMoneyModel extends DB_Model {
    protected $tables = array(
            'merchant' => 'md_lixiang.md_merchant'
    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_bank_out_money');
        $this->log->log_debug('BankOutMoneyModel  model be initialized');
    }

    /**
     * 用户提现申请
     * @param $userId 用户id
     * @param $bankNumber 卡号
     * @param $outMoney 提现金额
     * @param $moneyOutOrder 提现订单号
     * @return bool
     */
    public function userBankOut($userId, $bankNumber, $outMoney, $moneyOutOrder){
        $this->write_db->trans_begin();
        try{
            //查询用户钱包
            $userMoney = M_Mysqli_Class('md_lixiang','UserWalletModel')->getWalletByUserId($userId);
            $balance = $userMoney['balance'] - $outMoney;
            if ($balance < 0){
                throw new \Exception('用户余额不足！');
            }
            $updateWallet['id'] = $userId;
            $updateWallet['balance'] = $balance;
            //更改用户钱包
            $updateMoney = M_Mysqli_Class('md_lixiang','UserWalletModel')->actionUserWalletByAttr($updateWallet);
            if(!$updateMoney){
                throw new \Exception('更改用户余额失败');
            }
            //添加钱包操作日志
           $log= [//拼日志数据
                'user_id'=>$userId,
                'wallet_id'=>$userMoney['id'],
                'amount'=>$outMoney,
                'before_balance' => $userMoney['balance'],
                'after_balance' => $balance,
                'before_giving_balance'=>$userMoney['giving_balance'],
                'after_giving_balance' =>$userMoney['giving_balance'],
                'before_red_packet_balance'=>$userMoney['red_packet_balance'],
                'after_red_packet_balance'=>$userMoney['red_packet_balance'],
                'income_type'=>2,
                'type'=>5,
                'remark' => '用户提现',
                'primary_id' => $userId,
            ];
            $insertLog=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->addUserWalletLog($log);
            if(!$insertLog){
                throw new \Exception('添加钱包日志失败');
            }
            //向提现申请表插入数据
            $bankOutId = $this->insertCardPayment($userId, $bankNumber, $outMoney, $moneyOutOrder);
            //如果添加失败抛出异常
            if(!$bankOutId){
                throw new \Exception('提现申请表添加失败');
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

    public  function  insertCardPayment($userId, $bankNumber, $outMoney, $moneyOutOrder){
        $data['user_id'] = $userId;
        $data['out_money'] = $outMoney;
        $data['bank_num'] = $bankNumber;
        $data['order_sn'] = $moneyOutOrder;
        $data['creat_time']=date("Y-m-d H:i:s",time());
        $data['create_date']=date("Y-m-d H:i:s",time());
        return $this->insert($data);
    }

    /*
     * 获取全部数据
     * */
        public function getAgentBalanceData($limitAndOrderBY='',$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT BO.*,M.mobile,M.name FROM ".$this->tablename." AS BO LEFT JOIN 
        ".$this->tables['merchant']." AS M ON BO.user_id=M.attr_id 
        WHERE BO.status < 2 ".$where.$limitAndOrderBY;
        return $this->getCacheResultArray($sql,$where);
    }

    /*
     * 获取全部数据
     * */
    public function getAgentBalanceSearch($limit='',$parames){
        $where="";
        $where.=" AND state=".$parames['state'];
        if(!empty($parames['select'])){
            if(preg_match("/^1[345678]{1}\d{9}$/",$parames['select'])){
                $where.=" AND M.mobile=".$parames['select'];
            }elseif(substr($parames['select'],0,2)=='BM'){
                $where.=" AND BO.order_sn=".'"'.$parames['select'].'"';
            }else{
                $where.=" AND M.name LIKE "."'%". $parames['select'] ."%'";
            }
//            if(preg_match("/^([1-9]{1})(\d{14}|\d{18})$/",$parames['select'])){
//                $where.=" AND BO.bank_num=".'"'.$parames['select'].'"';               //验证银行卡
//            }else
        }
        $sql = " SELECT BO.*,M.mobile,M.name FROM ".$this->tablename." AS BO LEFT JOIN
        ".$this->tables['merchant']." AS M ON BO.user_id=M.attr_id
        WHERE BO.status < 2 ".$where." ORDER BY BO.id DESC ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }

    /**
     * 根据条件获取数量
     */
    public function getNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }

    /*
     * 修改
     * */
    public function updateData($parames)
    {
        $wheres['id']=$parames['id'];
        unset($parames['id']);
        $parames['end_time']=time();
        $update=$this->update($parames, $wheres);
        if($update > 0){
            return $update;
        }
        else{
            return false;
        }
    }
}

