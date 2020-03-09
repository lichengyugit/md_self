<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
Class topupconfig extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);      
        $this->smarty->assign("function", "topUpconfig");       
    }
    
    public function index(){
            $this->checkAuth();
            F()->Resource_module->setTitle('充值配置列表');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $url = "/topUpConfigList";
            $nums=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->getTopUpByAttr($this->parames);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $arr=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->getAllTopUp($showpage['limit'],$this->parames);
            //$arr=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->getAlls();
            //print_r($arr);exit;
            $this->smarty->assign('arr',$arr);
            $this->smarty->assign('pages',$showpage['show']);
            $this->smarty->view('topUpConfig/list.phtml');
              
        }  
        
        //更改充值配置
        public function changeTopUp(){
            $this->checkAuth();
            $action=$_SERVER['REQUEST_METHOD'];
            if($action=='POST'){
                $parames=$this->parames;
                $this->form_validation->set_data($parames);
                $this->form_validation->set_rules('amount','充值金额','numeric|min_length[1]|max_length[9]|required');
                $this->form_validation->set_rules('givingAmount','赠送金额','numeric|min_length[1]|max_length[9]|required');
                $this->form_validation->run();
                if($this->form_validation->run()===FALSE){
                    $this->msg($this->form_validation->validation_error(),'/changeTopUp?id='.$this->parames['id'],'error');
                }else{
                    $data=[
                        'amount'=>$parames['amount'],
                        'giving_amount'=>$parames['givingAmount'],
                        'user_type'=>$parames['userType'],
                        'pay_type'=>$parames['payType']
                    ];
                    if($arr=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->getAllTopUpConfig($data)){
                        if($arr[0]['id']!=$parames['id']){
                            $this->msg('此条类型已存在','/changeTopUp?id='.$this->parames['id'],'error');
                            exit;
                        }                        
                    }else{
                            $data['id']=$parames['id'];
                            if(M_Mysqli_Class('md_lixiang','TopUpConfigModel')->updateTopUp($data)){
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                                $this->msg('修改成功','/changeTopUp?id='.$this->parames['id'],'ok');
                            }else{
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                                $this->msg('修改失败','/changeTopUp?id='.$this->parames['id'],'error');
                            }  
                        }             
                }
            }else{
                F()->Resource_module->setTitle('编辑充值配置');
                F()->Resource_module->setJsAndCss(array(
                    'home_page'
                ), array(
                    'main'
                ));
                $parames=$this->parames;
                $arr=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->getAllTopUpConfig($parames);
                $this->smarty->assign('arr',$arr[0]);
                $this->smarty->view('topUpConfig/update.phtml');
            }
         }
         
       //添加充值配置
       public function addTopUp(){
           $this->checkAuth();
           $action=$_SERVER['REQUEST_METHOD'];
           if($action=='POST'){
                $parames=$this->parames;
                $this->form_validation->set_data($parames);
                $this->form_validation->set_rules('amount','充值金额','numeric|min_length[1]|max_length[9]|required');
                $this->form_validation->set_rules('givingAmount','赠送金额','numeric|min_length[1]|max_length[9]|required');
                $this->form_validation->run();
                if($this->form_validation->run()===FALSE){
                    $this->msg($this->form_validation->validation_error(),'/addTopUp','error');
                }else{
                    $data=[
                        'amount'=>$parames['amount'],
                        'giving_amount'=>$parames['givingAmount'],
                        'user_type'=>$parames['userType'],
                        'pay_type'=>$parames['payType']
                    ];
                    if($arr=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->getAllTopUpConfig($data)){
                        if($arr[0]['status']<2){
                            $this->msg('此条类型已存在','/addTopUp','error');
                        }else{
                            $update['id']=$arr[0]['id'];
                            $update['status']=0;
                            $update['create_date']=date("Y-m-d H:i:s",time());
                            $update['create_time']=time();
                            if(M_Mysqli_Class('md_lixiang','TopUpConfigModel')->updateTopUp($update)){
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                                $this->msg('添加成功','/addTopUp','ok');
                                exit;
                            }else{
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                                $this->msg('添加失败','/addTopUp','error');
                                exit;
                            }
                        }
                        
                    }else{
                        $data['create_date']=date("Y-m-d H:i:s",time());
                        $data['create_time']=time();
                        if(M_Mysqli_Class('md_lixiang','TopUpConfigModel')->addTopUpConfig($data)){
                            $this->msg('添加成功','/addTopUp','ok');
                        }else{
                            $this->msg('添加失败','/addTopUp','error');
                        }  
                    }                
                }
           }else{   
               F()->Resource_module->setTitle('添加充值配置');
               F()->Resource_module->setJsAndCss(array(
                   'home_page'
               ), array(
                   'main'
               ));
               $this->smarty->view('topUpConfig/insert.phtml');
           }
       }
       
       /**
        * 修改充值配置状态
        */
       public function actionTopUpStatus(){
           $this->checkAuth();
           $updateCompany=M_Mysqli_Class('md_lixiang','TopUpConfigModel')->updateTopUp($this->parames);
           if($updateCompany){
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
               $this->msg('操作成功','/topUpConfigList','ok');
           }else{
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
               $this->msg('操作失败','/topUpConfigList','error');
           }
       }
}