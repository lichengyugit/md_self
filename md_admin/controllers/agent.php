<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
Class Agent extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "agent");
    }

    /**
     * 服务商列表
     */
    public function agentList(){
            $this->checkAuth();
            $parames=$this->parames;
            F()->Resource_module->setTitle('特约服务商列表');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $url = "/agentList";
            $data['user_flag']=4;
            $nums=M_Mysqli_Class('md_lixiang','AdminModel')->getNumByAttr($data);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $arr=M_Mysqli_Class('md_lixiang','AdminModel')->getAllAdminByAttr($showpage['limit'],$data);
            $this->smarty->assign('arr',$arr);

            if(empty($parames)){
                $url = "/agentList";
                $nums=M_Mysqli_Class('md_lixiang','MerchantModel')->getNumByAttr([1=>1]);
                $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
                $limit="limit ".$showpage['limit'];
                $merchantDatas=M_Mysqli_Class('md_lixiang','MerchantModel')->getMerchantDataAll('',$limit);
                $parames['ipunt_data']='';
                $parames['create_time']='';
            }else{
                $merchantNums=M_Mysqli_Class('md_lixiang','MerchantModel')->getMerchantDataAll($parames,'');
                $uri=$this->makeSearchUrl($this->parames);
                //把参数带在地址后面
                $url='agentList?'.$uri;
                $showpage= $this->page($url,$this->commonDefine['pagesize'],count($merchantNums));
                $limit=' limit '.$showpage['limit'];
                $merchantDatas=M_Mysqli_Class('md_lixiang','MerchantModel')->getMerchantDataAll($parames,$limit);
                $parames=$this->parames;
            }
            if(!empty($this->parames['execlButton'])){
                $execlData='';
                $title=['特约服务名称','联系方式','特约服务地址','入驻日期','状态'];
                $status=[0=>'正常',1=>'禁用'];
                for($i=0;$i<count($merchantDatas);$i++){
                    $execlData[$i]['name']             =$merchantDatas[$i]['name'];
                    $execlData[$i]['mobile']           =$merchantDatas[$i]['mobile'];
                    $execlData[$i]['location']         =$merchantDatas[$i]['location'];
                    $execlData[$i]['create_date']      =$merchantDatas[$i]['create_date'];
                    $execlData[$i]['status']           =$merchantDatas[$i]['status'];
                }
                F()->Excel_module->exportExcel($title,$execlData,'用户列表Execl列表','./',true);
            }
            $this->smarty->assign('parames',$parames);
            $this->smarty->assign('merchantDatas',$merchantDatas);

            $this->smarty->assign("pages", $showpage['show']);
            $this->smarty->view('agent/list.phtml');
    }

    /**
     * 修改服务商状态
     *
     */
    public function editMerchantState()
    {

        $beforeData=M_Mysqli_Class('md_lixiang','MerchantModel')->getMerchantInfoBy(['id'=>$this->parames['id']]);
        $editData=M_Mysqli_Class('md_lixiang','MerchantModel')->updateMerchantById($this->parames);
        if($editData){
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['name'=>$beforeData['name'], 'mobile'=>$beforeData['mobile'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_merchant : 服务商表';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            $this->msg('操作成功','/agentList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/agentList','error');
        }
    }

    /*
     * 代理商下属用户列表
     * */
    public function MerchantUndUser()
    {
        $parames=$this->parames;
        F()->Resource_module->setTitle('特约服务商下属用户列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
            if(!isset($parames['id_card']) || $parames['id_card']=='x'){
                unset($parames['id_card']);
            }
            if(!isset($parames['select']) || $parames['select']==''){
                $parames['select']='';
            }
            $nums=M_Mysqli_Class('md_lixiang','UserModel')->getSearchCountBatteryByAttr($parames);
            $uri=$this->makeSearchUrl($this->parames);
            $url = "MerchantUndUser?".$uri;
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $limit="limit ".$showpage['limit'];
            $userDatas=M_Mysqli_Class('md_lixiang','UserModel')->tableQuery($parames,$limit);
            $parames['select']=isset($parames['select'])?$parames['select']:'';
            $parames['id_card']=isset($parames['id_card'])?$parames['id_card']:'x';
            $parames['time']=isset($parames['time'])?$parames['time']:'';
            $parames['merchant_user_id']=$parames['merchant_user_id'];

        $this->smarty->assign('parames',$parames);
        $this->smarty->assign('userDatas',$userDatas);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('agent/undUser.phtml');
    }



    /**
     * 修改代理商下属用户状态
     */
    public function actionAgentUserStatus(){
        $data=[
            'id'=>$this->parames['id'],
            'status'=>$this->parames['status']
        ];
        $beforeData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$data['id']]);
        $updateCompany=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($data);
        if($updateCompany){
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['name'=>$beforeData['name'],'card_number'=>$beforeData['card_number'], 'mobile'=>$beforeData['mobile'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_user : 用户表';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            $this->msg('操作成功','/MerchantUndUser?merchant_user_id='.$this->parames['merchant_user_id'].'&id_card='.$this->parames['id_card'].'&select='.$this->parames['select'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/MerchantUndUser?merchant_user_id='.$this->parames['merchant_user_id'].'&id_card='.$this->parames['id_card'].'&select='.$this->parames['select'],'error');
        }
    }


    /**
     * 商家入驻审核
     */
    public function actionAgentToexamine(){
        $this->checkAuth();
        $parames=$this->parames;
        F()->Resource_module->setTitle('商家入驻审核');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/actionAgentToexamine";
        $nums=M_Mysqli_Class('md_lixiang','RegisterModel')->getNumByAttr([]);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','RegisterModel')->getRegsByAttr([],$showpage['limit']);
        $this->smarty->assign('arr',$arr);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('agent/join.phtml');
    }

   /**
    * [agentSearch 商家入驻页面搜索]
    * @return [type] [description]
    */
   public function agentSearch(){
      F()->Resource_module->setTitle('商家入驻审核');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $parames=$this->parames;
      $selected=$parames;
      $num=M_Mysqli_Class('md_lixiang','RegisterModel')->getSearchCountBatteryByAttr($parames);
      $searchArray=[];
      $uri=$this->makeSearchUrl($this->parames);
      $url='userSearch?'.$uri;
      $showpage= $this->page($url,$this->commonDefine['pagesize'],$num);
      $select=' LIMIT '.$showpage['limit'];
      $arr=M_Mysqli_Class('md_lixiang','RegisterModel')->tableQuery($parames,$select);
      $this->smarty->assign('selected',$selected);
      $this->smarty->assign('arr',$arr);
      $this->smarty->assign('search',$this->parames['select']);
      $this->smarty->assign("pages", $showpage['show']);
      $this->smarty->view('agent/join.phtml');
   }


    /**
     * 商家入驻请求接口
     */
    public function Toexamine(){
        $parames=$this->parames;
        $invite=$this->make_invite_code();
        $Result=M_Mysqli_Class('md_lixiang','TransActionModel')->BusinessAudit($parames,$invite);
        if($Result){
            $afterData=M_Mysqli_Class('md_lixiang','RegisterModel')->getRegByAttr(['id'=>$parames['id']]);
            $insertData[0]=[
                'name'=>$afterData['name'],
                'mobile'=>$afterData['mobile'],
                'card_number'=>$afterData['card_number'],
                'shop_name'=>$afterData['shop_name'],
                'location'=>$afterData['location'],
            ];
            $tableName[0]['table_name']='md_register : 商家入驻表';
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            $this->setOutPut('成功');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->setOutPut('失败');
        }        
        /**
         * 1.绑定商家    md_user           
         * 2.用户钱包    md_user_wallet
         * 3.idcard表    md_idcard     
         * 4.邀请码表    md_invite_code
         * 5.Merchant表  md_Merchant
         */
    }


    /*
     * 特约服务商钱包
     * */
    public function agentWalletList()
    {
        $parames=$this->parames;
        $agenName=isset($parames['agent_name'])?$parames['agent_name'].'-':'';
        $parames['income_type']=isset($parames['income_type'])?$parames['income_type']:'';
        $parames['type']=isset($parames['type'])?$parames['type']:'';
        $parames['create_time']=isset($parames['create_time'])?$parames['create_time']:'';
        F()->Resource_module->setTitle($agenName.'特约服务商钱包列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        //特约商钱包信息
        $agentWallet=M_Mysqli_Class('md_lixiang','UserWalletModel')->getWalletByUserId($parames['user_id']);
        //查询钱包流水
        $agentWalletLogNum=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getWalletAndUserData('',$parames);
        $uri=$this->makeSearchUrl($this->parames);
        $url='agentWalletList?'.$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($agentWalletLogNum));
        $limit=" LIMIT ".$showpage['limit'];
        $agentWalletLogData=M_Mysqli_Class('md_lixiang','UserWalletLogModel')->getWalletAndUserData($limit,$parames);
        $incomeType=[1=>'收入',2=>'支出'];
        $operationType=[1=>'充值',2=>'提现',3=>'分佣',4=>'赠送',5=>'提现退款',6=>'下单',7=>'新用户赠送',8=>'月卡抵扣'];
        for($i=0;$i<count($agentWalletLogData);$i++){
            $agentWalletLogData[$i]['income_type']=$incomeType[$agentWalletLogData[$i]['income_type']];
            $agentWalletLogData[$i]['type']=$operationType[$agentWalletLogData[$i]['type']];
        }
        $this->smarty->assign('agentWallet',$agentWallet);
        $this->smarty->assign('agentWalletLogData',$agentWalletLogData);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign("parames", $parames);
        $this->smarty->view('agent/wallet_list.phtml');
    }




}


