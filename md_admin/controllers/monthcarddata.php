<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class monthcarddata extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "monthcarddata");
    }

    /**
     * 月卡订单列表
     */
    public function monthCardData()
    {
        $parames=$this->parames;
        $this->checkAuth();
        F()->Resource_module->setTitle('月卡订单列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/monthCardData";
        //如果没有参数为初次加载走这方法
        if(empty($parames)){
            $nums=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getCardConfigNumByAttrNum([1=>1]);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $limit=" LIMIT ".$showpage['limit'];
            $monthCardDatas=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getMonthCardOrder($limit);
            $returnParames['user_type']='';
            $returnParames['card_type']='';
            $returnParames['input_data']='';
        }else{
            //搜索后一直走这
            //查询出符合条件的所有数据计算分页
            $monthCardSum=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getMonthCardOrder('',$this->parames);
            //过滤参数
            $uri=$this->makeSearchUrl($this->parames);
            //把参数带在地址后面
            $url='monthCardData?'.$uri;
            $showpage= $this->page($url,$this->commonDefine['pagesize'],count($monthCardSum));
            $limit=' LIMIT '.$showpage['limit'];
            //分页查询符合条件的数据
            $monthCardDatas=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getMonthCardOrder($limit,$this->parames);
            //返回提交过来的值
            $returnParames=$parames;
        }
        $cardType=[1=>'年卡',2=>'季卡',3=>'月卡',4=>'半年卡'];
        $userType=[0=>'普通用户',1=>'集团用户'];
        for($i=0;$i<count($monthCardDatas);$i++){
            $monthCardDatas[$i]['card_type']=$cardType[$monthCardDatas[$i]['card_type']];
            $monthCardDatas[$i]['user_type']=isset($monthCardDatas[$i]['user_type'])?$userType[$monthCardDatas[$i]['user_type']]:'';
        }

        //如果为打印按钮走这
        if(!empty($parames['execlbutton'])){
                $execlData='';
                $payStatus=[0=>'未支付',1=>'已支付',2=>'支付失败'];
                $title=['用户名','联系方式','用户类型','订单号','月卡类型','月卡价格','实际付款金额','余额抵扣金额','起始时间','过期时间','下订单时间','支付状态'];
                for($i=0;$i<count($monthCardSum);$i++){
                    $execlData[$i]['user_name']=$monthCardSum[$i]['user_name'];
                    $execlData[$i]['user_mobile']=$monthCardSum[$i]['user_mobile'];
                    $execlData[$i]['user_type']=$userType[$monthCardSum[$i]['user_type']];
                    $execlData[$i]['order_sn']=$monthCardSum[$i]['order_sn'];
                    $execlData[$i]['card_type']=$cardType[$monthCardSum[$i]['card_type']];
                    $execlData[$i]['price']=$monthCardSum[$i]['price'];
                    $execlData[$i]['payment_amount']=$monthCardSum[$i]['payment_amount'];
                    $execlData[$i]['balance_deduction']=$monthCardSum[$i]['balance_deduction'];
                    $execlData[$i]['start_time']=$monthCardSum[$i]['start_time']==''?null:date('Y-m-d H:i:s',$monthCardSum[$i]['start_time']);
                    $execlData[$i]['over_time']=$monthCardSum[$i]['over_time']==''?null:date('Y-m-d H:i:s',$monthCardSum[$i]['over_time']);
                    $execlData[$i]['create_date']=$monthCardSum[$i]['create_date']==''?null:date('Y-m-d H:i:s',$monthCardSum[$i]['over_time']);
                    $execlData[$i]['pay_status']=$payStatus[$monthCardSum[$i]['pay_status']];

                }
                //$title数据标题   $execlData数据  execl文件名  true为下载
              F()->Excel_module->exportExcel($title,$execlData,'月卡订单数据Execl列表','./',true);
        }
        $data['parames']=$returnParames;
        $data['monthCardDatas']=$monthCardDatas;
        $this->smarty->assign("data",$data);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('monthcard/list.phtml');
    }



    /*
         * 交押金 退押金人员信息
         * */
    public function execlUserData()
    {
        $parames=$this->parames;
        if(empty($parames['create_time'])){
            $todayStart= date('Y-m-d 00:00:00', time());
            $todayEnd= date('Y-m-d 23:59:59', time());
            $parames['create_time']=$todayStart. ' - '.$todayEnd;
        }
        if($parames['type']==1){
            $execlName='交押金';
            $userPledgeOrderData=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getUserPledOrderData('',['create_time'=>$parames['create_time']]);//交押金用户信息
        }else{
            $execlName='退押金';
            $userPledgeOrderData=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getUserPledOrderRefundData('',['agree_time'=>$parames['create_time']]);  //退押金用户信息
        }
        $execlData='';
        $userType=array('普通用户','集团用户','无','无','商家');
        $title=array('用户名','身份证','手机号','用户身份');
        for($i=0;$i<count($userPledgeOrderData);$i++){
            $execlData[$i]=[
                'name'=>$userPledgeOrderData[$i]['name'],
                'card_number'=>' '.$userPledgeOrderData[$i]['card_number'],
                'mobile'=>$userPledgeOrderData[$i]['mobile'],
                'user_flag'=>$userType[isset($userPledgeOrderData[$i]['user_flag'])?$userPledgeOrderData[$i]['user_flag']:3]
            ];
        }
        F()->Excel_module->exportExcel($title,$execlData,$execlName.'用户信息Execl列表','./',true);

    }



    
}
