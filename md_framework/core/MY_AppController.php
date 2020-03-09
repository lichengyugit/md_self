<?php
/* 修车公共方法
 * lcy
 */
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class MY_AppController extends CI_Controller {

    public function __construct() {
        parent::__construct();

        //$this->load->library('session');
    }

    /**
     * [_checkUserLogin 验证是否登录]
     * @param  [type] $user_id [description]
     * @param  [type] $token   [description]
     * @return [type]          [description]
     */
    public function _checkUserLogin($user_id,$token) {
        $row=M_Mysqli_Class('md_lixiang', 'UserTokenModel')->checkToken($user_id,$token);
        $time=time();
        if(count($row)>0){
            if($time>$row['over_time']){
                $outPut['status'] = 'error';
                $outPut['code'] = '0112';
                $outPut['msg'] = '登录超时';
                $outPut['data'] = '';
                $this->setOutPut($outPut);
            }else{
                return;
            }
        }else{
            $outPut['status'] = 'error';
            $outPut['code'] = '0110';
            $outPut['msg'] = '未登录';
            $outPut['data'] = '';
            $this->setOutPut($outPut);
        }

    }
    /**
     * [_checkUserIdentification 验证是否认证]
     * @param  [type] $user_id [description]
     */
    public function _checkUserIdentification($userId,$type=1){
        $data['id']=$userId;
        $row=M_Mysqli_Class('md_lixiang', 'UserModel')->getUserInfoByAttr($data);
        if($row['mobile']==NULL){
            $outPut['status'] = 'error';
            $outPut['code'] = '1010';
            $outPut['msg'] = '还未认证手机号';
            $outPut['data']['breakUrl'] = '认证手机号.html';
            $this->setOutPut($outPut);
        }elseif($row['mobile']!=NULL && $row['identification']==0){
            $outPut['status'] = 'error';
            $outPut['code'] = '1014';
            $outPut['msg'] = '还未实名认证';
            $outPut['data']['breakUrl'] = '实名认证.html';
            $this->setOutPut($outPut);
        }elseif($row['mobile']!=NULL && $row['identification']==1){
            if($type==2){
                return;
            }elseif($type==1){
                if($row['user_type']==1){
                    $companyConfig=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getCompanyConfigInfoByAttr(['company_id'=>$row['attr_id']]);
                }
                if($row['user_type']==0 || $companyConfig['how_battery']==0){
                    if($arr=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getOrder($userId)){
                        if($arr['pay_status']==0){
                            $outPut['status'] = 'error';
                            $outPut['code'] = '1011';
                            $outPut['msg'] = '还未交押金';
                            $outPut['data']['breakUrl'] = '交押金.html';
                            $this->setOutPut($outPut);
                        }else{
                            if($arr['pledge_money_status']>0){
                                $outPut['status'] = 'error';
                                $outPut['code'] = '1019';
                                $outPut['msg'] = '无法进行换电(原因:您已申请退押金)';
                                $outPut['data'] = '';
                                $this->setOutPut($outPut);
                            }
                            if(M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttr(['user_id'=>$userId])){
                                return ;
                            }else{
                                $outPut['status'] = 'error';
                                $outPut['code'] = '1012';
                                $outPut['msg'] = '还未绑定电池';
                                $outPut['data']['breakUrl'] = '绑定电池.html';
                                $this->setOutPut($outPut);
                            }
                        }
                    }else{
                        $outPut['status'] = 'error';
                        $outPut['code'] = '1011';
                        $outPut['msg'] = '还未交押金';
                        $outPut['data']['breakUrl'] = '交押金.html';
                        $deposit=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getInfoByAttr(['user_id'=>$userId,'status'=>0,'pledge_status'=>1]);
                        if(count($deposit)){
                            $outPut['code'] = '1019';
                            $outPut['msg'] = '无法进行换电(原因:您已申请退押金)';
                            $outPut['data'] = '';
                        }
                        $this->setOutPut($outPut);
                    }
                }else{
                    if(M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttr(['user_id'=>$userId])){
                        if($arr=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getOrder($userId)){
                            if($arr['pay_status']==0){
                                $outPut['status'] = 'error';
                                $outPut['code'] = '1011';
                                $outPut['msg'] = '还未交押金';
                                $outPut['data']['breakUrl'] = '交押金.html';
                                $this->setOutPut($outPut);
                            }
                        }else{
                            $outPut['status'] = 'error';
                            $outPut['code'] = '1011';
                            $outPut['msg'] = '还未交押金';
                            $outPut['data']['breakUrl'] = '交押金.html';
                            $this->setOutPut($outPut);
                        }
                    }else{
                        $outPut['status'] = 'error';
                        $outPut['code'] = '1012';
                        $outPut['msg'] = '还未绑定电池';
                        $outPut['data']['breakUrl'] = '绑定电池.html';
                        $this->setOutPut($outPut);
                    }
                }

            }
        }else{
            $outPut['status'] = 'error';
            $outPut['code'] = '1009';
            $outPut['msg'] = '参数错误';
            $outPut['data'] = '';
            $this->setOutPut($outPut);
        }
        return ;
    }

    /**
     * [_checkUserSession 验证是否登录]
     * @param  [type] $user_id [description]
     * @param  [type] $token   [description]
     * @return [type]          [description]
     */
    public function _checkUserSession($sessionId) {
        /* $mem = new Memcache();
        $mem->addserver('127.0.0.1',11211);
        $session=$mem->get($sessionId); */
        $session=$this->getMemcache($sessionId);
        if($session['userId']==NULL){
            $outPut['status'] = 'error';
            $outPut['code'] = '0112';
            $outPut['msg'] = '登录超时';
            $outPut['data']['breakUrl'] = '登录接口';
            $outPut['data']['key']=$sessionId;
            $outPut['data']['session']=$session;
            $this->setOutPut($outPut);
        }else{
            return $session;
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
     * [setOutPut 输出加密内容]
     * @param [json] $outPut [description]
     */
    public function setOutPutToml($outPut){
        $this->load->library('common_rsa2');
        $res = $this->common_rsa2->privateEncrypt($outPut);
        echo $res;die;
        echo json_encode($outPut);exit;
    }
    public function setJsonpOutPut($outPut){
        echo "success_jsonpCallback(".json_encode($outPut).");";exit;
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
        /*global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";*/
        $ip=$_SERVER['REMOTE_ADDR'];
        return $ip;
    }

    /**
     * 短信发送
     */
    public function sendSms($mobile,$code_type,$smsId){
        $code=rand(100000,999999);
        if($code_type==1){//登录
            $decribe = '验证码:'.$code.'，欢迎登录修铺（为保证帐号安全，请勿向他人透露）';
        }elseif($code_type==2){//注册
            $decribe = '验证码:'.$code.'，欢迎注册修铺（为保证帐号安全，请勿向他人透露）';
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
        if($head=="XU"){//修车
            //$str=$this->getOxOrderLocationNum($data);
            //return $head.date("YmdH",time()).$str.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            return $head.date("YmdH",time()).$this->randomStr();//新的生成订单规则
        }elseif($head=='BY'){//购车订单号
            //$str=$this->getOxOrderLocationNum($data);
            //$str=rand(100000000, 999999999);
            //return $head.date("YmdH",time()).$str.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            return $head.date("YmdH",time()).$this->randomStr();//新的生成订单规则
        }elseif($head=='RT') {//租车订单号
            //$str=rand(100000000, 999999999);
            //return $head.date("YmdH",time()).$str.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            return $head . date("YmdH", time()) . $this->randomStr();//新的生成订单规则
        }elseif($head='OT'){
            return $head . date("YmdH", time()) . $this->randomStr();//新的生成订单规则
        }
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
            if(ISSIGN==FALSE){
                return $this->input->input_stream();
            }else{
                $streaming = $this->input->input_stream();
                //$uriArray=$this->actionHttpStreaming($streaming);
                $this->load->library('common_rsa2');
                $deSign = $this->common_rsa2->privateDecrypt($streaming['sign']); //验证签名
                //              print_r($streaming);
                //              print_r($this->actionHttpStreaming(urldecode($deSign)));exit;
                return $this->actionHttpStreaming(urldecode($deSign));
            }
        }

    }
    public function getParamesToml(){
        $streaming = $this->input->input_stream();
        print_r($streaming);die;
        $this->load->library('common_rsa2');

        var_dump($streaming['sign']);die;
        $deSign = $this->common_rsa2->privateDecrypt($streaming['sign']);
        $this->actionHttpStreaming(urldecode($deSign));
        print_r($this->input->get());die;
        $method=$_SERVER['REQUEST_METHOD'];
        if($method=="GET"){
            return $this->input->get();
        }else{
            if(ISSIGN==FALSE){
                return $this->input->input_stream();
            }else{
                $streaming = $this->input->input_stream();
                //$uriArray=$this->actionHttpStreaming($streaming);
                $this->load->library('common_rsa2');
                $deSign = $this->common_rsa2->privateDecrypt($streaming['sign']); //验证签名
                //              print_r($streaming);
                //              print_r($this->actionHttpStreaming(urldecode($deSign)));exit;
                return $this->actionHttpStreaming(urldecode($deSign));
            }
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
     * 生成二维码
     */
    public function makeQrCode($data){
        require_once DEFAULT_SYSTEM_PATH.'/libraries/Phpqrcode/phpqrcode.php';

        $value = $data['action']."&".$data['cabinetNumber']."&".$data['salt'];                  //二维码内容

        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 5;           //生成图片大小

        //生成二维码图片
        $filename = QRCODE_PATH.'/qrcode/'.$data['cabinetNumber'].'.png';
        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);

        $QR = $filename;                //已经生成的原始二维码图片文件

        $QR = imagecreatefromstring(file_get_contents($QR));

        //输出图片
//         imagepng($QR, 'qrcode.png');
//         imagedestroy($QR);
        //$arr=explode('/', $filename);
        //$str=$arr[4].'/'.$arr[5];
        return $filename;
    }

    /** * @desc 根据两点间的经纬度计算距离 *
    @param float $lat 纬度值 *
    @param float $lng 经度值
     */
    public function getDistance($lng1,$lat1,$lng2,$lat2)
    {
        $earthRadius = 6367000;
        //approximate radius of earth in meters  /* Convert these degrees to radians to work with the formula */
        $lat1 = ($lat1 * pi() ) / 180; $lng1 = ($lng1 * pi() ) / 180;
        $lat2 = ($lat2 * pi() ) / 180; $lng2 = ($lng2 * pi() ) / 180;
        /* Using the Haversine formula  http://en.wikipedia.org/wiki/Haversine_formula  calculate the distance */
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }
    /**
     *根据经纬度生成订单
     */
    public function createOrder($parames,$head){
        $location=F()->Gaode_module->locationChange($parames['location']);
        $address=F()->Gaode_module->getAddress($location['locations']);
        $addressArray['province']=$address['regeocode']['addressComponent']['province'];
        if($address['regeocode']['addressComponent']['city']){
            $addressArray['city']=$address['regeocode']['addressComponent']['city'];
        }else{
            $addressArray['city']=$address['regeocode']['addressComponent']['province'];
        }
        $addressArray['district']=$address['regeocode']['addressComponent']['district'];
        //查询省id
        $data['province_id']=M_Mysqli_Class('md_lixiang','ProvinceModel')->getProvinceId($addressArray['province']);
        //查询市id
        $data['city_id']=M_Mysqli_Class('md_lixiang','CityModel')->getCityId($addressArray['city'],$data['province_id']);
        //查询区id
        $data['district_id']=M_Mysqli_Class('md_lixiang','DistrictModel')->getDistrictId($addressArray['district'],$data['city_id']);
        return $orderNum=$this->createOrderNum($data,$head);
    }

    /**
     * 根据随机值
     */
    function generate_salt( $length = 8 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $salt = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $salt .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $salt;
    }

    public function getMemcache($key){
        $mem = new Memcache();
        $mem->addserver(MEMCACHE_IP,MEMCACHE_PORT);
        $session=$mem->get($key);
        $toArr=json_decode($session,true);
        if($toArr){
            $session=$toArr;
        }
        $mem->close();
        return $session;
    }

    public function getCabinetAllowList(){ //用户类型可用机柜类型 key为用户类型
        $array=array(
            "0"=>array("1"),
            "1"=>array("1","2"),
            "2"=>array("3"),
        );
        return $array;
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

    /**
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
     * 调机柜开门
     */
    public function callCabinetOpenDoor($parames){
        $url=ML_URL."df57ab54";
        $data=[
            'wp01'=>date("Y-m-d H:i:s",time()),
            'wp02'=>$parames['cabinet_id'],
            'wp03'=>$parames['user_id'],
            'wp04'=>$parames['pay'],
            'wp05'=>PIC_URL.'/qrcode/'.$parames['cabinet_id'].'.png',
            'wp06'=>$parames['location'],
            'wp07'=>$parames['order_sn'],
            'wp08'=>$parames['token'],
            'wp09'=>$parames['mdToken'],
            'wp10'=>$parames['status'],
            'wp11'=>$parames['scanTime']
        ];
        $JSONData=array("JSONData"=>json_encode($data));
        $return=$this->request_post($url,$JSONData);
        get_log()->log_api('<接口测试> ####  作用：A调用魔力后台支付通知参数：'.$JSONData['JSONData']);
        get_log()->log_api('<接口测试> ####  作用：A调用魔力后台支付通知接口后获取返回值：'.$return);
    }

    /**
     * @param id 用户Id
     * @param cId 集团id
     * 集团用户生成押金订单和底托订单
     * @return
     */
    public function companyUserOrder($id,$cId){
        $companyConfig=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getCompanyConfigInfoByAttr(['company_id'=>$cId]);
        if($companyConfig['is_bottom']==0 && $companyConfig['is_pledge']==0){
            $time=time();
            $insert=[
                'pledge_money'=>0,
                'order_sn'=>$this->createOrderNum("",'CP'),
                'user_id'=>$id,
                'create_time'=>$time,
                'create_date'=>date("Y-m-d H:i:s",$time),
                'pay_time'=>$time,
                'pay_date'=>date("Y-m-d H:i:s",$time),
                'pay_status'=>1
            ];
            M_Mysqli_Class('md_lixiang','PledgeOrderModel')->addOrder($insert);
            M_Mysqli_Class('md_lixiang','UserModel')->updateUser(['id'=>$id,'is_deposit'=>1]);//更改user表押金状态
            return 1;
        }elseif($companyConfig['is_bottom']==1 && $companyConfig['is_pledge']==0){
            return false;
        }
        return 2;
        /* if($companyConfig['is_bottom']==1){
            $insert1=[
                'user_id'=>$id,
                'money'=>$companyConfig['bottom_cost']*100,
                'order_sn'=>$this->createOrderNum("", 'BT')
            ];
            M_Mysqli_Class('md_lixiang','UserBottomModel')->insertUserBottom($insert1);
        } */
    }

    /**
     * 生成订单随机字符串
     */
    public  function randomStr(){
        $len =7;
        $chars='0123456789';
        $string=time();
        for(;$len>=1;$len--)
        {
            $position=rand()%strlen($chars);
            $position2=rand()%strlen($string);
            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        return $string;
    }

    /**
     * array_column
     * @param array 二维数组
     * @param key   取出的key
     */
    public function myArray_column($array,$key){
        $return=[];
        foreach ($array as $k=>$v){
            $return[]=$v[$key];
        }
        return $return;
    }

    /**
     * @param key 键名
     * @param var 键值
     * @param expire 存储时间
     * @param flag
     * @return bool
     */
    public function setMemcache($key,$var,$expire=3600,$flag=null){
        $mem = new Memcache();
        $mem->addserver(MEMCACHE_IP,MEMCACHE_PORT);
        if(is_array($var)){
            $return=$mem->set($key, json_encode($var), $flag, $expire);
        }else{
            $return=$mem->set($key, $var, $flag, $expire);
        }
        $mem->close();
        return $return;
    }

    /**
     * 获取城市id
     * @param $ln 经度
     * @param $la 纬度
     * @return int
     */
    public function getCityId($ln,$la){
        $city=F()->Gaode_module->getAddress($ln.','.$la,'base');
        if($city['status']==0){
            $outPut['status']="error";
            $outPut['code']="1536";
            $outPut['msg']="保存失败";
            $outPut['data']="";
            $this->setOutPut($outPut);exit;
        }
        $provinceId=M_Mysqli_Class('md_lixiang','ProvinceModel')->getProvinceId($city['regeocode']['addressComponent']['province']);
        if($city['regeocode']['addressComponent']['city']){
            $cityId=M_Mysqli_Class('md_lixiang','CityModel')->getCityId($city['regeocode']['addressComponent']['city'],$provinceId);
        }else{
            $cityId=M_Mysqli_Class('md_lixiang','CityModel')->getCityId($city['regeocode']['addressComponent']['province'],$provinceId);
        }
        if(!$cityId){
            $cityId=73;//如果查询不到定位的城市,默认上海市
        }
        return $cityId;
    }

    /*
     * 查询用户是否存在数据库
     * @parames    data[
     *                  open_id            用户open_id
     *                  user_flag          用户身份
     *                  user_from          用户来源
     *                  ]
     * */
    public function verifyUserInfo($data)
    {
        $userAttrInfoData=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['identification'=>0,'open_id'=>$data['open_id'],'status'=>0,'user_flag'=>9]);
        if(count($userAttrInfoData)){
            $status=5010;
            return $status;die;
        }
        $whereData=[
            'identification'=>1,
            'open_id'=>$data['open_id'],
            'status'=>0
        ];
        $userFlag=[];
        $userAllInfoData=M_Mysqli_Class('md_lixiang','UserModel')->getUsersInfoByAttr($whereData);
        if(count($userAllInfoData)){
            foreach ($userAllInfoData as $k=>$v){
                $userFlag[$k]=$v['user_flag'];
            }
        }
        if(in_array(9,$userFlag)){
            return 1000;
        }else{
            $addData=[
                'user_name'=>$userAllInfoData[0]['user_name'],
                'user_flag'=>$data['user_flag'],
                'nick_name'=>$userAllInfoData[0]['nick_name'],
                'avatar'=>$userAllInfoData[0]['avatar'],
                'ali_id'=>isset($userAllInfoData[0]['avatar'])?$userAllInfoData[0]['avatar']:'',
                'ali_nick_name'=>isset($userAllInfoData[0]['ali_nick_name'])?$userAllInfoData[0]['ali_nick_name']:'',
                'ali_avatar'=>isset($userAllInfoData[0]['ali_avatar'])?$userAllInfoData[0]['ali_avatar']:'',
                'name'=>isset($userAllInfoData[0]['name'])?$userAllInfoData[0]['name']:'',
                'card_number'=>isset($userAllInfoData[0]['card_number'])?$userAllInfoData[0]['card_number']:'',
                'mobile'=>isset($userAllInfoData[0]['mobile'])?$userAllInfoData[0]['mobile']:'',
                'identification'=>1,
                'is_deposit'=>0,
                'is_vip'=>isset($userAllInfoData[0]['is_vip'])?$userAllInfoData[0]['is_vip']:0,
                'id_card'=>0,
                'card_type'=>isset($userAllInfoData[0]['card_type'])?$userAllInfoData[0]['card_type']:0,
                'user_from'=>$data['user_from'],
                'status'=>0,
                'province'=>isset($userAllInfoData[0]['province'])?$userAllInfoData[0]['province']:0,
                'city'=>isset($userAllInfoData[0]['city'])?$userAllInfoData[0]['city']:0,
                'district'=>isset($userAllInfoData[0]['district'])?$userAllInfoData[0]['district']:0,
            ];
            $userAddData=M_Mysqli_Class('md_lixiang','UserModel')->addUser($addData);
            return 1000;
        }

    }
    //因为线上php版本不支持array_column函数所以重写方法
    function array_column($array, $column_key, $index_key=null){
        $result = [];
        foreach($array as $arr) {
            if(!is_array($arr)) continue;

            if(is_null($column_key)){
                $value = $arr;
            }else{
                $value = $arr[$column_key];
            }

            if(!is_null($index_key)){
                $key = $arr[$index_key];
                $result[$key] = $value;
            }else{
                $result[] = $value;
            }
        }
        return $result;
    }
}