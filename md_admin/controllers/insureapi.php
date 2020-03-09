<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class insureapi extends MY_Controller {


    public function __construct() {
        parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        $this->load->library('Common_restful');
        $this->url=INSURE_HOST;
    }

    /**
     * gett方法 
     * 
     */
    public function actionGet()
    {
        $parames=$this->parames;
        $url=$this->url."/".$parames['new'].'?action='.$parames['action'];
        switch ($parames['action'])
        {
             case "actionGetInsure"://获取人身意外险和第三方责任险
                $outPut=$this->common_restful->sendGet($url,$parames);
                break;
             case "actionGetBrand":
                $outPut=$this->common_restful->sendGet($url,'');
                break;
             case "actionSendSms":
             
                unset($parames['action']);
                foreach($parames as $k=>$v){
                     $url.="&".$k."=".$v;
                }
                $outPut=$this->common_restful->sendGet($url,$parames);
                // print_r($outPut);
                break;
            case "actionGetRebate":
             
                unset($parames['action']);
                foreach($parames as $k=>$v){
                     $url.="&".$k."=".$v;
                }
                $outPut=$this->common_restful->sendGet($url,$parames);
                // print_r($outPut);
                break;
            case "actionGetOrder":
                unset($parames['action']);
                foreach($parames as $k=>$v){
                     $url.="&".$k."=".$v;
                }
                $outPut=$this->common_restful->sendGet($url,$parames);
                // print_r($outPut);
                break;
            default:
                $outPut['status']="error";
                $outPut['code']="4040";
                $outPut['msg']="请求错误";
                $outPut['data']="";
        }
        $this->setOutPut($outPut);
    }

    public function actionPost(){
         
        $parames=$this->parames;
        // var_dump($parames);exit;
        switch ($parames['action'])
        {
            case "actionInsureOrder"://保险信息完善
                $url = $this->url."/insure";
                $outPut=$this->common_restful->sendPost($url,$parames);
                break;
            case "actionSendSms":
                $outPut=$this->common_restful->sendPost($this->url,$parames);
                //print_r($outPut);
                break;
            case "actionGetRebate"://返利
                $url = $this->url."/insure";
                //var_dump($parames);exit;
                // echo $url;
                // exit;
                $outPut=$this->common_restful->sendPost($url,$parames);
            break;
            default:
                $outPut['status']="error";
                $outPut['code']="4040";
                $outPut['msg']="请求错误";
                $outPut['data']="";
        }
        $this->setOutPut($outPut);
    }
    
    public function actionPut(){
        $parames=$this->parames;
        switch ($parames['action'])
        {
            case "actionInsureOrder"://保险信息完善
                $outPut=$this->common_restful->sendPut($this->url,$parames);
                break;
            default:
                $outPut['status']="error";
                $outPut['code']="4040";
                $outPut['msg']="请求错误";
                $outPut['data']="";
        }
        $this->setOutPut($outPut);
    }
    
}
?>
