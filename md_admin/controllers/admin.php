<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class admin extends MY_Controller
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
     * 后台管理列表
     */
    public function admin()
    { $this->checkAuth();
        F()->Resource_module->setTitle('管理员列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $uri=$this->makeSearchUrl($this->parames);
        $url = "admin?".$uri;
        $nums=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminRoleData('',$this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($nums));
        $limit=" LIMIT ".$showpage['limit'];
        $adminList=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminRoleData($limit,$this->parames);
        $roleData=M_Mysqli_Class('md_lixiang','RoleModel')->getAllsRoleByAttr([1=>1]);
        $this->smarty->assign('adminList',$adminList);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign("roleData", $roleData);
        $this->smarty->assign("parames", $this->parames);
        $this->smarty->view('admin/list.phtml');
    }


    /**
     * 修改后台用户信息
     */
    public function actionAdmin(){ 
        $this->checkAuth();
        if(array_key_exists('id', $this->parames)){
            F()->Resource_module->setTitle('修改管理员');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            /* $action=$_SERVER['REQUEST_METHOD'];
             if($action=='POST'){            // 管理员信息修改

                 $update=M_Mysqli_Class('md_lixiang','AdminModel')->updateAdminByAttr($this->parames);
                 if($update){
                     //var_dump($update);die;
                     $this->msg('修改成功','/actionAdmin?id='.$this->parames['id'],'ok');
                 }else{
                     $this->msg('修改失败','/actionAdmin?id='.$this->parames['id'],'error');
                 }
             } */
            $adminInfo=M_Mysqli_Class('md_lixiang','AdminModel')->getUserInfoByAttr($this->parames);
            $data[1]=1;
            $role=M_Mysqli_Class('md_lixiang','RoleModel')->getAllsRoleByAttr($data);
            $parames['admin_id']=$this->parames['id'];
            $adminRole=M_Mysqli_Class('md_lixiang','AdminRoleModel')->getAdminRoleByAttr($parames);
            //print_r($role);exit;
            $this->smarty->assign('adminRole',$adminRole);
            $this->smarty->assign('role',$role);
            $this->smarty->assign('adminInfo',$adminInfo);
            //var_dump($adminInfo);die;
            $this->smarty->view('admin/updata.phtml');
        }else{
            F()->Resource_module->setTitle('添加管理员');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $data[1]=1;
            $role=M_Mysqli_Class('md_lixiang','RoleModel')->getAllsRoleByAttr($data);
            $this->smarty->assign('role',$role);
            $this->smarty->view('admin/insert.phtml');
        }
    }


    /**
     *  启用禁用删除管理员
     */
    public function delAdmin(){
        $this->checkAuth();
        $beforeAdminData=M_Mysqli_Class('md_lixiang','AdminModel')->getUserInfoByAttr(['id'=>$this->parames['id']]);
        $update=M_Mysqli_Class('md_lixiang','AdminModel')->updateAdminByAttr($this->parames);
        if($update){
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['user_name'=>$beforeAdminData['user_name'], 'mobile'=>$beforeAdminData['mobile'],'status'=>$status[$this->parames['status']].','.$status[$beforeAdminData['status']]];
            $tableName[0]['table_name']='md_admin : 管理员表';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            $this->msg('操作成功','/admin','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/admin','error');
        }
    }

    /**
     * 修改管理员
     */
    public function updateAdmin(){
        $this->checkAuth();
        $parames=$this->parames;
        $data['admin_id']=$parames['id'];
        if($arr=M_Mysqli_Class('md_lixiang','AdminRoleModel')->getAdminRoleByAttr($data)){
            unset($data['admin_id']);
            $this->form_validation->set_data($parames);
            $this->form_validation->set_rules('user_name','用户名称','required');
            /*$this->form_validation->set_rules('mobile','手机号','trim|exact_length[11]|required');*/
            if($parames['password']){
              $this->form_validation->set_rules('password','密码','trim|min_length[6]|max_length[18]|required');
            }
            if ($this->form_validation->run() === FALSE) {
                $this->msg($this->form_validation->validation_error(),'/actionAdmin?id='.$parames['id'],'error');
            }
                $data=[
                  'role_id'=>$parames['role'],
                  'id'=>$arr['id']
                ];
                $str=$this->getRandomString(5);
                $arrs=[
                  'user_flag'=>$parames['role'],
                  'mobile'=>$parames['mobile'],
                  'password'=>$parames['password']==''?$parames['pass']:md5(md5($parames['password']).$str),
                  'salt'=>$parames['password']==''?$parames['sal']:$str,
                  'user_name'=>$parames['user_name'],
                  'id'=>$parames['id']
                ];
            $beforeAdminData=M_Mysqli_Class('md_lixiang','AdminModel')->getUserInfoByAttr(['id'=>$parames['id']]);
            $a=M_Mysqli_Class('md_lixiang','AdminRoleModel')->updateAdminRoleByAttr($data);
            $b=M_Mysqli_Class('md_lixiang','AdminModel')->updateAdminByAttr($arrs);
            $tableName[0]['table_name']='md_admin : 管理员表 -- 管理员信息('.$parames['user_name'].'-'.$parames['mobile'].')';
            $tableName[1]['table_name']='md_admin_role : 管理员角色表';
           if($a ||  $b){

               //================================================>操作内容记录
               $afterAdminData=M_Mysqli_Class('md_lixiang','AdminModel')->getUserInfoByAttr(['id'=>$parames['id']]);
               $afterRoleData=M_Mysqli_Class('md_lixiang','AdminRoleModel')->getAdminRoleByAttr(['id'=>$arr['id']]);
               $adminRes=$this->arrayNewWornData($afterAdminData,$beforeAdminData);
               if($adminRes) $insertData[0]=$adminRes;
               $roleRes=$this->arrayNewWornData($afterRoleData,$arr);
               $roleDataAll=M_Mysqli_Class('md_lixiang','RoleModel')->getAllsRoleByAttr([1=>1]);
               foreach ($roleDataAll as $v){
                   $roleName[$v['id']]=$v['name'];
               }
               if($roleRes)$roleRes[0]['old_string']=$roleName[$roleRes[0]['old_string']]; $roleRes[0]['new_string']=$roleName[$roleRes[0]['new_string']];
               $insertData[1]=$roleRes;
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
               //===================================================>
               $this->msg('操作成功','/actionAdmin?id='.$parames['id'],'ok');
           }else{
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
               $this->msg('操作失败','/actionAdmin?id='.$parames['id'],'error');
           }
        }else{
            $data['role_id']=$parames['role'];
            if(M_Mysqli_Class('md_lixiang','AdminRoleModel')->save($data)){
               $this->msg('操作成功','/actionAdmin?id='.$parames['id'],'ok');
            }else{
               $this->msg('操作失败','/actionAdmin?id='.$parames['id'],'error');
           }
        }
    }

    /**
     * 添加管理员
     */
    public function insertAdmin(){
        $this->checkAuth();
        $parames=$this->parames;
        $this->form_validation->set_data($parames);
        $this->form_validation->set_rules('userName','用户名称','required');
        $this->form_validation->set_rules('mobile','手机号','trim|exact_length[11]|required');
        $this->form_validation->set_rules('password','密码','trim|min_length[6]|max_length[18]|required');
        $this->form_validation->set_rules('rePassword','确认密码','matches[password]|required');
        if ($this->form_validation->run() === FALSE) {
            $this->msg($this->form_validation->validation_error(),'/actionAdmin','error');
        }else{
           $userName['user_name']=$parames['userName'];
           if(M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttr($userName)){
               $this->msg('用户名称已存在','/actionAdmin','error');
           }
           $mobile['mobile']=$parames['mobile'];
           if(M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttr($mobile)){
               $this->msg('手机号已存在','/actionAdmin','error');
           }
           $str=$this->getRandomString(5);
           $data=[
               'user_name'=>$parames['userName'],
               'password'=>md5(md5($parames['password']).$str),
               'user_flag'=>$parames['flag'],
               'salt'=>$str,
               'mobile'=>$parames['mobile'],
               'create_ip'=>$this->getClientIP()
           ];
            $data['password']=$parames['password'];
           if($id=M_Mysqli_Class('md_lixiang','AdminModel')->saveAdmin($data)){
               $adminAuth=[
                   'admin_id'=>$id,
                   'role_id'=>$parames['role']
               ];
               $res=M_Mysqli_Class('md_lixiang','AdminRoleModel')->save($adminAuth);
               $tableName[0]['table_name']='md_admin : 管理员表';
               $tableName[1]['table_name']='md_admin_role : 管理员角色表';
               $insertData=[
                   0=>$data,
                   1=>$adminAuth,
               ];
               if($res){
                   $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                   $this->msg('添加成功','/actionAdmin','ok');
               }else{
                   $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2,'type'=>'add'],$insertData,$tableName);
                   $this->msg('添加失败','/actionAdmin','error');
               }
           }else{
               $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2,'type'=>'add'],$data,['table_name'=>'md_admin']);
               $this->msg('添加失败','/actionAdmin','error');
           }
        }
    }

    



}


