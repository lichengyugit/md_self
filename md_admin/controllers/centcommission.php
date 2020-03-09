<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class centcommission extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "admin");
    }


    /**
     * 分佣管理列表
     */
    public function centcommissionList()
    {
            $this->checkAuth();
            F()->Resource_module->setTitle('分佣管理列表');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('centcommission/list.phtml');
    }



    /*
     * 普通用户分佣列表
     * */
    public function ajaxGetCentCommissionData()
    {
        $url = "ajaxGetCentCommissionData";
        $centcommisNum = M_Mysqli_Class('md_lixiang', 'UserWalletModel')->getUserWalletData('', $this->parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], count($centcommisNum));
        $limit = " LIMIT " . $showpage['limit'];
        $centcommisData = M_Mysqli_Class('md_lixiang', 'UserWalletModel')->getUserWalletData($limit, $this->parames);
        $arr['arr'] = $centcommisData;
        $arr['one'] = $showpage['show'];
        $this->setOutPut($arr);die;
    }


    /*
     * 业务员分佣列表
     * */
    public function ajaxGetSaleCentCommissionData()
    {
        $url = "ajaxGetSaleCentCommissionData";
        $saleCentcommisNum = M_Mysqli_Class('md_lixiang', 'UserWalletModel')->getUserWalletData('', $this->parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], count($saleCentcommisNum));
        $limit = " LIMIT " . $showpage['limit'];
        $saleCentCommisData = M_Mysqli_Class('md_lixiang', 'UserWalletModel')->getUserWalletData($limit, $this->parames);
        $arr['arr'] = $saleCentCommisData;
        $arr['one'] = $showpage['show'];
        $this->setOutPut($arr);die;
    }



    /*
         *分佣列表页面搜索
         * */
    public function centCommissionDataSearch()
    {
        $parames=$this->parames;
        $agentBlancenSearchNum=M_Mysqli_Class('md_lixiang','UserWalletModel')->getUserWalletData('',$parames);
        $url = "centCommissionDataSearch";
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],count($agentBlancenSearchNum));
        $limit=" LIMIT ".$showpage['limit'];
        $agentBlancenSearchData=M_Mysqli_Class('md_lixiang','UserWalletModel')->getUserWalletData($limit,$parames);
        $arr['arr']=$agentBlancenSearchData;
        $arr['one']= $showpage['show'];
//        echo '<pre />';
//        var_dump($agentBlancenSearchNum);
//        var_dump($arr);die;
        $this->setOutPut($arr);die;
    }

    /**
     *  用户与业务员钱包流水及搜索
     */
    public function UserWalletWaterList(){
//        var_dump($this->parames);die;
        $this->checkAuth();
        $parames=$this->parames;
        $titleName=isset($parames['title_name'])?$parames['title_name']:'';
        $parames['income_type']=isset($parames['income_type'])?$parames['income_type']:'';
        $parames['type']=isset($parames['type'])?$parames['type']:'';
        $parames['create_time']=isset($parames['create_time'])?$parames['create_time']:'';
        F()->Resource_module->setTitle($titleName.'钱包流水');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $userWalletLogNum=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getWalletAndUserData('',$parames);
        $uri=$this->makeSearchUrl($this->parames);
        $url='UserWalletWaterList?'.$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($userWalletLogNum));
        $limit=" LIMIT ".$showpage['limit'];
        if($parames['user_flag']=='0'){
            $userWalletLogData=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getWalletLogData($limit,$parames);
            for($i=0;$i<count($userWalletLogData);$i++){
                if($userWalletLogData[$i]['type']=='3'){
                    $userWalletLogData[$i]['name3']=M_Mysqli_Class('md_lixiang','InviteCodeModel')->getInviteCodeByAttr(['id'=>$userWalletLogData[$i]['primary_id']]);
                }
            }
        }elseif($parames['user_flag']=='3'){
            $userWalletLogData=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getWalletAndUserData($limit,$parames);
        }
        $incomeType=[1=>'收入',2=>'支出'];
        $operationType=[1=>'充值',2=>'提现',3=>'分佣',4=>'赠送',5=>'提现退款',6=>'下单',7=>'新用户赠送',8=>'月卡抵扣'];
        for($i=0;$i<count($userWalletLogData);$i++){
            $userWalletLogData[$i]['income_type']=$incomeType[$userWalletLogData[$i]['income_type']];
            $userWalletLogData[$i]['type']=$operationType[$userWalletLogData[$i]['type']];
        }
        $this->smarty->assign('userWalletLogData',$userWalletLogData);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign("parames", $parames);
        $this->smarty->view('centcommission/user_wallet_list.phtml');
    }

   /*
    * 批量修改用户信息
    * */
   public function editBatchUserData()
   {


       $paeames=$this->parames;
         if(IS_GET){
           F()->Resource_module->setTitle('批量修改电池信息');
           F()->Resource_module->setJsAndCss(array(
               'home_page'
           ), array(
               'main'
           ));
             $this->smarty->view('user/updatapil.phtml');
         }elseif(IS_AJAX){
             $cabinetData=M_Mysqli_Class('md_lixiang','CabinetModel')->getBoxByAttr(['cabinet_number'=>$paeames['cabinet_number'],'status'=>0]);
                 $data=[
                     'id'=>$cabinetData['id'],
                     'cabinet_number'=>$cabinetData['cabinet_number'],
                 ];
            $this->setOutPut($data);die;
         }else{
             $arr = [];
             $arr_tmp = explode(',', $paeames['battery_id']);
             foreach ($arr_tmp as $item){
                 $arr_tmp1 = explode('，', $item);
                 foreach ($arr_tmp1 as $value){
                     $arr[] = trim($value);
                 }
             }
             if(empty($paeames['cabinet_id'])){
                 $updata=[
                     'site_id'=>$paeames['site_id'],
                 ];
             }else{
                 $updata=[
                     'site_id'=>$paeames['site_id'],
                     'cabinet_id'=>$paeames['cabinet_id']
                 ];
             }

//             $cabinetData=M_Mysqli_Class('md_lixiang','BatteryModel')->updateInWhereBattery($arr,$updata);
             for($i=0;$i<count($arr);$i++){
                 $cabinetReturnRow[$i]=M_Mysqli_Class('md_lixiang','BatteryModel')->updateWheresBattery($updata,['battery_num'=>$arr[$i]]);
                 if($cabinetReturnRow[$i] > 0){
                     echo '修改成功';
                 }else{
                     echo '第'.$i.'条修改失败';
                 }
             }

         }



   }

    
}
