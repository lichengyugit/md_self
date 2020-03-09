<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class role extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->smarty->assign('resourceUrl', RESOURCE_URL);
        $this->smarty->assign('baseUrl', BASE_URL);
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('function','admin');
    }

    /**
     * 角色列表
     */
    public function roleList()
    {   $this->checkAuth();
        //$this->msg('添加成功','companyList','ok');exit;
        F()->Resource_module->setTitle('角色列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/roleList";
        $nums=M_Mysqli_Class('md_lixiang','RoleModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $roleList=M_Mysqli_Class('md_lixiang','RoleModel')->getAllRoleByAttr($showpage['limit'],$this->parames);
        $this->smarty->assign('roleList',$roleList);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('role/list.phtml');
    }

    /**
     * 新增与修改角色
     */
    public function actionRole(){
        $this->checkAuth();
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(array_key_exists('id', $this->parames)){
            F()->Resource_module->setTitle('权限分配');
            /* if($this->parames['id'])
            {                  //    角色修改判断
                $action=$_SERVER['REQUEST_METHOD'];
                if($action=='POST')
                {      
                     $parames = $this->parames;
                      $data['auth_id'] = implode(',',$parames['authid']);
                     // var_dump($parames);die;
                      $data['id']= $parames['id'];
                     $update=M_Mysqli_Class('md_lixiang','RoleAuthModel')->updateRoleAuthByAttr($data);
                    if($update){
                        $this->msg('修改成功','/actionRole?id='.$this->parames['id'],'ok');
                    }else{
                        $this->msg('修改失败','/actionRole?id='.$this->parames['id'],'error');
                    }
                }
            } */
            $roleInfo=M_Mysqli_Class('md_lixiang','RoleModel')->getRoleInfoByAttr($this->parames);
            $data['role_id']=$roleInfo['id'];
            $auth=M_Mysqli_Class('md_lixiang','RoleAuthModel')->getRoleAuthByAttr($data);
            $authGroup = M_Mysqli_Class('md_lixiang','AuthModel')->getRoleInfo();               //获取所有父权限
            $childInfo = M_Mysqli_Class('md_lixiang','AuthModel')->getRoleChildInfo();    // 获取所有子权限
            //$keyArray=array('id');
            if(count($auth)==0){
                $authId[0]=0;
            }else{
                foreach($auth as $k=>$v){
                    $authId[]= $v['auth_id'];   
                } 
            }
            //print_r($authId);exit;
            $this->smarty->assign('authGroup',$authGroup);
            $this->smarty->assign('authId',$authId);
            $this->smarty->assign('childInfo',$childInfo);
            $this->smarty->assign('roleInfo',$roleInfo);
            $this->smarty->view('role/updata.phtml');
        }else{
            F()->Resource_module->setTitle('添加角色');
            $this->smarty->view('role/insert.phtml');
        }
    }


    /**
     * 新增角色
     */
    public function saveRole(){
        $this->parames['create_time']=time();
        $this->parames['create_date']=date('Y-m-d H:i:s');
        $save=M_Mysqli_Class('md_lixiang','RoleModel')->addRoleConfig($this->parames);
        if($save>0){
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
            $this->msg('添加成功','/roleList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->msg('添加失败','/roleList','error');
        }
    }


    /**
     * 修改角色
     */
    public function updateRole(){
        $update=M_Mysqli_Class('md_lixiang','RoleModel')->updateRoleByAttr($this->parames);
        if($update){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('修改成功','/actionRole?id='.$this->parames['id'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/actionRole?id='.$this->parames['id'],'error');
        }
    }


    /**
     * 启用禁用删除角色
     */
    public function delRole(){
        $this->checkAuth();
        $update=M_Mysqli_Class('md_lixiang','RoleModel')->updateRoleByAttr($this->parames);
        if($update){
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1]);
            $this->msg('操作成功','/roleList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/roleList','error');
        }
    }
    /**
     * 分配权限
     */
    public function assignAuth(){
        $parames=$this->parames;
        $where=array("role_id"=>$parames['role_id']);
        $del=M_Mysqli_Class('md_lixiang','RoleAuthModel')->delRoleAuthByAttr($where);
        //print_r($del);exit;
        foreach($parames['authid'] as $k=>$v){
            $data[$k]['role_id']=$parames['role_id'];
            $data[$k]['auth_id']=$v;
            $data[$k]['create_time']=time();
            $data[$k]['create_date']=date("Y-m-d H:i:s",$data[$k]['create_time']);
        }
        $insert=M_Mysqli_Class('md_lixiang','RoleAuthModel')->saveRoleAuth($data);
        if($insert>0){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('操作成功','/actionRole?id='.$parames['role_id'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('操作失败','/actionRole?id='.$parames['role_id'],'error');
        }

    }
}

