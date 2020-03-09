<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class pledgeorder extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "pledgeorder");
    }

    /**
     * 押金订单列表
     */
    public function pledgeOrderList()
    {
        $parames=$this->parames;
        $this->checkAuth();
        F()->Resource_module->setTitle('押金订单列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $uri=$this->makeSearchUrl($this->parames);
        $url = "pledgeOrderList?".$uri;
        $pledgeOrderNums=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderListData($this->parames,'');
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($pledgeOrderNums));
        $limit=" LIMIT ".$showpage['limit'];
        $pledgeOrderData=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderListData($this->parames,$limit);
        $userType=[0=>'普通用户',1=>'集团用户'];
        $payType=[0=>'微信支付',1=>'集团支付',2=>'资金池支付',3=>'支付宝支付'];
        $pledgeMoneyStatus=[0=>'正常',1=>'退押金申请中',2=>'退押金审核通过'];
        foreach ($pledgeOrderData as $k=>$v){
            $pledgeOrderData[$k]['user_type']=isset($pledgeOrderData[$k]['user_type'])?$userType[$pledgeOrderData[$k]['user_type']]:'未知';
            $pledgeOrderData[$k]['pay_type']=$payType[$pledgeOrderData[$k]['pay_type']];
            $pledgeOrderData[$k]['pledge_money_status']=isset($pledgeOrderData[$k]['pledge_money_status'])?$pledgeMoneyStatus[$pledgeOrderData[$k]['pledge_money_status']]:'未知';
            $pledgeOrderData[$k]['pledge_money']=$pledgeOrderData[$k]['pledge_money']/100;
            $pledgeOrderData[$k]['is_bottom']=$pledgeOrderData[$k]['is_bottom']==0?'否':'是';
        }
        if(isset($parames['execlbutton'])){
            $title=['用户名','手机号','用户类型','订单号','押金金额','支付方式','订单状态','是否携带底托押金','同意用户申请时间','支付时间'];
            for($i=0;$i<count($pledgeOrderData);$i++){
                $execlData[$i]['name']                   =$pledgeOrderData[$i]['name'];
                $execlData[$i]['mobile']                 =$pledgeOrderData[$i]['mobile'];
                $execlData[$i]['user_type']              =$pledgeOrderData[$i]['user_type'];
                $execlData[$i]['order_sn']               =$pledgeOrderData[$i]['order_sn'];
                $execlData[$i]['pledge_money']           =$pledgeOrderData[$i]['pledge_money'];
                $execlData[$i]['pay_type']               =$pledgeOrderData[$i]['pay_type'];
                $execlData[$i]['pledge_money_status']    =$pledgeOrderData[$i]['pledge_money_status'];
                $execlData[$i]['is_bottom']              =$pledgeOrderData[$i]['is_bottom'];
                $execlData[$i]['apply_recede_time']      =date('Y-m-d H:i:s',$pledgeOrderData[$i]['apply_recede_time']);
                $execlData[$i]['pay_date']               =$pledgeOrderData[$i]['pay_date'];
            }
            F()->Excel_module->exportExcel($title,$execlData,'用户列表Execl列表','./',true);
        }

        $this->smarty->assign("pledgeOrderData", $pledgeOrderData);
        $this->smarty->assign("parames", $parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('pledgeorder/list.phtml');
    }






    
}
