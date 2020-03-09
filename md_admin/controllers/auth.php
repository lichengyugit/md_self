<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class auth extends MY_Controller
{

    public function __construct()
    {
       parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);       
        $this->smarty->assign('function','admin');
    }

    /**
     * 权限管理
     */
    public function authList()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('权限列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/authList";
        $nums=M_Mysqli_Class('md_lixiang','AuthModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $authList=M_Mysqli_Class('md_lixiang','AuthModel')->getAllAuthByAttr($showpage['limit'],$this->parames);
        $this->smarty->assign('authList',$authList);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('auth/list.phtml');
    }

    /**
     * 新增与修改权限页面
     */
    public function actionAuth(){
        $this->checkAuth();
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(array_key_exists('id', $this->parames)){
            F()->Resource_module->setTitle('修改权限');

            /* if($this->parames['id']){                  //  权限修改判断
               $action=$_SERVER['REQUEST_METHOD'];
                 if($action=='POST'){
                     $update=M_Mysqli_Class('md_lixiang','AuthModel')->updateAuthByAttr($this->parames);
                     if($update){                        
                         $this->msg('修改成功','/actionAuth?id='.$this->parames['id'],'ok');
                     }else{
                         $this->msg('修改失败','/actionAuth?id='.$this->parames['id'],'error');
                     }
                } 
            } */
            $authInfo=M_Mysqli_Class('md_lixiang','AuthModel')->getAuthInfoByAttr($this->parames);
            $data['pid']=0;
            $arr=M_Mysqli_Class('md_lixiang','AuthModel')->getAuthInfoByAttr($data);
            //print_r($authInfo);die;
            $this->smarty->assign('arr',$arr);
            $this->smarty->assign('authInfo',$authInfo[0]);
            $this->smarty->view('auth/updata.phtml');
        }else{
            F()->Resource_module->setTitle('权限添加');
            $arr = M_Mysqli_Class('md_lixiang','AuthModel')->getRoleInfo();               //获取所有父权限
            $this->smarty->assign('arr',$arr);
            $this->smarty->view('auth/insert.phtml');
        }
    }

    /**
     * 新增权限
     */
    public function saveAuth(){
        //$this->checkAuth();
        $parames=$this->parames;
        if($parames['authClass']!=0 && $parames['ruis']==NULL){
            $this->msg('权限路径不能为空','/actionAuth','error');exit;
        }
        $data['platform']=$parames['platform'];
        $data['pid']=$parames['authClass'];
        $data['ruis']=$parames['ruis'];
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $title['title']=$parames['authName'];
        $arr=M_Mysqli_Class('md_lixiang','AuthModel')->getAuthInfoByAttr($title);
        if($arr[0]){
            if($arr[0]['status']!=2){
                $this->msg('此权限名称已存在','/actionAuth','error');exit;
            }else{
                $data['status']=0;
                $data['id']=$arr[0]['id'];               
                if(M_Mysqli_Class('md_lixiang','AuthModel')->updateAuthByAttr($data)){
                    $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                    $this->msg('添加成功','/actionAuth','ok');exit;
                }else{
                    $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                    $this->msg('添加失败','/actionAuth','error');exit;
                }
            }
        }else{            
            $data['title']=$parames['authName'];
           if(M_Mysqli_Class('md_lixiang','AuthModel')->saveAuth($data)){
               //====================================操作内容记录
               $tableName[0]['table_name']='md_auth : 权限表';
               $insertData[0]=$data;
               $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
               //====================================
               $this->msg('添加成功','/actionAuth','ok');
           }else{
               $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
               $this->msg('添加失败','/actionAuth','error');
           } 
        }        
    }

    /**
     * 修改权限
     */
    public function updateAuth(){
        //$this->checkAuth();
        $parames=$this->parames;
        $data['id']=$parames['id'];
        $data['title']=$parames['title'];
        $beforeData=M_Mysqli_Class('md_lixiang','AuthModel')->getOneAuth(['id'=>$data['id']]);
        if($parames['pid']==0){
            if(M_Mysqli_Class('md_lixiang','AuthModel')->updateAuthByAttr($data)){
                //======================================操作内容记录
                $afterData=M_Mysqli_Class('md_lixiang','AuthModel')->getOneAuth(['id'=>$data['id']]);
                $insertData[0]=$this->arrayNewWornData($afterData,$beforeData);
                $tableName[0]['table_name']='md_auth : 权限表';
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
                //=======================================
                $this->msg('修改成功','/actionAuth?id='.$this->parames['id'],'ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('修改失败','/actionAuth?id='.$this->parames['id'],'error');
            }
        }else{
            $data['ruis']=$parames['ruis'];
            if(M_Mysqli_Class('md_lixiang','AuthModel')->updateAuthByAttr($data)){

                //======================================操作内容记录
                $afterData=M_Mysqli_Class('md_lixiang','AuthModel')->getOneAuth(['id'=>$data['id']]);
                $insertData[0]=$this->arrayNewWornData($afterData,$beforeData);
                $tableName[0]['table_name']='md_auth : 权限表';
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
                //=========================================>
                $this->msg('修改成功','/actionAuth?id='.$this->parames['id'],'ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('修改失败','/actionAuth?id='.$this->parames['id'],'error');
            }
        }       
    }
    
    /**
     *  删除权限
     */
    public function delAuth(){
        $this->checkAuth();
           $data['pid']=$this->parames['id'];
           $arr=M_Mysqli_Class('md_lixiang','AuthModel')->getAuthInfonNoDel($data);
        if($arr[0]){
            $this->msg('请先删除此权限下的子权限','/authList','error');exit;
        }else{
            $afterData=M_Mysqli_Class('md_lixiang','AuthModel')->getOneAuth(['id'=>$this->parames['id']]);
            $update=M_Mysqli_Class('md_lixiang','AuthModel')->updateAuthByAttr($this->parames);
            if($update){
                $status=[0=>'启用',1=>'禁用',2=>'删除'];$plaform=[0=>'pc端后台',1=>'手机端后台',2=>'仓储后台'];
                $insertData[0]=['title'=>$afterData['title'],'platform'=>$plaform[$afterData['platform']], 'ruis'=>$afterData['ruis'],'status'=>$status[$this->parames['status']].','.$status[$afterData['status']]];
                $tableName[0]['table_name']='md_admin : 管理员表';
                $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
                $this->msg('删除成功','/authList','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
                $this->msg('删除失败','/authList','error');
            }  
        }      
    }


    /**
     * 查询权限所属平台
     */
    public function auth_platfrom(){
        $parames=$this->parames;

        $result=M_Mysqli_Class('md_lixiang','AuthModel')->getAuthInfoByAttr(['id'=>$parames['auth_id']]);

        if(array_key_exists('0',$result)){
                $outPut['status'] = "ok";
                $outPut['code'] = "200";
                $outPut['msg'] = "请求成功";
                $outPut['data'] = $result[0]['platform'];
        }else{
                $outPut['status'] = "error";
                $outPut['code'] = "400";
                $outPut['msg'] = "请求失败";
                $outPut['data'] = "";
        }
        $this->setOutPut($outPut);
    }








}