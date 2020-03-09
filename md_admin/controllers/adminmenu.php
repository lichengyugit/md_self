<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class adminMenu extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "adminMenu");
    }


    /**
     * 后台栏目菜单
     */
    public function adminMenu()
    {   
        F()->Resource_module->setTitle('后台栏目菜单');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $menus=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getAllAuthMenusOrder([]);
        foreach ($menus as $key => $value) {
          $menus[$key]['pid']=$menus[$key]['parent_id'];
        }
        $str="<tr  class='odd gradeX'>
            <td>\$id</td>
            <td>\$spacer\$name</td>
            <td>\$url</td>
            <td><i class='\$css'></i>&nbsp</td>
            <td>\$css</td>
            <td>\$status</td>
            <td>\$sort</td>
            <td class='btn-default btn-xs' style='background-color:#F5F5F5;'><a href='addSonAdminMenu?id=\$id'>添加子栏目</a></td>
            <td class='btn-default btn-xs' style='background-color:#F5F5F5;'><a href='updateAdminMenu?update_id=\$id'>修改</a></td>
            <td class='btn-default btn-xs' style='background-color:#F5F5F5;'><a href='delMenu?id=\$id&status=2'>删除</a></td>
            </tr>";
        //转换数据
        $tree_data=array();
        foreach ($menus as $key=>$value){
            $tree_data[$value['id']]=array(
                'sort'=>$value['sort'],
                'status'=>$value['status'],
                'url'=>$value['url'],
                'css'=>$value['css'],
                'id'=>$value['id'],
                'parentid'=>$value['pid'],
                'name'=>$value['name']
            );
        }
        $tree=F()->Tree_module;
        $tree->init($tree_data);
        $html="<table class='table table-striped table-bordered table-hover' id='dataTables-example'>";
        $html.="<thead>
                    <tr>
                        <th>id</th>
                        <th>栏目名称</th>
                        <th>栏目路径</th>
                        <th>栏目样式</th>
                        <th>样式名称</th>
                        <th>栏目状态</th>
                        <th>排序</th>
                        <th colspan=3>操作</th>
                    </tr>
                </thead>";
        $html.=$tree->get_tree(0, $str);
        $html.="</table>";
        $this->smarty->assign('adminList',$html);
        $this->smarty->view('adminMenu/list.phtml');
    }


    /**
     * 添加子栏目
     */
    public function addSonAdminMenu(){
      F()->Resource_module->setTitle('添加栏目');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $parames=$this->parames;
      if(array_key_exists('name',$parames)){
        $result=M_Mysqli_Class('md_lixiang','AuthMenusModel')->saveMenu($parames);
        if($result['sort']==''){
          unset($result['sort']);
        }
        if($result){
            $tableName[0]['table_name']='md_auth_menus';
            $insertData=[0=>$parames];
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            $this->msg('添加成功','/adminMenu','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->msg('添加失败','/addSonAdminMenu','error');
        } 
      }else{
        $css=$this->CssStyle();
        $this->smarty->assign('css',$css);
        $this->smarty->assign('id',$parames['id']);
        $this->smarty->view('adminMenu/insert.phtml');
      }
    }

    /**
     * 修改子栏目
     */
    public function updateAdminMenu(){
      F()->Resource_module->setTitle('修改栏目');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $parames=$this->parames;
      if(array_key_exists('update_id',$parames)){
        $result=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getAllAuthMenus(['id'=>$parames['update_id']]);
        $css=$this->CssStyle();
        $this->smarty->assign('css',$css);
        $this->smarty->assign('result',$result[0]);
        $this->smarty->view('adminMenu/update.phtml');
      }else{
          $beforeData=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getOneAuthMenus(['id'=>$parames['id']]);
        $result=M_Mysqli_Class('md_lixiang','AuthMenusModel')->updateMenuByAttr($parames);
        if($result){
            $afterData=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getOneAuthMenus(['id'=>$parames['id']]);
            $adminMenusRes=$this->arrayNewWornData($afterData,$beforeData); $tableName[0]['table_name']='md_auth_menus : 栏目表';
            if($adminMenusRes) $insertData[0]=$adminMenusRes;
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
            $this->msg('修改成功','/adminMenu','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/updateAdminMenu?update_id='.$parames['update_id'],'error');
        }
      }
    }
    
    /**
     *  删除权限
     */
    public function delMenu(){
           $data['parent_id']=$this->parames['id'];
           $arr=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getAllAuthMenus($data);
        if(isset($arr[0]) && $arr[0]!=''){
            $this->msg('请先删除此栏目下的子栏目','/adminMenu','error');exit;
        }else{
            $beforeData=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getOneAuthMenus(['id'=>$this->parames['id']]);
            $update=M_Mysqli_Class('md_lixiang','AuthMenusModel')->updateMenuByAttr($this->parames);
            if($update){
                $status=[0=>'启用',1=>'禁用',2=>'删除'];
                $insertData[0]=['name'=>$beforeData['name'], 'url'=>$beforeData['url'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
                $tableName[0]['table_name']='md_auth_menus : 栏目表';
                $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
                $this->msg('删除成功','/adminMenu','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
                $this->msg('删除失败','/adminMenu','error');
            }  
        }      
    }

    /**
     * 样式大全
     */
    public function CssStyle(){
      $css=[
        'fa fa-table',
        'fa fa-plus-square-o',
        'fa fa-group',
        'fa fa-sitemap',
        'fa fa-folder-open',
        'fa fa-sticky-note',
        'fa fa-building-o',
        'fa fa-file-powerpoint-o',
        'fa fa-desktop',
        'fa fa-area-chart',
        'fa fa-credit-card-alt',
        'fa fa-credit-card',
        'fa fa-edit',
        'fa fa-university',
        'fa fa-male',
      ];
      return $css;
    }





}


?>
