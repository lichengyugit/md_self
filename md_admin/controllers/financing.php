<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class financing extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "financing");
    }

    /**
     * 充值列表
     */
    public function topUpList()
    {   $this->checkAuth();
        F()->Resource_module->setTitle('充值列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/topUpList";
        $data[2]=2;
       $nums=M_Mysqli_Class('md_lixiang','TopUpModel')->getTopUpByAttr($data);
       $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
       $arr=M_Mysqli_Class('md_lixiang','TopUpModel')->getAllTopUpByAttr($showpage['limit'],$data);
       //print_r($arr);exit;
       $this->smarty->assign('arr',$arr);
       $this->smarty->assign("pages", $showpage['show']);
       $this->smarty->view('user/userPayList.phtml');
    }
    

    
}