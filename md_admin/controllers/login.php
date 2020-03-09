<?php
if (!defined('ROOTPATH'))
{
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}

class login extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->smarty->assign('resourceUrl', RESOURCE_URL);      // 静态资源地址  有点问题  dev.r.xianghaundian.com/
        $this->smarty->assign('baseUrl', BASE_URL);               //URL地址
        $this->parames=$this->getParames();//调用http流方法
    }

    /**
     * 用户登陆与验证
     */
    public function login()
    {
        F()->Resource_module->setTitle('登陆魔动入口');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('login/login.phtml');
    }

    /**
     * [ postLoginFunction ] 获取用户信息
     * @param $parames
     */
     private function  postLoginFunction($parames)
     {
         $userInfo = M_Mysqli_Class('md_lixiang', 'AdminModel')->getUserInfoByAttr($parames);
         return $userInfo;
     }
     
     public function checkLogin(){
         $parames  = $this->parames;  // 获取用户提交数据post
         $this->form_validation->set_data($parames);
         $this->form_validation->set_rules('userName','用户名','trim|min_length[6]|max_length[18]|required');
         $this->form_validation->set_rules('password','密码','trim|min_length[6]|max_length[32]');
         if ($this->form_validation->run() === FALSE) {
             $outPut['status'] = "error";
             $outPut['code'] = "1001";
             $outPut['msg'] = $this->form_validation->validation_error();
             $outPut['data'] = "";
         }else{
             $data['mobile']=$parames['userName'];
             //$data['password']=md5($parames['password']);
             if($userinfo = $this->postLoginFunction($data))
             {
                 if($userinfo['status']==1){
                     $outPut['status']='error';
                     $outPut['msg']='此账户已被禁用,请联系管理员';
                    }elseif($userinfo['status']==2){
                        $outPut['status']='error';
                        $outPut['msg']='用户名或密码错误';
                    }else{
                        if(md5(md5($parames['password']).$userinfo['salt'])==$userinfo['password']){
                        $arr['admin_id']=$userinfo['id'];
                        //查询此用户角色和权限
                        $adminAuth=$this->getUserAuth($arr);
                        foreach ($adminAuth as $key => $value) {
                            if($adminAuth[$key]['platform']!=0){
                                unset($adminAuth[$key]);
                            }
                        }
                        foreach($adminAuth as $k=>$v){
                            $authRuisList[]=$v['ruis'];
                        }
                        $this->session->set_userdata('userName',$userinfo['user_name']);
                        $this->session->set_userdata('mobile',$userinfo['mobile']);
                        $this->session->set_userdata('authList',$authRuisList);
                        $this->session->set_userdata('user_id',$userinfo['id']);
                        $outPut['status']='ok';
                        $outPut['msg']='登录成功'; 
                     }else{
                         $outPut['status']='error';
                         $outPut['msg']='用户名或密码错误';
                     }
                    }
             } else {
                 $outPut['status']='error';
                 $outPut['msg']='用户名或密码错误';
             }         
         }
         $this->setOutPut($outPut);
     } 
     
     //获取用户权限
     private function getUserAuth($parames){
        //根据用户id找到属于用户的角色id
         if($role=M_Mysqli_Class('md_lixiang','AdminRoleModel')->getAdminRoleByAttr($parames)){
             $data['role_id']=$role['role_id'];
             //根据找到的角色id 找到所有的权限id
             if($auth=M_Mysqli_Class('md_lixiang','RoleAuthModel')->getRoleAuthByAttr($data)){
                 foreach ($auth as $k=>$v){
                     $auths[$k]=$v['auth_id'];
                 }                 
                 $where=implode(',', $auths);
                 //根据角色获取该角色下的权限
                 $arr=M_Mysqli_Class('md_lixiang','AuthModel')->getAuthWhereIn($where);
                 return $arr;
             }else{
                 $arr[0]=0;
                 return $arr;
             }
         }else{
             $arr[0]=0;
             return $arr;
         }
     }  

     //退出登录
     public function logOut(){
         $this->session->sess_destroy();
         header('location:login');
     }
     







     
}





