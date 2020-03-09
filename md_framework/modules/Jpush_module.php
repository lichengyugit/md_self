<?php
require DEFAULT_SYSTEM_PATH.'libraries/Jpush/autoload.php';
use JPush\Client as JPush;
class Jpush_module {
    private $CI;
    private $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_config();
    }
    
    //测试
    protected $userAppKey = 'c79f57cfdb1d7ff59f3092b7';
    protected $userMasterSecret = '6d5208461c7881a3abeea3d4';
    protected $fixAppKey = '2f188d5f58539fbe14ddf3da';
    protected $fixMasterSecret = 'dc12e8cb0838043443cd952c';

    // protected $userAppKey = '6abc11a86e3fa42c7e5104a6';
    // protected $userMasterSecret = '42b749eb363ce5d3419591a6';
    protected $shopAppKey = 'e1e44180f7f09859f757220b';
    protected $shopMasterSecret = '80a282824da656d009458440';
    // protected $fixAppKey = '865bbc097cc12a961cf2b2dd';
    // protected $fixMasterSecret = '16453ab6444a0fe1cc66c6d3';

    protected $br = '<br/>';
    protected $client;
    
    /**
     * 发送push
     * @param unknown $userId
     * @param unknown $pushUserIdArray
     * @param unknown $content
     * @param unknown $platform
     */
    public function send($userId,$pushUserIdArray,$content,$platform,$source)
    {
        require_once DEFAULT_SYSTEM_PATH."libraries/Jpush/src/JPush/Client.php";
        $AppKey=$source.'AppKey';
        $MasterSecret=$source.'MasterSecret';
        $client = new JPush($this->$AppKey, $this->$MasterSecret);
        $return=$client->push()
        ->setPlatform($platform)
        ->addAlias($pushUserIdArray)
        ->setMessage($content)
        ->setOptions(100000, 3600, null, false);

        $notifyReturn = $client->push()
        ->setPlatform(array('ios'))
        ->addAlias($pushUserIdArray)
        ->setNotificationAlert('您有未读消息')
        ->iosNotification('您有未读消息',array('content-available' => true))
        // ->setMessage($content)
        ->setOptions(100000, 3600, null, false);
        try {
            $return->send();
            $notifyReturn->send();
            $jpushValidate=$client->push()
            ->setPlatform($platform)
            ->addAlias($pushUserIdArray)
            ->setMessage($content)
            ->setOptions(100000, 3600, null, false)
            ->validate();
            $body['Platform']=$platform;
            $body['Alias']=$pushUserIdArray;
            $body['Message']=$content;
            $parames['user_id']=$userId;
            $parames['function']="Push";
            $parames['body']=json_encode($body);
            $parames['http_code']=$jpushValidate['http_code'];
            $parames['response_body']=json_encode($jpushValidate['body']);
            $parames['response_header']=json_encode($jpushValidate['headers']);
            if($jpushValidate['http_code']=="200"){
                $parames['status']=1;
            }
            $insert=M_Mysqli_Class('cro', 'PushLogModel')->addJpushLog($parames);
            unset($parames);
            unset($body);
            $jpushValidate['status']='success';
            return $jpushValidate;
        } catch (\JPush\Exceptions\JPushException $e) {
//             try something else here
            $body['error_msg']=$e->getMessage();
            $body['error_code']=$e->getCode();
            $parames['status']='fail';
            $parames['body']=$body;
            $parames['http_code']='200';
            return $parames;
        }
        
    }
    
    /**
     * 将用户下单信息推送给修哥
     */
    public function pushUserOrderToFix($userId,$pushUserIdArray,$content,$platform){
        $source='fix';
        if($pushUserIdArray['type']==1){
           $pushReturn[]=$this->send($userId, $pushUserIdArray['data'], $content, $platform, $source);
        }else{
           unset($pushUserIdArray['type']);
           foreach($pushUserIdArray['data'] as $k=>$v){
               $pushReturn[]=$this->send($userId, $v, $content, $platform, $source);
           }
        }
        return $pushReturn;
    }
    
    /**
     * 操作传入数组
     */
    public function operationIdArray($idArray,$limit){
        if(count($idArray)>$limit){
           $i=0;
           foreach($idArray as $k=>$v){
               $newIdArray['data'][$i]=array_slice($idArray,$i,$limit);
               $i++;
           }
           $newIdArray['type']="2";
           return $newIdArray;
        }else{
            $newIdArray['type']="1";
            $newIdArray['data']=$idArray;
            return $newIdArray;
        }
    }
    
    /**
     * 对象转数组
     */
    public function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $key=str_replace(' ', '', $key);
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }
}