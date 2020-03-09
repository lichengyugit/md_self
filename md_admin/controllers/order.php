<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class order extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "order");
    }

    /**
     * 订单列表
     */
    public function orderList()
    {
        $this->checkAuth();
        //$this->msg('添加成功','companyList','ok');exit;
        F()->Resource_module->setTitle('订单列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
//        $url = "/orderList";
        $uri=$this->makeSearchUrl($this->parames);
        $url='orderList?'.$uri;
        $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
        $nums=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderCount($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $pageSize=explode(',',$showpage['limit']);;
        $orderData=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderList($this->parames,$pageSize[0]);
        for ($i=0;$i<count($orderData);$i++){
            $orderData[$i]['pay']=$orderData[$i]['pay']/100;
        }
        $this->smarty->assign('companyData',$companyData);
        $this->smarty->assign('parames',$this->parames);
        $this->smarty->assign('arr',$orderData);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('order/list.phtml');
    }



    /**
     * 查看订单详细信息
     */
    public function orderInfo(){
        $this->checkAuth();
        F()->Resource_module->setTitle('订单详细');
        $parames=$this->parames;
        $data['order_id']=$parames['orderId'];
        $arr=M_Mysqli_Class('md_lixiang','OrderInfoModel')->getOrderInfoByAttr($data);
        $arr['service_time']=$arr['service_time'];
        $arr['pay']=sprintf("%.1f", $arr['pay']/100);
        $OrderArr=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderByAttr(['id'=>$this->parames['orderId']]);
        $this->smarty->assign('order',$OrderArr);
        $this->smarty->assign('arr',$arr);
        $this->smarty->view('order/action.phtml');
    }
   
    /**
     * 修改订单状态
     */
    public function changeOrder(){
        $this->checkAuth();
        $action=$_SERVER['REQUEST_METHOD'];
        $parames=$this->parames;
        if($action=='POST'){
            $data=[
                'id'=>$parames['id'],
                'order_status'=>$parames['orderStatus'],
                'pay_status'=>$parames['payStatus']
            ];
            $beforeData=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderByAttr(['id'=>$data['id']]);
            if(M_Mysqli_Class('md_lixiang','OrderModel')->updateOrder($data)){

                //=============================操作内容记录
                $orderStatus=[1=>'换电中',2=>'已换电',3=>'已完成',4=>'已取消'];
                $payStatus=[1=>'未支付',2=>'已支付'];
                $afterData=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderByAttr(['id'=>$data['id']]);
                $orderRes=$this->arrayNewWornData($afterData,$beforeData);
                $tableName[0]['table_name']='md_order : 订单表-- (订单号:'.$afterData['order_sn'].')';
                if($orderRes){
                    for($i=0; $i<count($orderRes); $i++){
                        if(isset($orderRes[$i]['clm_name']) && $orderRes[$i]['clm_name']=='order_status'){
                            $orderRes[$i]['old_string']=$orderStatus[$orderRes[$i]['old_string']];
                            $orderRes[$i]['new_string']=$orderStatus[$orderRes[$i]['new_string']];                        }
                        if(isset($orderRes[$i]['clm_name']) && $orderRes[$i]['clm_name']=='pay_status'){
                            $orderRes[$i]['old_string']=$orderStatus[$orderRes[$i]['old_string']];
                            $orderRes[$i]['new_string']=$orderStatus[$orderRes[$i]['new_string']];
                        }
                    }
                }else{
                    $insertData='';
                }

                $insertData[0]=$orderRes;
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
                //=============================

                $this->msg('更改成功','/changeOrder?id='.$parames['id'], 'ok');                
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('更改失败','/changeOrder?id='.$parames['id'], 'error');
            }
        }else{
            F()->Resource_module->setTitle('修改订单');
            $arr=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderByAttr($parames);
            //print_r($arr);exit;
            $this->smarty->assign('arr',$arr);            
            $this->smarty->view('order/update.phtml');
            }         
    }


    
    
}
