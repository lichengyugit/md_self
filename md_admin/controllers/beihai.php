<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class beihai extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "beihai");
    }

    /**
     * 北海用户列表
     */
    public function userList()
    {   //$this->checkAuth();
        F()->Resource_module->setTitle('北海用户列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));

        $url = "/beihaiList";
        $where['user_flag']=2;  //user_flag=2是北海用户
        $nums=M_Mysqli_Class('md_lixiang','UserModel')->getBeihaiUserByAttr($where);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','UserModel')->getAllBeihaiUserByAttr($showpage['limit'],$where);
        //print_r($arr);exit;
        $this->smarty->assign('arr',$arr);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('beihai/list.phtml');
    }


  /**
    * 修改用户状态
    */
   public function actionUserStatus(){
       //$this->checkAuth();
       $updateCompany=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($this->parames);
       if($updateCompany){
           $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1]);
           $this->msg('操作成功','/beihaiList','ok');
       }else{
           $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
           $this->msg('操作失败','/beihaiList','error');
       }
   }
    
    //添加北海用户
    public function addBHUser(){
        //$this->checkAuth();
        $action=$_SERVER['REQUEST_METHOD'];
        if($action=='POST'){
            $parames=$this->parames;     
                $this->form_validation->set_data($parames);
                $this->form_validation->set_rules('name','用户真实姓名','required');
                $this->form_validation->set_rules('idCardNumber','用户身份证号','exact_length[18]|required');
                $this->form_validation->set_rules('mobile','用户手机号','numeric|exact_length[11]|required');
                $this->form_validation->run();
                if($this->form_validation->run()===FALSE){
                    $this->msg($this->form_validation->validation_error(), '/addBHUser', 'error');
                }else{
                    $password=mt_rand(100000, 999999);
                    $res=F()->Idcard_module->UserIdentification($parames['name'],$parames['idCardNumber']);
                    if($res['result']['status']=='01'){
                        if($arr=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['mobile'=>$parames['mobile']])){
                            if($arr['status']==2){
                                $this->msg('该用户已被删除,请找相关人员了解详情', '/addBHUser', 'error');
                            }else{
                                $this->msg('此手机号已经存在', '/addBHUser', 'error');
                            }
                        }else{
                            $user=[
                                'user_name'=>$parames['name'],
                                'password'=>md5($password),
                                'user_flag'=>2,
                                //'open_id'=>md5($parames['mobile']),
                                'create_time'=>time(),
                                'create_date'=>date("Y-m-d H:i:s",time()),
                                'identification'=>1,
                                'identification_time'=>time(),
                                'create_ip'=>$this->getClientIP(),
                                'mobile'=>$parames['mobile'],
                                'plan_id'=>1,
                                'invite_code'=>$this->make_invite_code()
                            ];
                            if($id=M_Mysqli_Class('md_lixiang','UserModel')->addUser($user)){
                                $this->addUserWallet($id,$parames['topUpMoney']);
                                $this->addIdCardLog($res,$id,1);
                                $idCard=[
                                    'id'=>$id,
                                    'name'=>$res['result']['name'],
                                    'card_number'=>$res['result']['id']
                                ];
                                $this->addPledgeOrder($id);
                                $this->addIdCard($idCard);
                                F()->Sms_module->rSendSms($parames['mobile'],$id,3,$password);
                                $data=[
                                    'mw04'=>$user['user_name'],
                                    'mw15'=>$idCard['card_number'],
                                    'mw14'=>$user['mobile'],
                                    'mw12'=>$id,
                                    'mw13'=>$user['create_date']
                                ];
                                $outPut['call']= $this->regInform($data);
                                get_log()->log_api('<接口测试> #### 接口名：checkIdCard 作用：调用魔力后台微信注册会员通知接口参数：'.json_encode($data));
                                get_log()->log_api('<接口测试> #### 接口名：checkIdCard 作用：调用魔力后台微信注册会员通知接口后获取返回值：'.json_encode($outPut['call']));
                                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                                $this->msg('添加成功', '/addBHUser', 'ok');
                            }else{
                                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                                $this->msg('服务器繁忙', '/addBHUser', 'error');
                            }
                        }
                    }else{
                        $this->addIdCardLog($res,0,2);
                        $this->msg('实名认证不通过', '/addBHUser', 'error');
                    }
             }
        }else{ 
            F()->Resource_module->setTitle('添加北海用户');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('beihai/insert.phtml');
        }  
    }
    //用户订单列表
   public function beihaiUserOrderList(){
       //$this->checkAuth();
       $parames=$this->parames;
       F()->Resource_module->setTitle('用户订单列表');
       F()->Resource_module->setJsAndCss(array(
           'home_page'
       ), array(
           'main'
       ));
       $url='/beihaiUserOrderList?id='.$parames['id'];
       $nums=M_Mysqli_Class('md_lixiang','OrderModel')->getCountOrderByAttr(['user_id'=>$parames['id']]);
       $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
       $arr=M_Mysqli_Class('md_lixiang','OrderModel')->getAllOrder($showpage['limit'],['user_id'=>$parames['id']]); 
       $this->smarty->assign('arr',$arr);
       $this->smarty->assign("pages", $showpage['show']);
       $this->smarty->view('order/list.phtml');
   }
    
   //用户钱包流水
   public function beihaiUserWalletWater(){
       //$this->checkAuth();
       F()->Resource_module->setTitle('用户钱包流水');
       F()->Resource_module->setJsAndCss(array(
           'home_page'
       ), array(
           'main'
       ));
       $parames=$this->parames;
       $url='/beihaiUserWalletWater?id='.$parames['id'];
       $data['user_id']=$parames['id'];
       //print_r($parames);exit;
       $nums=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getNumByAttr($data);
       $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
       $arr=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getAllUserWalletLogPage($showpage['limit'],$data);
       //print_r($arr);exit;
       //$arr=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getUserWalletLogsInfoByAttr($data);
       foreach ($arr as $k=>$v){
           if($v['type']==1){
               $arr[$k]['type']='充值';
           }elseif($v['type']==2){
               $arr[$k]['type']='提现';
           }elseif($v['type']==3){
               $arr[$k]['type']='分佣';
           }elseif($v['type']==4){
               $arr[$k]['type']='赠送';
           }elseif($v['type']==5){
               $arr[$k]['type']='提现退款';
           }elseif($v['type']==6){
               $arr[$k]['type']='下单';
           }elseif($v['type']==7){
               $arr[$k]['type']='新用户赠送';
           }
       }
       //print_r($arr);exit;
       $this->smarty->assign('arr',$arr);
       $this->smarty->assign("pages", $showpage['show']);
       $this->smarty->view('beihai/userWalletWater.phtml');
   }
   
   //用户电池信息
   public function beihaiUserBattery(){
       //$this->checkAuth();
       F()->Resource_module->setTitle('用户电池信息');
       F()->Resource_module->setJsAndCss(array(
           'home_page'
       ), array(
           'main'
       ));
       $parames=$this->parames;
       $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByUser($parames['id']);
       //print_r($arr);exit;
       if($arr){
           $arr['binding_time']=date("Y-m-d H:i:s",$arr['binding_time']);
           $this->smarty->assign('arr',$arr);
           $this->smarty->view('beihai/userBattery.phtml');
       }else{
           $this->msg('用户还没有绑定电池','/beihaiList','error');
       }
       
   }
   
   //用户余额充值
   public function topUpbeihaiUser(){
       //$this->checkAuth();
       $action=$_SERVER['REQUEST_METHOD'];
       if($action=='POST'){
           $parames=$this->parames;
           $this->form_validation->set_data($parames);
           $this->form_validation->set_rules('topUpMoney','充值金额','numeric|required');
           $this->form_validation->run();
           if($this->form_validation->run()===FALSE){
               $this->msg($this->form_validation->validation_error(),'/topUpbeihaiUser?id='.$parames['id'],'error');
           }else{
           $data['balance']=$parames['topUpMoney']+$parames['balance'];
           $userWallet=M_Mysqli_Class('md_lixiang','UserWalletModel')->getWalletByUserId($parames['id']);
           if(M_Mysqli_Class('md_lixiang','UserWalletModel')->updateWalletBalance($data,$parames['id'])){
               $WalletLogData=[//拼日志数据
                   'user_id'=>$parames['id'],
                   'wallet_id'=>$userWallet['id'],
                   'amount'=>$parames['topUpMoney'],
                   'before_balance' => $userWallet['balance'],
                   'after_balance' => $data['balance'],
                   'before_giving_balance'=>$userWallet['giving_balance'],
                   'after_giving_balance' =>$userWallet['giving_balance'],
                   'before_red_packet_balance'=>$userWallet['red_packet_balance'],
                   'after_red_packet_balance'=>$userWallet['red_packet_balance'],
                   'income_type'=>1,
                   'type'=>1,
                   'primary_id'=>0
               ];
               if(M_Mysqli_Class('md_lixiang','UserWalletLogModel')->addUserWalletLog($WalletLogData)){
                   $this->msg('充值成功','/topUpbeihaiUser?id='.$parames['id'],'ok');
               }else{
                   $this->msg('服务器繁忙,请稍后重试。','/topUpbeihaiUser?id='.$parames['id'],'error');
               }
           }else{
               $this->msg('服务器繁忙,请稍后重试。','/topUpbeihaiUser?id='.$parames['id'],'error');
           }
         }
       }else{
           F()->Resource_module->setTitle('用户余额充值');
           F()->Resource_module->setJsAndCss(array(
               'home_page'
           ), array(
               'main'
           ));
           $parames=$this->parames;
           $arr=M_Mysqli_Class('md_lixiang','UserModel')->getUserNameAndIdNumber($parames['id']);
           $this->smarty->assign('arr',$arr);
           $this->smarty->view('beihai/topUp.phtml');
       }
   }
    //添加验证身份证日志表
    private function addIdCardLog($res,$userId,$result){
        $arr=$res['result'];
        if($result==1){
            $log=[
                'user_id'=>$userId,
                'code'=>$res['code'],
                'charge'=>$res['charge'],
                'msg'=>$res['msg'],
                'idcard'=>$arr['id'],
                'name'=>$arr['name'],
                'msg1'=>$arr['msg'],
                'status'=>$arr['status'],
                'success'=>$arr['success'],
                'sex'=>$arr['sex'],
                'area'=>$arr['area'],
                'birthday'=>$arr['birthday']
            ];
        }else{
            $log=[
                'user_id'=>$userId,
                'code'=>$res['code'],
                'charge'=>$res['charge'],
                'msg'=>$res['msg'],
                'idcard'=>$arr['id'],
                'name'=>$arr['name'],
                'msg1'=>$arr['msg'],
                'status'=>$arr['status']
            ];
        }
        M_Mysqli_Class('md_lixiang','IdCardLogModel')->addLog($log);
    }
    
    //添加身份证信息
    private function addIdCard($data){
        $where['user_id']=$data['id'];
        if($arr=M_Mysqli_Class('md_lixiang','IdCardModel')->getUserIdCardByAttr($where)){
            $update=[
                'id'=>$arr['id'],
                'user_id'=>$data['id'],
                'name'=>$data['name'],
                'card_number'=>$data['card_number']
            ];
            M_Mysqli_Class('md_lixiang','IdCardModel')->updateUserIdCard($update);
        }else{
            $data['user_id']=$data['id'];
            unset($data['id']);
            M_Mysqli_Class('md_lixiang','IdCardModel')->addIdCard($data);
        }
    }
    
    //添加用户钱包记录
    private function addUserWallet($id,$money){
        $data=[
            'balance'=>0,
            'giving_balance'=>0,
            'total_balance'=>0,
            'red_packet_balance'=>0
        ];
        M_Mysqli_Class('md_lixiang','UserWalletModel')->updateWalletBalance($data,$id);
    }
    
    //添加用户押金记录
    private function addPledgeOrder($userId){
        $orderNumber=$this->createOrderNum('', 'CP');
        $data=[
            'pledge_money'=>0,
            'order_sn'=>$orderNumber,
            'user_id'=>$userId,
            'pay_time'=>time(),
            'pay_status'=>1,
            'create_time'=>time(),
            'create_date'=>date('Y-m-d H:i:s',time())
        ];
        M_Mysqli_Class('md_lixiang','PledgeOrderModel')->addOrder($data);
    }
    
    //微信注册会员通知
    private function regInform($data){
        $url=ML_URL."d31387a9";
        return $this->apiAndData($url, $data);
    }
}
