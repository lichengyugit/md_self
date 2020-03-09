<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}

Class User extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);        
        $this->smarty->assign("function", "user");
    }

    
    public function index(){
            $this->checkAuth();
            F()->Resource_module->setTitle('用户列表');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $url='userList';
            $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
            $nums=M_Mysqli_Class('md_lixiang','UserModel')->getUserByAttr(['or_user_flag'=>1,'user_flag'=>0,'identification'=>1]);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $arr=M_Mysqli_Class('md_lixiang','UserModel')->getAllUserByAttr($showpage['limit'],['user_flag'=>0,'or_user_flag'=>1,'identification'=>1]);
/*          $a=M_Mysqli_Class('md_lixiang','UserModel')->returntable();
            print_r($a);die;*/
            $idstr=[];
            foreach ($arr as $k => $v) {
                $idstr+=[$k=>$v['id']];
            }
            $idcard=M_Mysqli_Class('md_lixiang','IdCardModel')->getIn($idstr);
            foreach ($arr as $key => &$value) {
              foreach ($idcard as $k => $v) {
                if($value['id']==$v['user_id']){
                  $arr[$key]['name']=$idcard[$k]['name'];
                  $arr[$key]['card_number']=$idcard[$k]['card_number'];
                }
              }
            }
//            $selected['is_vip']='x';
            $selected['id_card']='x';
            $selected['attr_id']='';
            $selected['user_flag']='x';
            $selected['create_time']='';
            $selected['is_deposit']='x';
            $arr=$this->actionIndex($arr);
            $this->smarty->assign('selected',$selected);
            $this->smarty->assign('companyData',$companyData);
            $this->smarty->assign('search','');
            $this->smarty->assign('arr',$arr);
            $this->smarty->assign("pages", $showpage['show']);
            $this->smarty->view('user/list.phtml');
    }


   /**
    * [userSearch 用户页面搜索]
    * @return [type] [description]
    */
   public function userSearch(){
      F()->Resource_module->setTitle('用户列表');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $parames=$this->parames;
      $selected=$parames;
//      if($parames['is_vip']=='x'){
//        unset($parames['is_vip']);
//      }
      if($parames['id_card']=='x'){
        unset($parames['id_card']);
      }
      if($parames['user_flag']=='x'){
        unset($parames['user_flag']);
      }
       if($parames['is_deposit']=='x'){
           unset($parames['is_deposit']);
       }
      if(empty($parames['select'])){
        $parames['select']='';
      }
      if(empty($parames['compay_id'])){
        unset($parames['compay_id']);
      }
      if(empty($parames['attr_id'])){
        unset($parames['attr_id']);
      }
       $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
      $num=M_Mysqli_Class('md_lixiang','UserModel')->getSearchCountBatteryByAttr($parames);
      $searchArray=[];
      $uri=$this->makeSearchUrl($this->parames);
      $url='userSearch?'.$uri;
      $showpage= $this->page($url,$this->commonDefine['pagesize'],$num);
      $select=' LIMIT '.$showpage['limit'];
      $arr=M_Mysqli_Class('md_lixiang','UserModel')->tableQuery($parames,$select);
//      var_dump($selected);die;
      $this->smarty->assign('selected',$selected);
      $this->smarty->assign('arr',$arr);
      $this->smarty->assign('companyData',$companyData);
      $this->smarty->assign('search',$this->parames['select']);
      $this->smarty->assign("pages", $showpage['show']);
      $this->smarty->view('user/list.phtml');
   }



   public function execlButton(){
       $parames=$this->parames;
       if($parames['is_vip']=='x'){
           unset($parames['is_vip']);
       }
       if($parames['id_card']=='x'){
           unset($parames['id_card']);
       }
       if($parames['user_flag']=='x'){
           unset($parames['user_flag']);
       }
       if($parames['is_deposit']=='x'){
           unset($parames['is_deposit']);
       }
       if(empty($parames['select'])){
           $parames['select']='';
       }
       if(empty($parames['compay_id'])){
           unset($parames['compay_id']);
       }
       if(empty($parames['attr_id'])){
           unset($parames['attr_id']);
       }
       /*       print_r($parames);die;*/
       $userData=M_Mysqli_Class('md_lixiang','UserModel')->tableQuery($parames);
       $execlData='';
       $title=['所属集团','用户昵称','真实姓名','用户手机号','身份证号','用户类型','押金缴纳','是否为月卡','是否认证','注册时间','用户状态'];
       $userType=[0=>'普通用户',1=>'集团用户'];
       $isVip   =[0=>'否',1=>'是'];
       $isCard  =[0=>'否',1=>'是'];
       $identification  =[0=>'未认证',1=>'已认证'];
       $status=[0=>'正常',1=>'禁用'];
       $isDeposit=[0=>'未缴纳',1=>'已缴纳'];
       for($i=0;$i<count($userData);$i++){
           $execlData[$i]['company_name']      =$userData[$i]['company_name'];
           $execlData[$i]['nick_name']      =urldecode($userData[$i]['nick_name']);
           $execlData[$i]['name']           =$userData[$i]['name'];
           $execlData[$i]['mobile']         =$userData[$i]['mobile'];
           $execlData[$i]['card_number']    =' '.$userData[$i]['card_number'];
           $execlData[$i]['user_type']      =$userType[$userData[$i]['user_type']];
           $execlData[$i]['is_deposit']     =$isDeposit[$userData[$i]['is_deposit']];
           $execlData[$i]['id_card']        =$isCard[$userData[$i]['id_card']];
           $execlData[$i]['identification'] =$identification[$userData[$i]['identification']];
           $execlData[$i]['create_date']    =empty($userData[$i]['create_date'])?null:$userData[$i]['create_date'];
           $execlData[$i]['status']         =$status[$userData[$i]['status']];
       }
       F()->Excel_module->exportExcel($title,$execlData,'用户列表Execl列表','./',true);
   }



    public function actionIndex($arr){
        for($i=0 ; $i<count($arr) ; $i++){
          $arr[$i]['nick_name']=urldecode($arr[$i]['nick_name']);
        }
        return $arr;
    }
    
    public function seeUserSum(){
        $this->checkAuth();
        F()->Resource_module->setTitle('用户钱包余额');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $arr=M_Mysqli_Class('md_lixiang','UserWalletModel')->getWalletByUserId($parames['id']);
        //print_r($arr);exit;
        $this->smarty->assign('arr',$arr);
        $this->smarty->view('user/userSum.phtml');
    } 
    public function seeUserBattery(){
        $this->checkAuth();
        F()->Resource_module->setTitle('用户电池信息');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatterysByAttr(['user_id'=>$parames['id'],'status'=>0]);
//        for($i=0;$i<count($arr);$i++){
//            $arr[$i]['cabinet_name']=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$arr[$i]['cabinet_id']])['cabinet_name'];
//        }
        //print_r($arr);exit;
//        $arr['binding_time']=date("Y-m-d H:i:s",$arr['binding_time']);
        $this->smarty->assign('arr',$arr);
        $this->smarty->view('user/userBattery.phtml');
    }
    public function seeUserCard(){
        F()->Resource_module->setTitle('用户购买月卡类型');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $url='/seeUserCard?id='.$parames['id'];
        $nums=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getCardConfigNumByAttrNum(['user_id'=>$parames['id']]);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getCardConfigByAttrs($showpage['limit'],['user_id'=>$parames['id']]);
        $over_time=M_Mysqli_Class('md_lixiang','UserCardModel')->getUserCardByAttr(['user_id'=>$parames['id']]);
        $str='';
        if($over_time){
          $str='过期时间：'.date('Y-m-d H:i:s',$over_time['over_time']);
        }
        $this->smarty->assign('search','');
        $this->smarty->assign('over_time',$str);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign('arr',$arr);
        $this->smarty->view('user/userCard.phtml');
    }
   public function seeUserWalletWater(){
       $this->checkAuth();
       F()->Resource_module->setTitle('用户钱包流水');
       F()->Resource_module->setJsAndCss(array(
           'home_page'
       ), array(
           'main'
       ));
      $parames=$this->parames;      
      $url='/seeUserWalletWater?id='.$parames['id'];     
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
      $this->smarty->view('user/userWalletWater.phtml');
   }
   
   //用户订单列表
   public function userOrderList(){
       $this->checkAuth();
       $parames=$this->parames;
       F()->Resource_module->setTitle('用户订单列表');
       F()->Resource_module->setJsAndCss(array(
           'home_page'
       ), array(
           'main'
       ));
//       $url='/userOrderList?id='.$parames['id'];
//       $nums=M_Mysqli_Class('md_lixiang','OrderModel')->getCountOrderByAttr(['user_id'=>$parames['id']]);
//       $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
//       $arr=M_Mysqli_Class('md_lixiang','OrderModel')->getAllOrder($showpage['limit'],['user_id'=>$parames['id']]);
//       foreach ($arr as $key => $value) {
//            $user=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$arr[$key]['user_id']]);
//            $info=M_Mysqli_Class('md_lixiang','OrderInfoModel')->getOrderInfoByAttr(['order_id'=>$arr[$key]['id']]);
//            $cabinet=M_Mysqli_Class('md_lixiang','CabinetModel')->getAllBoxByAttr(['cabinet_number'=>$arr[$key]['cabinet_id']]);
//            $arr[$key]['pay']=sprintf("%.1f",$info['pay']/100);
//            $arr[$key]['name']=$user['name'];
//            $arr[$key]['return_battery_id']=$info['return_battery_id'];
//            $arr[$key]['cabinet_name']=$cabinet[0]['cabinet_name'];
//        }
       $uri=$this->makeSearchUrl($this->parames);
       $url='userOrderList?'.$uri;
       $nums=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderCount($this->parames);
       $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
       $pageSize=explode(',',$showpage['limit']);;
       $orderData=M_Mysqli_Class('md_lixiang','OrderModel')->getOrderList($this->parames,$pageSize[0]);
       for ($i=0;$i<count($orderData);$i++){
           $orderData[$i]['pay']=$orderData[$i]['pay']/100;
       }
       $parames['order_status']=isset($parames['order_status'])?$parames['order_status']:'';
       $parames['pay_status']=isset($parames['pay_status'])?$parames['pay_status']:'';
       $parames['pay_ment']=isset($parames['pay_ment'])?$parames['pay_ment']:'';
       $this->smarty->assign('parames',$parames);
       $this->smarty->assign('orderData',$orderData);
       $this->smarty->assign("pages", $showpage['show']);
       $this->smarty->view('user/user_order.phtml');
   }

   //用户充值列表
   public function userPayList(){
       $this->checkAuth();
       $parames=$this->parames;
       F()->Resource_module->setTitle('用户充值列表');
       F()->Resource_module->setJsAndCss(array(
           'home_page'
       ), array(
           'main'
       ));
       $url='/userPayList?id='.$parames['id'];
       $data['user_id']=$parames['id'];
       $nums=M_Mysqli_Class('md_lixiang','TopUpModel')->getTopUpByAttr($data);
       $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
       $arr=M_Mysqli_Class('md_lixiang','TopUpModel')->getAllTopUpByAttr($showpage['limit'],$data);
       $this->smarty->assign('arr',$arr);
       $this->smarty->assign("pages", $showpage['show']);
       $this->smarty->view('user/userPayList.phtml');
   }
   /**
    * 修改用户状态
    */
   public function actionUserStatus()
   {
       $this->checkAuth();
       $data=[
           'id'=>$this->parames['id'],
           'status'=>$this->parames['status']
       ];
       $beforeData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$data['id']]);
       $updateCompany=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($data);
       if($updateCompany > 0){

           //=====================================操作内容记录
           $status=[0=>'启用',1=>'禁用',2=>'删除'];
           $userFlag=[0=>'普通用户',1=>'集团用户'];
           $insertData[0]=['name'=>$beforeData['name'], 'mobile'=>$beforeData['mobile'],'user_flag'=>$userFlag[$beforeData['user_flag']],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
           $tableName[0]['table_name']='md_user : 用户表';
           $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
           //=============================================
           if(array_key_exists('site_id',$this->parames)) {
               $this->msg('操作成功', '/siteAffiliatedUser?site_id=' . $this->parames['site_id'], 'ok');
           }else{
               $this->msg('操作成功','/userList','ok');
           }
       }else{
           $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
           if(array_key_exists('site_id',$this->parames)) {
               $this->msg('操作失败', '/siteAffiliatedUser?site_id=' . $this->parames['site_id'], 'error');
           }else{
               $this->msg('操作失败','/userList','error');
           }
       }
   }
   
   /*
    * 修改用户信息
    * */
   public function editUserData()
   {
       $this->checkAuth();
       $parames=$this->parames;
       if(IS_GET){
//           echo 111;die;
           F()->Resource_module->setTitle('修改用户信息');
           F()->Resource_module->setJsAndCss(array(
               'home_page'
           ), array(
               'main'
           ));
           $userData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['id']]);
           $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$userData['site_id']]);
           $this->smarty->assign('userData',$userData);
           $this->smarty->assign('siteData',$siteData);
           $this->smarty->view('user/update.phtml');
       }elseif(IS_AJAX){
           $parames['site_status']=1;
           $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteData('',$parames);
           $this->setOutPut($siteData);die;
       }else{
           if($userCardNumber=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['card_number'=>$parames['card_number'],'attr_id'=>$parames['attr_id'],'user_flag'=>1,'status'=>0])){
               if($userCardNumber['id']!=$parames['id']){
                   $this->msg('身份证已存在', '/editUserData?id='.$parames['id'] , 'error');die;
               }
           }

           if($userMobile=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['mobile'=>$parames['mobile'],'attr_id'=>$parames['attr_id'],'user_flag'=>1,'status'=>0])){
               if($userMobile['id']!=$parames['id']) {
                   $this->msg('手机号已存在', '/editUserData?id=' . $parames['id'], 'error');die;
               }
           }

           $data=[
               'id'=>$parames['id'],
               'name'=>$parames['name'],
               'user_name'=>$parames['name'],
               'card_number'=>$parames['card_number'],
               'nick_name'=>urlencode($parames['name']),
               'mobile'=>$parames['mobile'],
               'site_id'=>$parames['site_id']
           ];
           $beforeData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$data['id']]);
           $upData=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($data);
           if($upData > 0 ){
               M_Mysqli_Class('md_lixiang','IdCardModel')->editIdCardData(['card_number'=>$parames['card_number'],'name'=>$parames['name']],['user_id'=>$parames['id']]);

               //=========================操作内容记录
               $afterData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$data['id']]);
               $userRes=$this->arrayNewWornData($afterData,$beforeData);
               if($userRes){
                   for($i=0 ; $i<count($userRes); $i++){
                       if($userRes[$i]['clm_name']=='site_id'){
                           $userRes[$i]['old_string']=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$userRes[$i]['old_string']])['site_name'];
                           $userRes[$i]['new_string']=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$userRes[$i]['new_string']])['site_name'];
                       }
                   }
                   $insertData[0]=$userRes;
               }else{
                   $insertData='';
               }
               $tableName[0]['table_name']='md_user : 用户表 -- (用户信息:'.$parames['name'].'-'.$parames['mobile'].')';
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
               //=========================
               $this->msg('操作成功', '/userList' , 'ok');
           }else{
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
               $this->msg('操作失败', '/editUserData?id='.$parames['id'] , 'error');
           }
       }

   }

   /*
    * 清除魔力后台用户电池绑定状态
    * */
    public function clearUserBattery()
    {
        $this->checkAuth();
        $parames=$this->parames;
        $data=[
            'su01'=>date('Y-m-d H:i:s',time()),
            'su02'=>$parames['user_id'],
            'su03'=>''
            ];
        $url=ML_URL."f74cf60e";
        $outPut=$this->apiAndData($url, $data);
        get_log()->log_api('<接口测试> #### 接口名：clearUserBattery 作用：调用魔力获取用户电池信息通知接口参数：'.json_encode($data));
        get_log()->log_api('<接口测试> #### 接口名：clearUserBattery 作用：调用魔力获取用户电池信息接口后获取返回值：'.json_encode($outPut));
        if($outPut['rt_cd']=='0000'){
            if(!empty($outPut['rt_01'])){
                $data1=[
                    'br01'=>date('Y-m-d H:i:s',time()),
                    'br02'=>'',
                    'br03'=>$parames['user_id'],
                    'br04'=>'',
                    'br05'=>$outPut['rt_01']
                ];
                $url1=ML_URL."d8bdf6c3";
                $outPut1=$this->apiAndData($url1, $data1);
                get_log()->log_api('<接口测试> #### 接口名：clearUserBattery 作用：调用魔力解绑用户电池通知接口参数：'.json_encode($data1));
                get_log()->log_api('<接口测试> #### 接口名：clearUserBattery 作用：调用魔力解绑用户电池接口后获取返回值：'.json_encode($outPut1));
                if($outPut1['rt_cd']=='0000'){

                    //===================================操作内容记录
                    $afterData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['user_id']]);
                    $tableName[0]['table_name']='调用魔力后台清除用户电池信息';
                    $data=[
                        '清除日期'=>$data1['br01'],
                        '清除人员名称'=>$afterData['name'],
                        '清除人员手机号'=>$afterData['mobile'],
                        '清除提示信息'=>$outPut1['rt_msg']
                    ];
                    $insertData[0]=$data;
                    $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                    //==================================
                    $this->msg($outPut1['rt_msg'], '/userList' , 'ok');
                }else{
                    $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                    $this->msg($outPut1['rt_msg'], '/userList' , 'error');
                }
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('无电池', '/userList' , 'error');
            }
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg($outPut['rt_msg'], '/userList' , 'error');
        }
    }


    /*
     * 用户密码重置
     * */
    public function userPasswordReset()
    {
//        $this->checkAuth();
        $userData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$this->parames['user_id']]);
        $tmp = range(1,9);
        $newPassWord='md'.implode(array_rand($tmp,4));
        $data=[
            'id'=>$this->parames['user_id'],
            'password'=>md5($newPassWord)
        ];
        $editStatus=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($data);
        if($editStatus){
            $outPut=F()->Sms_module->rSendSms($userData['mobile'],$userData['id'],4,$newPassWord);
            if($outPut['code']=='2000'){

                //===================================操作内容记录
                $tableName[0]['table_name']='md_user:用户表';
                $data=[
                    'user_id'=>$userData['id'],
                    'user_name'=>$userData['name'],
                    'mobile'=>$userData['mobile'],
                    'password'=>$newPassWord,
                    '发送信息结果'=>$outPut['msg']
                ];
                $insertData[0]=$data;
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                //==================================
//                 echo '<pre />';var_dump($outPut);die;
                $this->msg($outPut['msg'], '/userList' , 'ok');
            }else{
//                echo '<pre />';var_dump($outPut);die;
                //===================================操作内容记录
                $tableName[0]['table_name']='md_user:用户表';
                $data=[
                    'user_id'=>$userData['id'],
                    'user_name'=>$userData['name'],
                    'mobile'=>$userData['mobile'],
                    'password'=>$newPassWord,
                    '发送信息结果'=>$outPut['msg']
                ];
                $insertData[0]=$data;
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2,'type'=>'add'],$insertData,$tableName);
                //==================================
                $this->msg($outPut['msg'], '/userList' , 'error');
            }
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('密码重置失败', '/userList' , 'error');
        }

    }


    /*
     * 押金订单列表
     * */
    public function getpledgeOrderData()
    {
        $parames=$this->parames;
        $html='';
        $html.='<table class="layui-table" lay-size="sm">

                                    <thead>
                                      <tr>
                                        <th style="text-align: center;font-weight:bold">订单号</th>
                                        <th style="text-align: center;font-weight:bold">押金金额</th>
                                        <th style="text-align: center;font-weight:bold">支付状态</th>
                                      </tr> 
                                    </thead>
                                    <tbody>';
        $data=[
            'user_id'=>$parames['user_id'],
            'status'=>0,
            'pledge_money_status'=>0,
        ];
        $payStatus=[0=>'未支付',1=>'已支付'];
        $userPledgeOrderData=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getPledgeOrderAll($data);
        if($userPledgeOrderData){
            foreach ($userPledgeOrderData as $k=>$v){
                $v['pledge_money']=$v['pledge_money']/100;
                $html.='<tr><td style="text-align: center">'.$v['order_sn'].'</td><td style="text-align: center">'.$v['pledge_money'].'</td><td style="text-align: center">'.$payStatus[$v['pay_status']].'</td></tr>';
            }
        }else{
            $html.="<tr><td colspan='10' style='text-align: center'>无</td></tr>";
        }

        $html.='</tbody></table>';
        $this->setOutPut($html);die;
    }


}


