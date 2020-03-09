<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}

class service extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "service");
    }
    
    
    /**
     * 添加业务员
     */
    public function actionAddUser(){
        F()->Resource_module->setTitle('添加业务员');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(array_key_exists('user_name', $this->parames)){
            $parames=$this->parames;
            $parames['identification_time']=time();
            $parames['invite_code']=$this->make_invite_code();
            $password=mt_rand(100000, 999999);
            $parames['password']=md5($password);
            $serviceInfo=M_Mysqli_Class('md_lixiang','UserModel')->addUser($parames);
            $serviceAction=M_Mysqli_Class('md_lixiang','UserWalletModel')->addUserWalletLog(['user_id'=>$serviceInfo]);
            F()->Sms_module->rSendSms($parames['mobile'],$serviceInfo,3,$password);
            if($serviceInfo && $serviceAction){
                $tableName[0]['table_name']='md_user : 用户表';
                $parames['password']=$password;
                $parames['user_flag']='业务员';
                $insertData[0]=$parames;
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                $this->msg('添加成功','/service','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                $this->msg('添加失败','/service','error');
            }
        }else{
            F()->Resource_module->setTitle('添加业务员');
            $this->smarty->view('service/service.phtml');
        }
    }

    /*
     *  用户退押金管理列表
     */
    public function actionDeposit(){
        F()->Resource_module->setTitle('退押金申请列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        // $data=['pledge_money_status'=>1,'pay_status'=>1,'status'=>0];
        // $url = "/actionDeposit";
        // $nums=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getNumByAttr($data);
        // $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        // $arr=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getAllPledgeOrderByAttr($showpage['limit'],$data);
        // $idCard=M_Mysqli_Class('md_lixiang','IdCardModel')->getAllIdCardByAttr([]);
        // $user=M_Mysqli_Class('md_lixiang','UserModel')->getConditionUser([]);
        // foreach ($arr as $k => $v) {
        //     foreach ($idCard as $key => $value) {
        //         if($v['user_id'] == $value['user_id'] ){
        //             $arr[$k]+=[
        //                 'name'=>$value['name'],
        //                 'card_number'=>$value['card_number']
        //             ];
        //         }
        //     }
        // }
        // foreach ($arr as $k => $v) {
        //     foreach ($user as $key => $value) {
        //         if($v['user_id'] == $value['id'] ){
        //             $arr[$k]+=[
        //                 'mobile'=>$value['mobile']
        //             ];
        //         }
        //     }
        // }
        // foreach ($arr as $key => $value) {
        //     $arr[$key]['pledge_money']=$arr[$key]['pledge_money']/100;
        // }
        // $this->smarty->assign('pages', $showpage['show']);
        // $this->smarty->assign('arr',$arr);
        $this->smarty->view('service/deposit.phtml');
    }



    /*
     *  用户退押金 审核信息 列表接口
     */
    public function AjaxactionDepositList(){
        $parames=$this->parames;
        $data=['dr.pledge_status'=>0,'dr.status'=>0];
        $numData=['pledge_status'=>0];
        $url = "AjaxactionDepositList";
        $nums=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getNumByAttr($numData);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','DepositRefundModel')->AllDepositList($showpage['limit'],$data);
        $return['arr']=$arr;
        $return['one']= $showpage['show'];
        $this->setOutPut($return);die;
    }

    /*
     *  用户退押金已审批管理列表接口
     */
    public function AjaxactionDeposit(){
        $parames=$this->parames;
        $data=['dr.pledge_status'=>1,'dr.status'=>0];
        $numData=['pledge_status'=>1];
        $url = "AjaxactionDeposit";
        $nums=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getNumByAttr($numData);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','DepositRefundModel')->AllDepositList($showpage['limit'],$data);
        $return['arr']=$arr;
        $return['one']= $showpage['show'];
        $this->setOutPut($return);die;
    }


    /*
     *  用户退押金已退款管理列表接口
     */
    public function AjaxactionEndDeposit(){
        $parames=$this->parames;
        $data=['dr.pledge_status'=>2,'dr.status'=>2];
        $numData=['pledge_status'=>2,'status'=>2];
        $url = "AjaxactionEndDeposit";
        $nums=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getNumByAttrCondition($numData);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','DepositRefundModel')->AllDepositList($showpage['limit'],$data);
        $return['arr']=$arr;
        $return['one']= $showpage['show'];
        $this->setOutPut($return);die;
    }


    //审核信息列表 检索
    public function DepositListSearch(){
        $parames=$this->parames;
        if( $parames['data'] == '2' ){
            $data=['dr.status'=>2];
            $numData=['dr.status'=>2];
        }else{
            $data=['dr.status'=>0];
            $numData=['dr.status'=>0];
        }
        $data+=['dr.pledge_status'=>$parames['data']];
        $numData+=['pledge_status'=>$parames['data']];
        switch ($parames['tag']) {
            case '0':
                    $url = "AjaxactionDepositList";
                break;
            case '1':
                    $url = "AjaxactionDeposit";
                break;
            case '2':
                    $url = "AjaxactionEndDeposit";
                break;
            default:
                    $url = "AjaxactionDepositList";
                break;
        }
        $nums=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getSearchNumByAttr($numData,$parames['select']);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','DepositRefundModel')->SearchAllDepositList($showpage['limit'],$data,$parames['select']);
        $return['arr']=$arr;
        $return['one']= $showpage['show'];
        $this->setOutPut($return);die;
    }




    /**
     * 用户退押金列表检索
     */
    public function DepositSearch(){
        $parames=$this->parames;
        $arr=[
            '申请列表'=>['pledge_money_status'=>1,'pay_status'=>1,'status'=>0],
            '审核列表'=>['pledge_money_status'=>2,'status'=>0],
            '已退还列表'=>['pledge_money_status'=>2,'status'=>2]
        ];
    }


    /*
     *  商家站点端注册
     */
    public function serviceRegister(){
        F()->Resource_module->setTitle('商家站点端注册');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $arr=M_Mysqli_Class('md_lixiang','RegisterModel')->getRegsByAttr([]);
        $this->smarty->assign('arr',$arr);
        $this->smarty->view('service/register.phtml');
    }




    /**
     * 用户退押金信息审核
     */
    public function serviceRefund(){
        $parames=$this->parames;
        //查找申请退电用户绑定电池id  和填写电池id是否一致 不一致返回错误
        $RefundData=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getDepositRefundByAttr(['id'=>$parames['refund_id']]);
        if(array_key_exists('refund_reason',$parames)){
            $upReWhere=[
                'id'=>$parames['refund_id']
            ];
            $upReData=[
                'recycle_people'=>$parames['recycle_people'],
                'recycle_date'=>$parames['recycle_date'],
                'recycle_time'=>strtotime($parames['recycle_date']),
                'refund_reason'=>$parames['refund_reason'],
                'recycle_site_name'=>$parames['recycle_site_name']
            ];
            $upRefund=M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById($upReData,$upReWhere);
        }
        if(array_key_exists('refund_reason_not',$parames)){
            $upReWhere=[
                'id'=>$parames['refund_id']
            ];
            $upReData=[
                'recycle_people'=>$parames['recycle_people'],
                'recycle_date'=>$parames['recycle_date'],
                'recycle_time'=>strtotime($parames['recycle_date']),
                'refund_reason'=>$parames['refund_reason_not'],
                'recycle_site_name'=>$parames['recycle_site_name']

            ];
            $upRefund=M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById($upReData,$upReWhere);
        }
        if( $RefundData['type'] == 1 ){
            if(array_key_exists('battery_num',$parames)){
                if($RefundData['battery_num']==$parames['battery_num']){
                    //更改电池表 电池绑定状态
                    $upBattery=[
                        'rent_status'=>1,
                        'user_id'=>0,
                        'battery_num'=>$parames['battery_num'],
                        'battery_status'=>3,
                        'cabinet_id'=>0,
                        'rent_status'=>1
                    ];
                    //传入接口log参数
                    $data=[
                        'br01'=>date('Y-m-d H:i:s',time()),
                        'br03'=>$parames['user_id'],
                        'br04'=>'',
                        'br05'=>$parames['battery_num']
                    ];
                    get_log()->log_api('<接口测试> #### 接口名：BindingBattery 作用：魔动调用魔力电池解绑接口参数：'.json_encode($data));
                    //写死接口 返回0000 0010为确实
                    $result=$this->regInform($data);
                    get_log()->log_api('<接口测试> #### 接口名：BindingBattery 作用：魔动调用魔力电池解绑接口返回值：'.json_encode($result));
                    if($result['re_cd']==0000 || $result['re_cd']==0010){
                        $battery=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($upBattery);
                        if($battery>0){
                            M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>1],['user_id'=>$parames['user_id'],'id'=>$parames['refund_id']]);
                            M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->delectInfoByAttr(['user_id'=>$RefundData['user_id'],'battery_num'=>$RefundData['battery_num'],'pledge_order_id'=>$RefundData['order_id'],'battery_status'=>1]);

                            //============================操作内容记录
                            $tableName[0]['table_name']='md_depositRefund : 退押金表';
                            $backUser=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
                            $backData=[
                                'user_id'=>$parames['user_id'],
                                'user_name'=>$backUser['name'],
                                'mobile'=>$backUser['mobile'],
                                'refund_id'=>$parames['refund_id'],
                                'battery_num'=>$parames['battery_num']
                            ];
                            $insertData[0]=$backData;
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                            //============================

                            $this->setOutPut('审核完成');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->setOutPut('审核失败');
                        }
                    }else{
                        $this->setOutPut($result['rt_msg']);
                    }
                }else{
                    $this->setOutPut('电池编码有误');
                }
            }else{
                if(empty($RefundData['battery_num'])){
                    $upOrder=[
                        'pledge_money_status'=>1,
                        'agree_time'=>time(),
                        'agree_id'=>$_SESSION['user_id'],
                        'status'=>0,
                        'user_id'=>$RefundData['user_id'],
                        'cabinet_id'=>0,
                        'rent_status'=>1
                    ];
                    $Order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrder,['id'=>$RefundData['order_id']]);
                    //这一步仅仅改掉 用户退押金表(md_deposit_refund)的pledge_status字段的进程状态
                    $result=M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>1],['user_id'=>$parames['user_id'],'id'=>$parames['refund_id']]);
                    if($result>0){

                        //===========================操作内容记录
                        $tableName[0]['table_name']='md_depositRefund : 退押金表';
                        $backUser=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
                        $backData=[
                            'user_id'=>$parames['user_id'],
                            'user_name'=>$backUser['name'],
                            'mobile'=>$backUser['mobile'],
                            'refund_id'=>$parames['refund_id'],
                            'battery_num'=>'无电池审核'
                        ];
                        $insertData[0]=$backData;
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                        //=============================

                        $this->setOutPut('审核完成');
                    }else{
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                        $this->setOutPut('审核失败');
                    }
                }else{
                    $this->setOutPut('该用户为绑定电池用户');
                }
            }
        }elseif( $RefundData['type'] == 0 ){
            if(array_key_exists('battery_num',$parames)){
                if($RefundData['battery_num']==$parames['battery_num']){
                    //更改电池表 电池绑定状态
                    $upBattery=[
                        'rent_status'=>1,
                        'user_id'=>0,
                        'battery_num'=>$parames['battery_num'],
                        'battery_status'=>3
                    ];
                    //传入接口log参数
                    $data=[
                        'br01'=>date('Y-m-d H:i:s',time()),
                        'br03'=>$parames['user_id'],
                        'br04'=>'',
                        'br05'=>$parames['battery_num']
                    ];
                    get_log()->log_api('<接口测试> #### 接口名：BindingBattery 作用：魔动调用魔力电池解绑接口参数：'.json_encode($data));
                    //写死接口 返回0000 0010为确实
                    $result=$this->regInform($data);
                    get_log()->log_api('<接口测试> #### 接口名：BindingBattery 作用：魔动调用魔力电池解绑接口返回值：'.json_encode($result));
                    if($result['re_cd']==0000 || $result['re_cd']==0010){
                        $battery=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($upBattery);
                        if($battery>0){
                            $upOrder=[
                                'pledge_money_status'=>2,
                                'agree_time'=>time(),
                                'agree_id'=>$_SESSION['user_id'],
                                'status'=>2,
                                'user_id'=>$RefundData['user_id']
                            ];
                            $Order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrder,['id'=>$RefundData['order_id']]);
                            M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>1],['user_id'=>$parames['user_id'],'id'=>$parames['refund_id']]);
                            /*M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->delectInfoByAttr(['user_id'=>$RefundData['user_id'],'battery_num'=>$RefundData['battery_num'],'pledge_order_id'=>$RefundData['order_id'],'battery_status'=>1]);*/
                            M_Mysqli_Class('md_lixiang','UserModel')->updateUserByAttr(['is_deposit'=>0],['id'=>$RefundData['user_id']]);

                            //============================操作内容记录
                            $tableName[0]['table_name']='md_depositRefund : 退押金表';
                            $backUser=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
                            $backData=[
                                'user_id'=>$parames['user_id'],
                                'user_name'=>$backUser['name'],
                                'mobile'=>$backUser['mobile'],
                                'refund_id'=>$parames['refund_id'],
                                'battery_num'=>$parames['battery_num']
                            ];
                            $insertData[0]=$backData;
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                            //============================

                            $this->setOutPut('审核完成');
                        }else{
                            $upOrder=[
                                'pledge_money_status'=>2,
                                'agree_time'=>time(),
                                'agree_id'=>$_SESSION['user_id'],
                                'status'=>2,
                                'user_id'=>$RefundData['user_id']
                            ];
                            $Order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrder,['id'=>$RefundData['order_id']]);
                            M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>1],['user_id'=>$parames['user_id'],'id'=>$parames['refund_id']]);
                            /*M_Mysqli_Class('md_lixiang','PledgeBatteryModel')->delectInfoByAttr(['user_id'=>$RefundData['user_id'],'battery_num'=>$RefundData['battery_num'],'pledge_order_id'=>$RefundData['order_id'],'battery_status'=>1]);*/
                            M_Mysqli_Class('md_lixiang','UserModel')->updateUserByAttr(['is_deposit'=>0],['id'=>$RefundData['user_id']]);

                            //============================操作内容记录
                            $tableName[0]['table_name']='md_depositRefund : 退押金表';
                            $backUser=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
                            $backData=[
                                'user_id'=>$parames['user_id'],
                                'user_name'=>$backUser['name'],
                                'mobile'=>$backUser['mobile'],
                                'refund_id'=>$parames['refund_id'],
                                'battery_num'=>$parames['battery_num']
                            ];
                            $insertData[0]=$backData;
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                            //============================
                            $this->setOutPut('审核完成');
                        }
                    }else{
                        $this->setOutPut($result['rt_msg']);
                    }
                }else{
                    $this->setOutPut('电池编码有误');
                }
            }else{
                if(empty($RefundData['battery_num'])){
                    $upOrder=[
                        'pledge_money_status'=>2,
                        'agree_time'=>time(),
                        'agree_id'=>$_SESSION['user_id'],
                        'status'=>2,
                        'user_id'=>$RefundData['user_id']
                    ];
                    $Order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrder,['id'=>$RefundData['order_id']]);
                    //这一步仅仅改掉 用户退押金表(md_deposit_refund)的pledge_status字段的进程状态
                    $result=M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>1],['user_id'=>$parames['user_id'],'id'=>$parames['refund_id']]);
                    if($result>0){
                        M_Mysqli_Class('md_lixiang','UserModel')->updateUserByAttr(['is_deposit'=>0],['id'=>$RefundData['user_id']]);

                        //===========================操作内容记录
                        $tableName[0]['table_name']='md_deposit_refund : 退押金表';
                        $backUser=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
                        $backData=[
                            'user_id'=>$parames['user_id'],
                            'user_name'=>$backUser['name'],
                            'mobile'=>$backUser['mobile'],
                            'refund_id'=>$parames['refund_id'],
                            'battery_num'=>'无电池审核'
                        ];
                        $insertData[0]=$backData;
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                        //=============================

                        $this->setOutPut('审核完成');
                    }else{
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                        $this->setOutPut('审核失败');
                    }
                }else{
                    $this->setOutPut('该用户为绑定电池用户');
                }
            }
        }
    }




    /**
     * 用户退款接口
     */
    public function serviceDeposit(){
        // 收到信息
        // user_id => 29                    //用户id
        // order_id => 11                   //订单表id
        // battery_num(单颗绑定为空/多颗绑定为电池编号)
        // recede_money => 1000             //退款金额 按元算
        // pledge_status => 1               //0申请中 1信息已审核 2审核通过
        // type => 0/1                      //0单颗电池退押金 1多颗电池退押金     
        $parames=$this->parames;
        $RefundData=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getDepositRefundByAttr(['id'=>$parames['refund_id']]);
        $pledgeOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderByAttr(['id'=>$RefundData['order_id']]);
        if( $pledgeOrder['pay_type'] == 2 ){
            switch ($RefundData['type']) {
                //集团资金池支付 单颗 ： 更改押金订单表状态之外 需要把押金订单表的钱数 添加到集团资金池里 并且添加记录
                case '0':
                        //更改押金订单表状态
                        $upOrder=[
                            'pledge_money_status'=>3,
                            'agree_time'=>time(),
                            'agree_id'=>$_SESSION['user_id'],
                            'status'=>2,
                            'user_id'=>$RefundData['user_id']
                        ];
                        //更改用户
                        $upUser=[
                            'id'=>$RefundData['user_id'],
                            'status'=>2
                        ];
                        $Order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrder,['id'=>$pledgeOrder['id']]);
                        //根据集团id 查找到
                        $user=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$RefundData['user_id']]);
                        $companyid=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr(['id'=>$user['attr_id']]);
                        $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr(['company_id'=>$companyid['id']]);
                        $MoneyData=[
                            'balance'=>$CompanyMoneyInfo['balance']+$RefundData['recede_money']
                        ];
                        $CompanyMoneyResult=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->updateCompanyMoney($MoneyData,['company_id'=>$companyid['id']]);
                        //记录
                        $reData=[
                            'company_id'=>$companyid['id'],
                            'amount'=>$RefundData['recede_money'],
                            'user_id'=>$user['id'],
                            'type'=>3
                        ];
                        $record=M_Mysqli_Class('md_lixiang','CompanyMoneyRecordModel')->saveCompanyMoneyRecord($reData);
                        //禁用掉该用户
                        $user=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($upUser);
                        if($Order>0){
                            M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>2,'status'=>2,],['id'=>$RefundData['id']]);
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                            $this->setOutPut('审核完成');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->setOutPut('审核失败');
                        }
                    break;
                    //集团资金池支付 多颗 ： 减掉之后 添加到集团资金池 添加记录 检查用户时候还有押金
                case '1':
                        //查询关联押金订单表
                        $selectOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderByAttr(['id'=>$RefundData['order_id']]);
                        //获取退款金额
                        $upOrderData=[
                            'refund_money'=>$selectOrder['refund_money']+($RefundData['recede_money']*100),
                            'agree_time'=>time(),
                            'agree_id'=>$_SESSION['user_id']
                        ];
                        $upOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrderData,['id'=>$selectOrder['id']]);
                        //根据集团id 查找到
                        $user=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$RefundData['user_id']]);
                        $companyid=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr(['id'=>$user['attr_id']]);
                        $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr(['company_id'=>$companyid['id']]);
                        $MoneyData=[
                            'balance'=>$CompanyMoneyInfo['balance']+$RefundData['recede_money']
                        ];
                        $CompanyMoneyResult=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->updateCompanyMoney($MoneyData,['company_id'=>$companyid['id']]);
                        //记录
                        $reData=[
                            'company_id'=>$companyid['id'],
                            'amount'=>$RefundData['recede_money'],
                            'user_id'=>$user['id'],
                            'type'=>3
                        ];
                        $record=M_Mysqli_Class('md_lixiang','CompanyMoneyRecordModel')->saveCompanyMoneyRecord($reData);
                        M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>2,'status'=>2],['id'=>$parames['refund_id']]);
                        //重新检查订单表
                        $ReviveOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderByAttr(['id'=>$RefundData['order_id']]);
                        if( ($ReviveOrder['pledge_money']-$ReviveOrder['refund_money']) <= 0){
                            //禁用掉该用户
                            //更改用户
                            $upUser=[
                                'id'=>$RefundData['user_id'],
                                'status'=>2
                            ];
                            $endUpOrderData=[
                                'status'=>2,
                                'pledge_money_status'=>2,
                            ];
                            $endUpOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($endUpOrderData,['id'=>$selectOrder['id']]);
                            $user=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($upUser);
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                            $this->setOutPut('审核完成');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->setOutPut('审核完成');
                        }
                    break;
                default:
                    $this->setOutPut('未知错误');
                    break;
                }
        }else{
            switch ($RefundData['type']) {
                //个人 单颗
                case '0':
                        //更改押金订单表状态
                        $upOrder=[
                            'pledge_money_status'=>3,
                            'agree_time'=>time(),
                            'agree_id'=>$_SESSION['user_id'],
                            'status'=>2,
                            'user_id'=>$RefundData['user_id']
                        ];

                        //如果为普通用户则不删除
                    $userDataAttr=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$RefundData['user_id']]);
                    if($userDataAttr['user_flag']=='0'){
                        //更改用户
                        $upUser=[
                            'id'=>$RefundData['user_id'],
                            'is_deposit'=>0,
                        ];
                    }else{
                        //更改用户
                        $upUser=[
                            'id'=>$RefundData['user_id'],
                            'is_deposit'=>0,
                            'status'=>2
                        ];
                    }

                        $Order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrder,['id'=>$pledgeOrder['id']]);
                        //禁用掉该用户
                        $user=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($upUser);
                        if($Order>0){
                            M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>2,'status'=>2],['id'=>$RefundData['id']]);

                            //===========================操作内容记录
                            $tableName[0]['table_name']='md_deposit_refund : 退押金表';
                            $backData=[
                                'user_id'=>$userDataAttr['id'],
                                'user_name'=>$userDataAttr['name'],
                                'mobile'=>$userDataAttr['mobile'],
                                'refund_id'=>$parames['refund_id'],
                                'recede_money'=>$RefundData['recede_money'],
                                'battery_num'=>'押金退款'
                            ];
                            $insertData[0]=$backData;
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                            //=============================

                            $this->setOutPut('审核完成');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->setOutPut('审核失败');
                        }
                    break;
                    //个人 多颗
                case '1':
                        //查询关联押金订单表
                        $selectOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderByAttr(['id'=>$RefundData['order_id']]);
                        //获取退款金额
                        $upOrderData=[
                            'refund_money'=>$selectOrder['refund_money']+($RefundData['recede_money']*100),
                            'agree_time'=>time(),
                            'agree_id'=>$_SESSION['user_id']
                        ];
                        $upOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upOrderData,['id'=>$selectOrder['id']]);
                        M_Mysqli_Class('md_lixiang','DepositRefundModel')->updateInfoById(['pledge_status'=>2,'status'=>2],['id'=>$parames['refund_id']]);
                        //重新检查订单表
                        $ReviveOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderByAttr(['id'=>$RefundData['order_id']]);
                        if( ($ReviveOrder['pledge_money']-$ReviveOrder['refund_money']) <= 0){
                            //禁用掉该用户
                            //更改用户
                            $upUser=[
                                'id'=>$RefundData['user_id'],
                                'is_deposit'=>0
                            ];
                            $endUpOrderData=[
                                'status'=>2,
                                'pledge_money_status'=>3,
                            ];
                            $endUpOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($endUpOrderData,['id'=>$selectOrder['id']]);
                            $user=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($upUser);
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                            $this->setOutPut('审核完成');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->setOutPut('审核完成');
                        }
                    break;
                default:
                    $this->setOutPut('未知错误');
                    break;
                }
        }
    }

  


    //excel导出
   public function serviceExcelExport(){
        $parames=$this->parames;
        $search=$parames['select'];
        if(empty($parames['select'])){
            $parames['select']='';
        }
        if(!empty($parames['time'])){
            $str=preg_split('/\s-\s/',$parames['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $time=" AND dr.create_time>".$strTime." AND dr.create_time<".$endTime;
        }else{
            $time='';
        }
        $data=[
            'dr.pledge_status'=>$parames['tag'],
            //'dr.status'=>2
        ];        
        $arr=M_Mysqli_Class('md_lixiang','DepositRefundModel')->AllExcelDepositList($data,$search,$time);
        $execlData='';
        $title=['序号','日期','站点(后台名称)','站点','客户姓名','身份证号','电话','退电回收人员','注册日期','确认回收时间','退电原因','备注(电话确认电池回收)','电池编码','金额','支付订单号','备注'];
        foreach ($arr as $k => $v) {
            $execlData[$k]['num']              =$k+1;
            $execlData[$k]['dcreate_date']              =$v['dcreate_date'];
            $execlData[$k]['site_name']       =' '.$v['site_name'];
            $execlData[$k]['recycle_site_name']       =$v['recycle_site_name'];
            $execlData[$k]['name']            =$v['name'];
            $execlData[$k]['card_number']          =$v['card_number'].' ';
            $execlData[$k]['mobile']      =$v['mobile'];
            $execlData[$k]['recycle_people']       =$v['recycle_people'];
            $execlData[$k]['ucreate_date']          =$v['ucreate_date'];
            $execlData[$k]['recycle_date']      =$v['recycle_date'];
            $execlData[$k]['refund_reason']       =$v['refund_reason'];
            $execlData[$k]['inspect_battery']          ='';
            $execlData[$k]['battery_num']      =$v['battery_num'];
            $execlData[$k]['recede_money']       =$v['recede_money'];
            $execlData[$k]['order_sn']          =$v['order_sn'];
            $execlData[$k]['beizhu']      ='';
        }
        F()->Excel_module->exportExcel($title,$execlData,'用户退押金用户表','./',true);
   }






    //单独请求接口
    private function regInform($data){
        $url=ML_URL."d8bdf6c3"; 
       return $this->apiAndData($url,$data);
    }

    //取消押金退款
    public function cancelPledgeOrder()
    {
        $this->checkAuth();
        $parames=$this->parames;
        $refundData=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getInfoByAttr(['id'=>$parames['refund_id']]);
        $selectOrder=M_Mysqli_Class('md_lixiang','DepositRefundModel')->deleteData($parames['refund_id']);
        $upData=[
            'pledge_money_status'=>0,
            'apply_recede_time'=>null,
            'agree_time'=>null,
            'agree_id'=>null,
            'recede_order_sn'=>null,
            'status'=>0,
        ];
        $pledgeStatus=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->updateOrderByAttr($upData,['id'=>$refundData['order_id']]);
        $userData=[
            'id'=>$parames['user_id'],
            'is_deposit'=>1,
        ];
        $userStatus=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($userData);
        if($userStatus){

            //===================================操作内容记录
            $userData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
            $tableName[0]['table_name']='取消用户退款请求';
            $data=[
                'user_id'=>$userData['id'],
                'user_name'=>$userData['name'],
                'mobile'=>$userData['mobile'],
                '押金订单号'=>$refundData['order_sn'],
                '押金金额'=>$refundData['recede_money']
            ];
            $insertData[0]=$data;
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            //==================================

            $this->setOutPut('取消成功');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->setOutPut('取消失败');
        }
    }

}



