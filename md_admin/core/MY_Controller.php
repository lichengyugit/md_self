<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class MY_Controller extends CI_Controller {
    // public $userinfo = FALSE;
    // protected $langs;
    public function __construct() {
        parent::__construct();
        if(!$_SESSION['userName']){$_SESSION['userName']='';}
        $this->smarty->assign('session',$_SESSION['userName']);
        $this->smarty->assign('tree',$this->menus_auth_list());
    }

    /**
     * 通用变量定义
     * @return multitype:string
     */
    public function commonDefine(){
        return array('pagesize'=>'15',
            'baseUrl'=>BASE_URL,
            'resourceUrl'=>RESOURCE_URL
        );
    }
    /**
     * [_checkUserLogin 验证是否登录]
     * @param  [type] $user_id [description]
     * @param  [type] $token   [description]
     * @return [type]          [description]
     */
    public function _checkUserLogin($user_id,$token) {
        $row=M_Mysqli_Class('cro', 'UserTokenModel')->checkToken($user_id,$token);
        $time=time();
        if(count($row)>0){
            if($time>$row['over_time']){
                $outPut['status'] = 'error';
                $outPut['msg'] = '登录超时';
                $outPut['data'] = '';
                $this->setOutPut($outPut);
            }else{
                return;
            }
        }else{
            $outPut['status'] = 'error';
            $outPut['msg'] = '未登录';
            $outPut['data'] = '';
            $this->setOutPut($outPut);
        }
        
    }
    
    /**
     * [setOutPut 输出内容]
     * @param [json] $outPut [description]
     */
    public function setOutPut($outPut){
        echo json_encode($outPut);exit;
    }

    /**
     * [setArray 过滤数据]
     * @param [type] $keyArray [description]
     * @param [type] $data     [description]
     */
    public function setArray($keyArray,$data){
      if(count($keyArray)>0){
        foreach($keyArray as $k=>$v){
            $setData[$v]=$data[$v];
        }
        return $setData;
      }
        return $data;
    }
    
    public function getClientIP()
    {
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
    
    /**
     * 短信发送
     */
    public function sendSms($mobile,$code_type,$smsId){
        $code=rand(100000,999999);
        if($code_type==1){//登录
            $decribe = '验证码:'.$code.'，欢迎登录首席骑行官（为保证帐号安全，请勿向他人透露）';
        }elseif($code_type==2){//注册
            $decribe = '验证码:'.$code.'，欢迎注册首席骑行官（为保证帐号安全，请勿向他人透露）';
        }elseif($code_type==3){//找回密码
            $decribe = '验证码:'.$code.'，您正在找回密码，请确保本人使用（为保证帐号安全，请勿向他人透露）';
        }
        $this->load->library('Sms/ChuanglanSmsApi');
        $result = $this->chuanglansmsapi->sendSMS($mobile,$decribe);
        if(!is_null(json_decode($result))){
            $output = json_decode($result,true);
            if(isset($output['code'])  && $output['code']=='0'){
                $data['mobile']=$mobile;
                $data['code_type']=$code_type;
                $data['code']=$code;
                $time=time();
                $data['over_time']=$time+60*15;
                if($smsId==0){
                   $addSms=M_Mysqli_Class('cro', 'UserSmsModel')->addSms($data);
                }else{
                   $data['id']=$smsId;
                   $updSms=M_Mysqli_Class('cro', 'UserSmsModel')->updateSms($data);
                   unset($data['id']);
                }
                $data['send_time']=$time;
                $data['sms_context']=json_encode(array("send"=>$decribe,"return"=>$output));
                unset($data['over_time']);
                $addSmsLog=M_Mysqli_Class('cro', 'SmsLogModel')->addSmsLog($data);
                $outPut['status']="ok";
                $outPut['msg']="发送成功";
                $outPut['data']="";
            }else{
                $outPut['status']="error";
                $outPut['msg']="发送失败";
                $outPut['data']=$output['errorMsg'];
            }
        }else{
                $outPut['status']="error";
                $outPut['msg']="发送失败";
                $outPut['data']=$output['errorMsg'];
        }
        return $outPut;
    }
    
    /**
     * 生成订单号
     */
    public function createOrderNum($data,$head){
        if($head=="XO"){//下单
            $str=$this->getOxOrderLocationNum($data);
            return $head.date("YmdH",time()).$str.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }elseif($head=='CP'){//押金
           return $head.date("YmdH",time()).substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }elseif($head=='XT'){//充值
            return $head.date("YmdH",time()).substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }elseif($head=='MC'){//月卡
            return $head.date("YmdH",time()).substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }
    }
        
    /**
     * 生成仓储调拨单号
     * CP201810181686030857256554910
     */
    public function createStorageAllotNum(){
            //订购日期
            $order_date = date('Y-m-d');
            //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
            $order_id_main = date('YmdHis') . rand(10000000,99999999);
            //订单号码主体长度
            $order_id_len = strlen($order_id_main);
            $order_id_sum = 0;
            for($i=0; $i<$order_id_len; $i++){
                $order_id_sum += (int)(substr($order_id_main,$i,1));
            }
            //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
            $order_id = 'ST'.$order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
        return $order_id;
    }


    /**
     * 地区组成
     */
    private function getOxOrderLocationNum($data){
        $str="";
        if(count($data['province_id'])<=2){
            $str.="0".$data['province_id'];
        }else{
            $str.=$data['province_id'];
        }
        if(count($data['city_id'])<=3){
            $str.="0".$data['city_id'];
        }else{
            $str.=$data['city_id'];
        }
        if(count($data['district_id'])<=4){
            $str.="0".$data['district_id'];
        }else{
            $str.=$data['district_id'];
        }
        return $str;
    }

    /**
     * 处理http流参数
     */
    private function actionHttpStreaming($streaming){
        $arr=explode("&", $streaming);
        foreach($arr as $k=>$v){
            $keyArray=explode("=", $v);
            $return[$keyArray[0]]=$keyArray[1];
        }
        return $return;
    }
    
    /**
     * 根据请求处理参数
     * @return unknown
     */
    public function getParames(){
        $method=$_SERVER['REQUEST_METHOD'];
        if($method=="GET"){
            return $this->input->get();
        }else{
            return $this->input->input_stream();
//             $streaming = $this->input->input_stream();
//             //$uriArray=$this->actionHttpStreaming($streaming);
//             $this->load->library('common_rsa2');
//             $deSign = $this->common_rsa2->privateDecrypt($streaming['sign']); //验证签名
//             return $this->actionHttpStreaming($deSign);
        }
    }

    /**
     * [create_dir 创建文件夹]
     * @param  [type]  $dirName   [路径名]
     * @param  integer $recursive [description]
     * @param  integer $mode      [权限为777]
     * @return [type]             [description]
     */
    public function create_dir($dirName, $recursive = 1,$mode=0777) {
        ! is_dir ( $dirName ) && mkdir ( $dirName,$mode,$recursive );
    }

    /**
     * [_cut 截取字符串]
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @param  [type] $str   [description]
     * @return [type]        [description]
     */
     public function _cut($begin,$end,$str)
     {
        $b = mb_strpos($str,$begin) + mb_strlen($begin);
        $e = mb_strpos($str,$end) - $b;
        return mb_substr($str,$b,$e);
    }
    
    /**
     * 分页
     */
    public function page($url,$pagesize,$nums)
    {   
        $currentPage = $this->input->get('currentPage')?$this->input->get('currentPage'):$this->input->post('post_page');
        $pages=ceil($nums/$pagesize);
        if($currentPage>$pages){
            $currentPage=$pages;
        }
        if($currentPage < 1){
            $currentPage=1;
        }
        $beginNum = ($currentPage-1)*$pagesize;
        $limit="$beginNum,$pagesize";
        $pre = $currentPage-1;
        if($pre<1)
        {
            $pre=1;
        }
        $next = $currentPage +1;
        if($next>$pages)
        {
            $next=$pages;
        }

        $show = '<div class="col-xs-12">';
        $show .= '<div class="col-sm-6">';
        $show .= '<div class="dataTables_info" id="dataTables-example_info" style="color:#000;" role="alert" aria-live="polite" aria-relevant="all">';
        $show .= '总共'.$nums.'条数据';
        $show .= '</div>';
        $show .= '</div>';
        $show .= '<div class="col-sm-6">';
        $show .= '<div class="dataTables_paginate paging_simple_numbers" id="dataTables-example_paginate">';
        $show .= '<ul class="pagination">';
        if(strpos($url,'?')==true){
            $show .= '<li class="paginate_button previous disabled" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_previous"><a href="'.$url.'"&currentPage="'.$pre.'">Previous</a></li>';
        }
        else{
            $show .= '<li class="paginate_button previous disabled" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_previous"><a href="'.$url.'"?currentPage="'.$pre.'">Previous</a></li>';
        }
        for($i=1;$i<=$pages;$i++)
        {
            if($i>$currentPage+2 || $i<$currentPage-2)
                continue;
            if($i==$currentPage)
               $show.= ' <li class="paginate_button active" aria-controls="dataTables-example" tabindex="0"><a href="#">'.$i.'</a></li>';
            else{
                if(strpos($url,'?')==true){
                    $show.= '<li class="paginate_button" aria-controls="dataTables-example" tabindex="0"><a href="'.$url.'&currentPage='.$i.'">'.$i.'</a></li>';
                }else{
                    $show.= '<li class="paginate_button" aria-controls="dataTables-example" tabindex="0"><a href="'.$url.'?currentPage='.$i.'">'.$i.'</a></li>';
                }
            }
        }
        if(strpos($url,'?')==true){
           $show .= '<li class="paginate_button next" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_next"><a href="'.$url.'&currentPage='.$next.'">Next</a></li>';
        }
        else{
            $show .= '<li class="paginate_button next" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_next"><a href="'.$url.'?currentPage='.$next.'">Next</a></li>';
        }
        $show .= '</ul>';
        $show .= '</div>';
        $show .= '</div>';
        $show .= '</div>';
        $showpage['limit']=$limit;
        $showpage['show']=$show;
        return $showpage;
       }


       
    /**
     * ajax分页
     */
    public function newpage($url,$pagesize,$nums)
    {
        $currentPage = $this->input->get('currentPage')?$this->input->get('currentPage'):$this->input->post('post_page');
        $pages=ceil($nums/$pagesize);
        if($currentPage>$pages){
            $currentPage=$pages;
        }
        if($currentPage < 1){
            $currentPage=1;
        }
        $beginNum = ($currentPage-1)*$pagesize;
        $limit="$beginNum,$pagesize";
        $pre = $currentPage-1;
        if($pre<1)
        {
            $pre=1;
        }
        $next = $currentPage +1;
        if($next>$pages)
        {
            $next=$pages;
        }
        $show = '<div class="col-xs-12">';
        $show .= '<div class="col-sm-6">';
        $show .= '<div class="dataTables_info" id="dataTables-example_info" style="color:#000;" role="alert" aria-live="polite" aria-relevant="all">';
        $show .= '总共'.$nums.'条数据';
        $show .= '</div>';
        $show .= '</div>';
        $show .= '<div class="col-sm-6">';
        $show .= '<div class="dataTables_paginate paging_simple_numbers" id="dataTables-example_paginate">';
        $show .= '<ul class="pagination">';
        if(strpos($url,'?')==true){
            $show .= '<li class="paginate_button previous disabled" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_previous"><a onclick="'.$url.'('.$pre.')" >Previous</a></li>';
        }
        else{
            $show .= '<li class="paginate_button previous disabled" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_previous"><a onclick="'.$url.'('.$pre.')" >Previous</a></li>';
        }
        for($i=1;$i<=$pages;$i++)
        {
            if($i>$currentPage+2 || $i<$currentPage-2)
                continue;
            if($i==$currentPage)
               $show.= ' <li class="paginate_button active" aria-controls="dataTables-example" tabindex="0"><a href="#">'.$i.'</a></li>';
            else{
                if(strpos($url,'?')==true){
                    $show.= '<li class="paginate_button" aria-controls="dataTables-example" tabindex="0"><a onclick="'.$url.'('.$i.')" >'.$i.'</a></li>';
                }else{
                    $show.= '<li class="paginate_button" aria-controls="dataTables-example" tabindex="0"><a onclick="'.$url.'('.$i.')" >'.$i.'</a></li>';
                }
            }
               
        }
        if(strpos($url,'?')==true){
           $show .= '<li class="paginate_button next" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_next"><a onclick="'.$url.'('.$next.')" >Next</a></li>';
        }
        else{
            $show .= '<li class="paginate_button next" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_next"><a onclick="'.$url.'('.$next.')" >Next</a></li>';
        }
        $show .= '</ul>';                                                                                                           
        $show .= '</div>';
        $show .= '</div>';
        $show .= '</div>';
        $showpage['limit']=$limit;
        $showpage['show']=$show;
        return $showpage;
       }




       /**
        * 跳转方法
        */
       public function msg($msg,$url,$type,$jumpurl=""){
           $this->redirect('msgView?msg='.$msg.'&type='.$type.'&url='.$url);exit;
       }
       
       /**
        * 判断登录和用户权限
        */
       public function checkAuth(){
           if(!$this->session->userdata['mobile']){
               header('location:/login');
           }else{
               $str=explode('/',$this->curPageURL());
               $str1=explode('?', $str[3]);
               if(!in_array($str1[0], $this->session->userdata['authList'],true)){
                   $this->msg('无权访问', '/index', 'error');
               } 
           }       
       }
       
       /**
        * 获取网页地址
        */
      public  function curPageURL()
       {
           $pageURL = 'http';
            
           /* if ($_SERVER["HTTPS"] == "on")
           { */
               $pageURL .= "s";
           //}
           $pageURL .= "://";
            
           if ($_SERVER["SERVER_PORT"] != "80")
           {
               $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
           }
           else
           {
               $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
           }
           return $pageURL;
       }
       
       /**
        * 生成校验位
        */
       function getRandomString($len, $chars=null)
       {
           if (is_null($chars)) {
               $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
           }
           mt_srand(10000000*(double)microtime());
           for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
               $str .= $chars[mt_rand(0, $lc)];
           }
           return $str;
       }

       /**
        *  [getDes 获取检索信息] 
        *  @param [type] $[name] [<description>]
        */
       
       /**
        *  根据权限获取对应侧边栏内容
        */
/*       
       public function getLeftMenus($authList){
        if(is_array($authList)){
            foreach($authList as $k=>$v){
                $authIdList[]=$v['id'];
           }
        }
        $authIdList=implode(",", $authIdList);
        //去除最后一位逗号
        
        foreach($menusList as $k=>$v){
           if($v['parent_id']==0){
               $array[$k][]=$v;
               if($v['parent_id']==$v['id']){
                  $array[$k]['child'][]=$v;
               }
           }
        }
       }
*/


       /*
        * 生成用户邀请码
        */
       function make_invite_code() {
           $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
           $rand = $code[rand(0,25)]
           .strtoupper(dechex(date('m')))
           .date('d').substr(time(),-5)
           .substr(microtime(),2,5)
           .sprintf('%02d',rand(0,99));
           for(
               $a = md5( $rand, true ),
               $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
               $d = '',
               $f = 0;
           $f < 8;
           $g = ord( $a[ $f ] ),
           $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
           $f++
           );
           return $d;
       }

         function make_invite_codes() {
            $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
            for(
                $a = md5( $rand, true ),
                $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
                $d = '',
                $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & (0x1F+30) ],
            $f++
            );
            if(M_Mysqli_Class('md_lixiang','UserModel')->getUserByAttr(['invite_code'=>$d])){
                $this->make_invite_code();
            }
            return $d;
        }


       /**
        * 对接接口数据拼接
        */
       public function apiAndData($url,$data){
           $JSONData=array("JSONData"=>json_encode($data));
           $return=$this->request_post($url,$JSONData);
           return json_decode($return,true);
       }

       /**
        * curl_post
        */
       public function request_post($url = '', $param = '') {
           if (empty($url) || empty($param)) {
               return false;
           }
           $postUrl = $url;
           $curlPost = $param;
           $ch = curl_init();//初始化curl
           curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
           curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
           curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
           curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
           $data = curl_exec($ch);//运行curl
           curl_close($ch);
           return $data;
       }


       /*
        * 根据用户权限渲染列表
        */
       public function menus_auth_list(){
          if(!empty($_SESSION['authList'])){
            $session=$_SESSION['authList'];
            $menus=M_Mysqli_Class('md_lixiang','AuthMenusModel')->getAllAuthMenusOrder([]);
            if(!empty($menus)){
              $i=0;
              $arr=[];
              $parent_id=0;
              foreach ($menus as $key => $value) {
                  if(!empty($value['url']) && !in_array($value['url'], $session)){
                    unset($value);
                  }else{
                    if( str_replace('/','',$_SERVER['REQUEST_URI']) == $value['url']){
                        $value['status']=2;
                        $parent_id=$value['parent_id'];
                    }
                    $arr[$i]=$value;
                  }
                  $i++;
              }
              $arr=$this->menus_lock($arr,$parent_id);
              $arrange=$this->menus_arrange($arr,0);
              $thi=$this->menus_array($arrange);
              $thi=$this->menus_array($thi);
              $thi=$this->menus_array($thi);
              $thi=$this->menus_array($thi);
              $tree=$this->getLeftHtml($thi);
              return $tree;
            }
          }
       }



       private function menus_lock(&$parames,$parent_id){
            if($parent_id != 0){
                foreach ($parames as $k => &$v) {
                  if( $v['id'] == $parent_id ){
                    $v['status']=1;
                    $this->menus_lock($parames,$v['parent_id']);
                    return $parames;
                  }
                }
                return $parames;
            }else{
                return $parames;
            }
       }


       /*
        * 安排无权限内容
        */
       private function menus_array(&$parames){
          foreach ($parames as $key => &$value) {
              if(empty($value['son']) && empty($value['url'])){
                unset($parames[$key]);
              }elseif(!empty($value['son'])){
                $this->menus_array($value['son']);
              }
          }
          return $parames;
       }


       /*
        * 安排菜单数据
        */
      private function menus_arrange($data,$pId)
      {
        $tree = '';
        foreach($data as $k => $v)
        {
          if($v['parent_id'] == $pId)
          {        //父亲找到儿子
           $v['son'] = $this->menus_arrange($data,$v['id']);
           $tree[] = $v;
          }
        }
        return $tree;
      }


      /*
       *  安排菜单页面
       */
      private function getLeftHtml($tree)
      {
        $html = '';
        foreach($tree as $t)
        {
          $c=$t['parent_id']==0?'':'';
          if($t['son'] == '')
          {
            if($t['status']==2){
                $html .= "<li class='thirdli'><a href='/{$t['url']}'><i class='{$t['css']}'></i>{$t['name']}<i class='fa fa-angle-right'></i></a></li>";
            }else{
                $html .= "<li class='thirdli'><a href='/{$t['url']}'><i class='{$t['css']}'></i>{$t['name']}</a></li>";
            }
          }
          else
          {
            if($t['status']==1){
                $html .= "<li class='active ' ><a href='' ><i class='{$t['css']}'></i>{$t['name']}</a><ul class='nav nav-second-level ".$c."'>";
                $html .= $this->getLeftHtml($t['son']);
                $html = $html."</ul></li>";
            }else{
                $html .= "<li><a href=''><i class='{$t['css']}'></i>{$t['name']}</a><ul class='nav nav-second-level ".$c."'>";
                $html .= $this->getLeftHtml($t['son']);
                $html = $html."</ul></li>";
            }
          }
        }
        return $html ? '<ul  class="nav nav-second-level" id="main-menu" class="active-menu">'.$html.'</ul>' : $html ;
      }

      



    //获取memache对象
    public function getMemObj()
    {
        $mem = new Memcache();
        $mem->addserver('127.0.0.1',11211);
        return $mem;
    }

    //获取memache缓存
    public function getMemcache($key){
        $session=$this->getMemObj()->get($key);
        return $session;
    }

    //设置memache缓存
    public function setMemcache($key,$value,$flag=null,$expire=null){
        $session=$this->getMemObj()->set($key,$value,$flag,$expire);
        return $session;
    }


    /**
     * 搜索url拼接
     */
    public function makeSearchUrl($keyArray){
        $searchUrl='&';
        if(is_array($keyArray)){
            foreach ($keyArray as $k=>$v){
               $searchUrl.=$k."=".$v."&";
            }
        }
        return $searchUrl;
    }

    /*
     * 写入后台日志
     * */
    public function writeBackstageLog($parames,$insertData='',$tableName='')
    {
        $str=explode('/',$this->curPageURL());
        $str1=explode('?', $str[3]);
        $adminRole=M_Mysqli_Class('md_lixiang','AdminRoleModel')->getAdminRoleByAttr(['admin_id'=>$this->session->userdata['user_id']]);
        $role=M_Mysqli_Class('md_lixiang','RoleModel')->getOneRoleByAttr(['id'=>$adminRole['role_id']]);
        $auth=M_Mysqli_Class('md_lixiang','AuthModel')->getOneAuth(['ruis'=>"'".$str1[0]."'"]);
        $data=[
            'user_name'=>$this->session->userdata['userName'],                               //管理员名称
            'user_mobile'=>$this->session->userdata['mobile'],                               //管理员账户
            'user_flag'=>$role['name'],                                                      //管理员身份
            'operation_type'=>$parames['operation_type'],                                    //操作类型
            'operation_state'=>$parames['operation_state'],                                  //操作状态
            'url'=>$str1[0],                                                                 //操作路由
            'operation_menus'=>$auth['title'],                                               //操作栏目
            'operation_ip'=>$this->getClientIP(),                                            //操作人员ip
            'status'=>0
        ];
        $logReturnId=M_Mysqli_Class('md_lixiang','BackstageLogModel')->addLog($data);
        if($insertData){
            //处理操作详情内容
            switch ($parames['type']){
                case 'add' :
                    $y=0;
                    for ($i=0;$i<count($insertData);$i++){
                        foreach ($insertData[$i] as $k=>$v){
                            $addData[$y]=[
                                'back_id'    =>$logReturnId,
                                'table_name' =>$tableName[$i]['table_name'],
                                'clm_name'   =>$k,
                                'old_string' =>'',
                                'new_string' =>$v,
                                'create_time'=>time(),
                                'create_date'=>date('Y-m-d H:i:s',time()),
                            ];
                            $y++;
                        }
                    }
                    break;
                case 'edit':
                    $y=0;
                    foreach ($insertData as $key=>$val){
                        foreach ($val as $key2=>$val2){
                            $addData[$y]=[
                                'back_id'    =>$logReturnId,
                                'table_name' =>$tableName[$key]['table_name'],
                                'clm_name'   =>$val2['clm_name'],
                                'old_string' =>$val2['old_string'],
                                'new_string' =>$val2['new_string'],
                                'create_time'=>time(),
                                'create_date'=>date('Y-m-d H:i:s',time()),
                            ];
                            $y++;
                        }

                    }
                    break;
                case 'del':
                    $y=0;
                    for($i=0; $i<count($insertData); $i++){
                        foreach( $insertData[$i] as $key=>$val){
                            if($key=='status'){
                                $status=explode(',',$val);
                            }
                            $addData[$y]=[
                                'back_id'    =>$logReturnId,
                                'table_name' =>$tableName[$i]['table_name'],
                                'clm_name'   =>$key,
                                'old_string' =>isset($status)?$status[1]:$val,
                                'new_string' =>isset($status)?$status[0]:'',
                                'create_time'=>time(),
                                'create_date'=>date('Y-m-d H:i:s',time()),
                            ];
                            $y++;
                        }

                    }
                    break;
                default :
                    return true;

            }
            if(!empty($parames['type'])){
                M_Mysqli_Class('md_lixiang','BackstageContentModel')->bashSaveBackContent($addData);
            }
        }




    }


    /*
     * 处理新旧值差异
     * */
    public function arrayNewWornData($newData,$wornData)
    {
        $returnRes=array_diff_assoc($newData,$wornData);
        if($returnRes){
            $y=0;
            foreach ($returnRes as $key=>$val){
                $addData[$y]=[
                    'clm_name'=>$key,
                    'old_string'=>$wornData[$key],
                    'new_string'=>$val,
                ];
                $y++;
            }
            return $addData;die;
        }else{
            return '';
        }
    }


     function deep_in_array($clm,$value,$array)
    {
           foreach ($array as $key=>$val){
               $newArrar[]=$val[$clm];
           }
           if(in_array($value,$newArrar)){
               return true;
           }else{
               return false;
           }
    }
}




