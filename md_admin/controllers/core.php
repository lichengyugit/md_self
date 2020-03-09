<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class Core extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "core");
    }

    /**
     * 页面跳转
     */
    public function msgView()
    {
        //$this->msg('添加成功','companyList','ok');exit;
        $this->smarty->assign('msg',$this->parames['msg']);
        $this->smarty->assign('url',$this->parames['url']);
        if($this->parames['type']=='ok'){
            F()->Resource_module->setTitle('成功');
            $this->smarty->view('common/message_ok.phtml');
        }else{
            F()->Resource_module->setTitle('失败');
            $this->smarty->view('common/message_error.phtml');
        }
    }
    
}