<?php
header('Content-type: application/json');
header('HTTP/1.1 200 OK');
require DEFAULT_SYSTEM_PATH.'libraries/Qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;
/**
 * 七牛上传图片
 */
class Qiniu_module {
    private $CI;
    private $log;

    public function __construct() 
    {
        $this->CI = & get_instance();
        $this->log = & get_config();
    }

    // 用于签名的公钥和私钥
//     protected $accessKey = 'd4iGHqB2pw_x2UKLWI7p1bZTCMWocquqN8DOtF6a';
//     protected $secretKey = '7fO5tuY1jijiW8E-I85uohMcbyb-AInljMJW3D-U';
//     protected $bucket = 'xiubike';
        protected $accessKey = 'V7SX38uzQ7W7vN_d6FjDHjDrbtDaZdTA-CDHCDgN';
        protected $secretKey = 'vu0mswY4a8RcOsQ7bQSzNkOFzpJYvfdTAYoXPvBJ';
        protected $bucket = 'lixiang';
        protected $domain = IMG_URL;
    
    /**
     * [createToken 生成文件上传token]
     * @return [type] [description]
     */
    public function createToken()
    { 
        // 初始化签权对象
        $auth = new \Qiniu\Auth($this->accessKey,$this->secretKey);
//         return $token = $auth->uploadToken($this->bucket);
        return $auth;
    }
    
       
    //获取上传Token
    public function getQiniuToken()
    {

        // 生成上传 Token
        $token = $this->createToken()->uploadToken($this->bucket);
        return $token;
    
    }
    
    
    //上传文件
    /**
     *
     * @param unknown $filePathl 本地路径
     * @param unknown $key       上传到七牛后保存的文件名
     * @param unknown $domain    上传的目标空间域名
     * @param unknown $uploadMgr 初始化 UploadManager 对象并进行文件的上传。
     * @param unknown $token     调用getQiniuToken方法获取上传token
     * @return multitype:
     */
    public function uploadPic($filePath,$key)
    {   
        
        //引入上传类
        require_once( DEFAULT_SYSTEM_PATH."libraries/Qiniu/src/Qiniu/Storage/UploadManager.php");
        $domain=$this->domain;
        $filePath = $filePath;
        $key = date('YmdHis').$key;
        $uploadMgr = new Qiniu\Storage\UploadManager();
        $token=$this->getQiniuToken();
        // 调用 UploadManager的 putFile方法进行文件的上传。
        list($succ, $err) = $uploadMgr->putFile($token, $key, $filePath);
        //返回Key=七牛云文件名和hash
        if ($err !== null) {
            return $err;
        } else {
            return $succ;
        }
    }
    
    
    //下载私有空间的文件
    /**
     *
     * @param unknown $qiniuUrl  七牛云私有空间的域名
     * @param unknown $key       七牛云保存的文件名
     */
    public function downloadUrl($key)
    {
        // 私有空间中的域名
        $baseUrl = 'http://'.$this->domain.'/'.$key;
        // 对链接进行签名,第二参数$expires 设置链接过期时间默认3600s
        $signedUrl = $this->createToken()->privateDownloadUrl($baseUrl);
        return $signedUrl;
    }




    public function getPfop()
    {
        //转码是使用的队列名称。 https://portal.qiniu.com/mps/pipeline
        $pipeline = 'gzmod_filename';
        $pfop = new Qiniu\Processing\PersistentFop($this->createToken(), $this->bucket, $pipeline, null);
        return $pfop;
    }





    /**
     * 音视频转码 -->mp4
     * @param unknown $key       七牛云保存的文件名
     */
    public function videoTranscode($keys)
    {
        $key = $keys;
       //要进行转码的转码操作。
        $fops = "avthumb/mp4/s/640x360/vb/1.4m";
//        $fops = "avthumb/mp4/s/640x360/vb/1.4m|saveas/" . \Qiniu\base64_urlSafeEncode($this->bucket . ":qiniu_640x360.mp4");
        list($id, $err) = $this->getPfop()->execute($key, $fops);
        if ($err != null) {
            return false;
        } else {
            return $this->getQiniuFile($id);
        }
    }

    /**
     * 获取转码状态里面包含文件名
     * @param unknown $id     七牛云转码中的id
     */
    public function getQiniuFile($id)
    {
        time_sleep_until(time()+3.5);
        list($ret, $err) = $this->getPfop()->status($id);
        if ($err != null) {
            return false;
        } else {
            return $ret;
        }
    }


}
