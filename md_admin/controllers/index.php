<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class index extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->smarty->assign('resourceUrl', RESOURCE_URL);
        $this->smarty->assign('baseUrl', BASE_URL);
		  $this->parames=$this->getParames();//调用http流方法
        $this->smarty->assign('function','index');
    }

    /**
     * 首页入口
     */
    public function index()
    {
        F()->Resource_module->setTitle('首页');
        F()->Resource_module->setJsAndCss(array(
                'home_page'
        ), array(
                'main'
        ));
        if(!$_SESSION['mobile']==NULL)
        {   
            $test[0]=0;
            $user=M_Mysqli_Class('md_lixiang','UserModel')->getUserByAttr(['user_flag'=>0,'or_user_flag'=>1,'identification'=>1]);
            $order=M_Mysqli_Class('md_lixiang','OrderModel')->getCountOrderByAttr($test);
            $battery=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttr($test);
            $cabinet=M_Mysqli_Class('md_lixiang','CabinetModel')->getNumByAttr($test);
            $data=[
                'user'=>$user,
                'order'=>$order,
                'battery'=>$battery,
                'cabinet'=>$cabinet
            ];
            $this->smarty->assign('data',$data);
            $this->smarty->view('index/index.phtml');
        }else{
             $this->redirect('login');
        }
    }



      /*
       * 查看管理员信息
       * */
      public function getAdminData()
      {
          F()->Resource_module->setTitle('管理员信息');
          F()->Resource_module->setJsAndCss(array(
              'home_page'
          ), array(
              'main'
          ));
          $adminData=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttrOne(['id'=>$this->session->userdata['user_id']]);
          $adminRoleData=M_Mysqli_Class('md_lixiang','AdminRoleModel')->getAdminRoleByAttr(['admin_id'=>$this->session->userdata['user_id']]);
          $roleData=M_Mysqli_Class('md_lixiang','RoleModel')->getRoleInfoByAttr(['id'=>$adminRoleData['role_id']]);
          $data=[
              'user_name'=>$adminData['user_name'],
              'user_flag'=>$roleData['name'],
              'create_ip'=>$adminData['create_ip'],
              'create_date'=>$adminData['create_date']
          ];
          $this->smarty->assign('data',$data);
          $this->smarty->view('index/admin_data_list.phtml');
      }

    /*
   * 管理员修改密码
   * */
    public function editAdminPassword()
    {
        if(IS_GET){
            F()->Resource_module->setTitle('修改密码');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $adminData=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttrOne(['id'=>$this->session->userdata['user_id']]);
            $this->smarty->assign('adminData',$adminData);
            $this->smarty->view('index/update.phtml');
        }else{
            $adminDatasalt=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttrOne(['id'=>$this->session->userdata['user_id']]);
            $password=md5(md5($this->parames['password']).$adminDatasalt['salt']);
            $adminData=M_Mysqli_Class('md_lixiang','AdminModel')->updateAdminByAttr(['id'=>$this->session->userdata['user_id'],'password'=>$password]);
            if($adminData > 0){
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                $this->msg('操作成功', '/index' , 'ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('操作失败', '/editAdminPassword' , 'error');
            }
        }

    }
}
