<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
Class cardconfig extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "cardconfig");
    }
    
    public function index(){    
            $this->checkAuth();
            F()->Resource_module->setTitle('月卡配置列表');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $url = "/cardConfigList";
            $nums=M_Mysqli_Class('md_lixiang','CardConfigModel')->getCardConfigNumByAttr($this->parames);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $arr=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAllCardConfigs($showpage['limit'],$this->parames);
            //$arr=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAlls();
            //print_r($arr);exit;
            $this->smarty->assign('arr',$arr);
            $this->smarty->assign("pages", $showpage['show']);
            $this->smarty->view('cardConfig/list.phtml');
        }  
        
        //更改充值配置
        public function changeCard(){
            $this->checkAuth();
            $action=$_SERVER['REQUEST_METHOD'];
            if($action=='POST'){
                $parames=$this->parames;
                $this->form_validation->set_data($parames);
                $this->form_validation->set_rules('amount','金额','numeric|min_length[1]|max_length[9]|required');
                $this->form_validation->run();
                if($this->form_validation->run()===FALSE){
                    $this->msg($this->form_validation->validation_error(),'/changeCard?id='.$this->parames['id'],'error');
                }else{
                    $data=[
                        'name'=>$parames['cardName'],
                        'card_type'=>$parames['cardType'],
                        'user_type'=>$parames['userType'],
                        'pay_type'=>$parames['payType']
                    ];
                    if($arr=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAllCardConfig($data)){
                        if($arr[0]['id']!=$parames['id']){
                            $this->msg('此卡类型已存在','/changeCard?id='.$this->parames['id'],'error');exit;           
                        }else{
                            $data['id']=$parames['id'];
                            $data['amount']=$parames['amount'];
                            if(M_Mysqli_Class('md_lixiang','CardConfigModel')->updateCard($data)){
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);

                                $this->msg('修改成功','/changeCard?id='.$this->parames['id'],'ok');
                            }else{
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                                $this->msg('修改失败','/changeCard?id='.$this->parames['id'],'error');
                            }
                        }                       
                    }else{
                        $data['id']=$parames['id'];
                        $data['amount']=$parames['amount'];
                        if(M_Mysqli_Class('md_lixiang','CardConfigModel')->updateCard($data)){
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                            $this->msg('修改成功','/changeCard?id='.$this->parames['id'],'ok');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->msg('修改失败','/changeCard?id='.$this->parames['id'],'error');
                        }  
                    }                
                }
            }else{
                F()->Resource_module->setTitle('更改月卡配置');
                F()->Resource_module->setJsAndCss(array(
                    'home_page'
                ), array(
                    'main'
                ));
                $parames=$this->parames;
                $arr=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAllCardConfig($parames);
                $this->smarty->assign('arr',$arr[0]);
                $this->smarty->view('cardConfig/update.phtml');
            }
         }
         
         
       //添加充值配置
       public function addCardConfig(){
           $this->checkAuth();
           $action=$_SERVER['REQUEST_METHOD'];
           $cdrdType=[1=>'年卡',2=>'季卡',3=>'月卡'];
           $userType=[0=>'普通用户',1=>'集团用户'];
           $payType=[1=>'微信',2=>'支付宝'];
           if($action=='POST'){
                $parames=$this->parames;
                $this->form_validation->set_data($parames);
                $this->form_validation->set_rules('amount','价格','numeric|min_length[1]|max_length[9]|required');
                $this->form_validation->run();
                if($this->form_validation->run()===FALSE){
                    $this->msg($this->form_validation->validation_error(),'/addCard','error');
                }else{
                    $data=[
                        'city'=>$parames['city'],
                        'name'=>$parames['cardName'],
                        'card_type'=>$parames['cardType'],
                        'user_type'=>$parames['userType'],
                        'pay_type'=>$parames['payType']
                    ];
                    if($arr=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAllCardConfig($data)){
                        if($arr[0]['status']<2){
                            $this->msg('此卡类型已存在','/addCard','error');
                        }else{
                            $update['id']=$arr[0]['id'];
                            $update['amount']=$parames['amount'];
                            $update['status']=0;
                            $update['create_date']=date("Y-m-d H:i:s",time());
                            $update['create_time']=time();
                            if(M_Mysqli_Class('md_lixiang','CardConfigModel')->updateCard($update)){

                                //=================================操作内容记录
                                $tableName[0]['table_name']='md_card_config : 会员卡配置表';
                                $data['card_type']=$cdrdType[$data['card_type']];
                                $data['user_type']=$userType[$data['user_type']];
                                $data['pay_type']=$payType[$data['pay_type']];
                                $insertData[0]=$update;
                                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                                //=================================

                                $this->msg('添加成功','/addCard','error');
                            }else{
                                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                                $this->msg('添加失败','/addCard','error');
                            }
                        }
                    }else{
                        $data['amount']=$parames['amount'];
                        $data['create_date']=date("Y-m-d H:i:s",time());
                        $data['create_time']=time();
                        if(M_Mysqli_Class('md_lixiang','CardConfigModel')->addCardConfig($data)){

                            //=================================操作内容记录
                            $tableName[0]['table_name']='md_card_config : 会员卡配置表';
                            $data['card_type']=$cdrdType[$data['card_type']];
                            $data['user_type']=$userType[$data['user_type']];
                            $data['pay_type']=$payType[$data['pay_type']];
                            $insertData[0]=$data;
                            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                            //=================================

                            $this->msg('添加成功','/addCard','ok');
                        }else{
                            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                            $this->msg('添加失败','/addCard','error');
                        }  
                    }                
                }
           }else{
               F()->Resource_module->setTitle('添加月卡');
               F()->Resource_module->setJsAndCss(array(
                   'home_page'
               ), array(
                   'main'
               ));
               $province=M_Mysqli_Class('md_lixiang','ProvinceModel')->getAllProvince([]);
               $this->smarty->assign('province',$province);
               $this->smarty->view('cardConfig/insert.phtml');
           }
       }
       /**
        * 省市联动接口
        */
       public function city(){
            $parames=$this->parames;
            $city=M_Mysqli_Class('md_lixiang','CityModel')->getAllCityConfig(['ProvinceID'=>$parames['province']]);
            $str="<select class='form-control' name='city'>";
            foreach ($city as $k => $v) {
                $str.="<option value='".$v['CityName']."'>".$v['CityName']."</option>";
            }
            $str.="</select>";
            $this->setOutPut($str);
       }

       /**
        * 修改月卡配置状态
        */
       public function actionCardStatus(){
           $this->checkAuth();
           $cdrdType=[1=>'年卡',2=>'季卡',3=>'月卡'];
           $userType=[0=>'普通用户',1=>'集团用户'];
           $payType=[1=>'微信',2=>'支付宝'];
           $beforeData=M_Mysqli_Class('md_lixiang','CardConfigModel')->getCardConfigByAttr(['id'=>$this->parames['id']]);
           $updateCompany=M_Mysqli_Class('md_lixiang','CardConfigModel')->updateCard($this->parames);
           if($updateCompany){

               //===============================操作内容记录
               $status=[0=>'启用',1=>'禁用',2=>'删除'];
               $insertData[0]=['name'=>$beforeData['name'], 'amount'=>$beforeData['amount'],'card_type'=>$cdrdType[$beforeData['card_type']],
                   'user_type'=>$userType[$beforeData['user_type']],'pay_type'=>$payType[$beforeData['pay_type']],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
               $tableName[0]['table_name']='md_card_config : 会员卡配置表';
               $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
               //==============================================

               $this->msg('操作成功','/cardConfigList','ok');
           }else{
               $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
               $this->msg('操作失败','/cardConfigList','error');
           }
       }



}
