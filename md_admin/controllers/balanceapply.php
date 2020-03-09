<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class balanceapply extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "backstagelog");
    }


    /**
     * 用户余额申请列表
     */
    public function agentBalanceApplyList()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('特约商余额提现列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('balanceApply/list.phtml');
    }

    /*
     * 申请列表
     * */
    public function ajaxAgentBalanceApprove()
    {
        $parames=$this->parames;
        $url = "ajaxAgentBalanceApprove";
        $blancenNms=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getNumByAttr(['state'=>0]);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$blancenNms);
        $limitAndOrderBY=' ORDER BY BO.creat_time DESC LIMIT '.$showpage['limit'];
        $agentBlancens=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getAgentBalanceData($limitAndOrderBY,['state'=>0]);
        $arr['arr']=$agentBlancens;
        $arr['one']= $showpage['show'];
        $this->setOutPut($arr);die;
    }

    /*
     * 已审核列表
     * */
    public function ajaxAgentBalanceEndApprove()
    {
        $parames=$this->parames;
        $url = "ajaxAgentBalanceEndApprove";
        $blancenNms=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getNumByAttr(['state'=>1]);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$blancenNms);
        $limitAndOrderBY=' ORDER BY BO.end_time DESC LIMIT '.$showpage['limit'];
        $agentBlancens=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getAgentBalanceData($limitAndOrderBY,['state'=>1]);
        for($i=0;$i<count($agentBlancens);$i++){
            $agentBlancens[$i]['end_time']=date('Y-m-d H:i:s',$agentBlancens[$i]['end_time']);
        }
        $arr['arr']=$agentBlancens;
        $arr['one']= $showpage['show'];
        $this->setOutPut($arr);die;
    }

    /*
         * 已退还列表
         * */
    public function ajaxAgentBalanceReturned()
    {
        $parames=$this->parames;
        $url = "ajaxAgentBalanceReturned";
        $blancenNms=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getNumByAttr(['state'=>2]);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$blancenNms);
        $limitAndOrderBY=' ORDER BY BO.end_time DESC LIMIT '.$showpage['limit'];
        $agentBlancens=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getAgentBalanceData($limitAndOrderBY,['state'=>2]);
        for($i=0;$i<count($agentBlancens);$i++){
            $agentBlancens[$i]['end_time']=date('Y-m-d H:i:s',$agentBlancens[$i]['end_time']);
        }
        $arr['arr']=$agentBlancens;
        $arr['one']= $showpage['show'];
        $this->setOutPut($arr);die;
    }

    /*
     *页面搜索
     * */
    public function agentBalanceSearch()
    {
        $parames=$this->parames;
        $agentBlancenSearchNum=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getAgentBalanceSearch('',$parames);
        $url = "agentBalanceSearch";
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],count($agentBlancenSearchNum));
        $limit=" LIMIT ".$showpage['limit'];
        $agentBlancenSearchData=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getAgentBalanceSearch($limit,$parames);
        for($i=0;$i<count($agentBlancenSearchData);$i++){
            $agentBlancenSearchData[$i]['end_time']=isset($agentBlancenSearchData[$i]['end_time'])?date('Y-m-d H:i:s',$agentBlancenSearchData[$i]['end_time']):'';
        }
        $arr['arr']=$agentBlancenSearchData;
        $arr['one']= $showpage['show'];
//        echo '<pre />';
//        var_dump($agentBlancenSearchNum);
//        var_dump($arr);die;
        $this->setOutPut($arr);die;
    }

    /*
     * 提现审核通过
     * */
    public function balanceVerified()
    {
        $parames=$this->parames;
        $upState=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->updateData(['id'=>$parames['id'],'state'=>$parames['state']]);
        if($upState){
            $afterData[0]=M_Mysqli_Class('md_lixiang','BankOutMoneyModel')->getAgentBalanceData('',['BO.id'=>$parames['id']]);
            $tableName[0]['table_name']='md_bank_money : 余额提现表';
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$afterData[0],$tableName);
            $data=[];
            $data['msg']='审核完成';
            $data['state']=$parames['state'];
            $this->setOutPut($data);
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->setOutPut('审核失败');
        }
    }
}
