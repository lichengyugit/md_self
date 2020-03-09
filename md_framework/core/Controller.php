<?php
/**
 * CodeIgniter
 * 
 * @package CodeIgniter
 * @author Neowei
 * @copyright Copyright (c) 2015 - 2025, aqdog, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://www.aqdog.com
 * @since Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Application Controller Class
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Libraries
 * @author Neowei
 * @link http://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller {
    private static $instance;

    /**
     * Constructor
     */
    public function __construct() {
        self::$instance = & $this;
        // Assign all the class objects that were instantiated by the
        // bootstrap file (CodeIgniter.php) to local class variables
        // so that CI can run as one big super object.
        foreach (is_loaded() as $var=>$class) {
            $this->$var = & load_class($class);
        }
        
        $this->load = & load_class('Loader', 'core');
        $this->load->initialize();
        
        $this->log = &get_log();
        $this->log->log_trace('控制类controller实例化 Controller Class Initialized');
    }

    public static function &get_instance() {
        return self::$instance;
    }

    protected function redirect($uri = '', $method = 'location', $http_response_code = 302) {

        if (!preg_match('#^https?://#i', $uri)) {
            $uri = BASE_URL . '/' . $uri;
        }
        switch ($method) {
        case 'refresh' :
            header("Refresh:0;url=" . $uri);
            break;
        default :
            header("Location: " . $uri, TRUE, $http_response_code);
            break;
        }
        if (php_sapi_name() !== 'cli' or !defined('STDIN')) {
            exit();
        }
    }

    public function show_404($page = '') {
        $this->log->log_warn('404 页面找不到了 --> ' . $page);
        $this->redirect(BASE_URL . '/httperror/error404');
        return FALSE;
    }

    public function _ajaxFail($message = '', $data = '') {
        header('Content-Type:application/json; charset=utf-8');
        $result['code'] = 0;
        $result['msg'] = $message;
        $result['data'] = $data;
        echo json_encode($result);
    }

    public function _ajaxSucc($message = '', $data = '') {
        header('Content-Type:application/json; charset=utf-8');
        $result['code'] = 1;
        $result['msg'] = $message;
        $result['data'] = $data;
        echo json_encode($result);
    }

    public function log_warn($message, $php_error = FALSE) {
        $this->log->write_log('warn', $message, $php_error);
    }

    public function log_debug($message, $php_error = FALSE) {
        $this->log->write_log('debug', $message, $php_error);
    }

    public function log_info($message, $php_error = FALSE) {
        $this->log->write_log('info', $message, $php_error);
    }

    public function log_error($message, $php_error = FALSE) {
        $this->log->write_log('error', $message, $php_error);
    }

    private function getLockCache() {
        if (!isset($this->cache)) {
            $this->load->driver('cache', array(
                    'adapter' => 'file' 
            ), 'lock_cache');
        }
        return $this->lock_cache;
    }

    public function getFileCache() {
        if (!isset($this->file_cache)) {
            $this->load->driver('cache', array(
                    'adapter' => 'file' 
            ), 'file_cache');
        }
        return $this->file_cache;
    }

    public function getRedisCache() {
        if (!isset($this->file_cache)) {
            $this->load->driver('cache', array(
                    'adapter' => 'redis' 
            ), 'redis_cache');
        }
        return $this->redis_cache;
    }

    public function lock($strkey) {
        if (OPEN_LOCK) {
            $this->getLockCache();
            $key = 'lock-cache-' . $this->uri->uri_string . '-' . $strkey;
            if ($this->lock_cache->get($key)) {
                $count = 0;
                while($this->lock_cache->get($key) && $count <= 50) {
                    if ($count == 50) {
                        $this->unlock($strkey);
                        break;
                    } else {
                        $count++;
                        usleep(100000);
                    }
                }
            } else {
                $this->lock_cache->save($key, 'true');
            }
        }
    }

    public function unlock($strkey) {
        if (OPEN_LOCK) {
            $key = 'lock-cache-' . $this->uri->uri_string . '-' . $strkey;
            $this->lock_cache->delete($key);
        }
    }

    protected function encryptId($id) {
        $strbase = "Flpvf70CsakVjqgeWUPXQxSyJizmNH6B1u3b8cAEKwTd54nRtZOMDhoG2YLrI";
        $rtn = "";
        $numslen = strlen($id);
        // 密文第一位标记数字的长度
        $begin = substr(substr($strbase, 0, 9), $numslen - 1, 1);
        
        // 密文的扩展位
        $extlen = 8 - $numslen;
        $temp = str_replace('.', '', $id / 2.718281828459045235360287471352662497757247093699);
        $temp = substr($temp, -$extlen);
        
        $arrextTemp = str_split(substr($strbase, 19));
        $arrext = str_split($temp);
        foreach ($arrext as $v) {
            $rtn .= $arrextTemp[$v];
        }
        
        $arrnumsTemp = str_split(substr($strbase, 9, 10));
        $arrnums = str_split($id);
        foreach ($arrnums as $v) {
            $rtn .= $arrnumsTemp[$v];
        }
        return $begin . $rtn;
    }

    protected function encryptIds($data) {
        foreach ($data as &$val) {
            $val['id'] = $this->encryptId($val['id']);
        }
        return $data;
    }

    protected function decryptId($output) {
        $strbase = 'Flpvf70CsakVjqgeWUPXQxSyJizmNH6B1u3b8cAEKwTd54nRtZOMDhoG2YLrI';
        $begin = substr($output, 0, 1);
        $rtn = '';
        $len = strpos(substr($strbase, 0, 9), $begin);
        if ($len !== false) {
            $len++;
            $arrnums = str_split(substr($output, -$len));
            foreach ($arrnums as $v) {
                $rtn .= strpos(substr($strbase, 9, 10), $v);
            }
        }
        return $rtn;
    }

    protected function _checkPost() {
        if (IS_POST) {
            return TRUE;
        } else {
            $this->log_error('该页面是post请求,不能其他方式直接访问!来自IP为:' . CLIENTIP . '的请求');
            return $this->show_404();
        }
    }

    protected function _checkGet() {
        if (IS_GET) {
            return TRUE;
        } else {
            $this->log_error('该页面是get请求,不能其他方式直接访问!来自IP为:' . CLIENTIP . '的请求');
            return $this->show_404();
        }
    }

    protected function _checkAjax() {
        if (IS_AJAX) {
            return TRUE;
        } else {
            $this->log_error('该页面是ajax请求,不能直接访问!来自IP为:' . CLIENTIP . '的请求');
            return $this->show_404();
        }
    }

    protected function _checkRepeat() {
        return F()->Validation_module->checkRepeat();
    }
    
    /**
     * 获得用户salt
     */
    protected function getSalt($length){
        $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $key="";
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }
    
/**
     * GBK转UTF-8
     */
    public function gbk2utf8($s)
    {
        return mb_convert_encoding($s,"UTF-8","GBK");
    }
    
    /**
     * UTF-8转GBK
     * @param unknown $s
     * @return string
     */
    public function utf82gbk($s)
    {
        return mb_convert_encoding($s,"GBK","UTF-8");
    }
    
    /**
     * 生成随机数
     */
    protected function randStr($len=8,$format='ALL'){
        $is_abc = $is_numer = 0;
        $password = $tmp ='';  
        switch($format){
        case 'ALL':
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        break;
        case 'CHAR':
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        break;
        case 'NUMBER':
        $chars='0123456789';
        break;
        case 'SMALL':
        $chars='0123456789abcdefghijklmnopqrstuvwxyz';
        break;
        default :
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        break;
        }
        mt_srand((double)microtime()*1000000*getmypid());
        while(strlen($password)<$len){
        $tmp =substr($chars,(mt_rand()%strlen($chars)),1);
        if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
        $is_numer = 1;
        }
        if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
        $is_abc = 1;
        }
        $password.= $tmp;
        }
        if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
        $password = $this->randStr($len,$format);
        }
        return $password;
    }
    
    /**
     * 验证手机是否符合规则
     */
    public function checkMolieRules($mobile_phone){
        $search ='/^(1(([35][0-9])|(47)|[8][0123456789]))\d{8}$/';
        if(preg_match($search,$mobile_phone)) {
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * 验证邮箱是否符合规则
     */
    public function checkEmailRules($Email){
        $search ='/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/';
        if(preg_match($search,$Email)) {
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * 获得IP整形
     * @return number
     */
    public function get_ip()
    {
        return ip2long($_SERVER["REMOTE_ADDR"]);
    }
    
    /**
     * 获得IP
     * @return unknown
     */
    public function get_ip_char()
    {
        return $_SERVER["REMOTE_ADDR"];
    }
    
    /**
     * IP整形转换浮点
     * @param unknown $ip
     * @return string
     */
    public function read_ip($ip)
    {
        return long2ip($ip);
    }
    
    /**
     * 获得指定字符串后的字符串
     */
    public function intercept_str($start,$end,$str)
    {
        if(empty($start)||empty($end)||empty($str))return "参数不正确";
        $strarr=explode($start,$str);
        $str=$strarr[1];
        $strarr=explode($end,$str);
        return $strarr[0];
    }
    
    /**
     * 上传图片
     */
    public function uploadPic($fileName){
        $this->load->library('common_picture');
        $deeppath = $this->common_picture->getDeepPath();
        $config['upload_path'] = PIC_SRC_PATH . $deeppath;
        $this->common_picture->mkdirs($config['upload_path']);
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = 2046;
        //$config['max_width'] = 1024;
        //$config['max_height'] = 768;
        $config['file_name'] = $this->common_picture->createPicFilename();
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload($fileName)) {
            $error = array(
                    'error' => $this->upload->display_errors()
            );
            return $error;
        } else {
            $data = array(
                    'upload_data' => $this->upload->data()
            );
            $picUrl=$this->common_picture->getSourcePicture($deeppath, $data['upload_data']['file_name']);
            $data['upload_data']['picUrl']=$deeppath.'/'.$data['upload_data']['file_name'];
            $data['upload_data']['deeppath']=$deeppath;
            return $data;
        }
    }
    
    /**
     * 上传文件
     * @param unknown $fileName
     */
    public function uploadFile($fileName){
        
    }
    
    /**
     * 上传视频
     * @param unknown $fileName
     */
    public function uploadVideo($fileName){
        $this->load->library('common_video');
        $deeppath = $this->common_video->getDeepPath();
        $config['upload_path'] = VIDEOS_PATH . $deeppath;
        $this->common_video->mkdirs($config['upload_path']);
        $config['allowed_types'] = 'mp4';
        $config['max_size'] = 20000;
        $config['file_name'] = $this->common_video->createVideoFilename();
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload($fileName)) {
            $error = array(
                    'error' => $this->upload->display_errors()
            );
            return $error;
        } else {
            $data = array(
                    'upload_data' => $this->upload->data()
            );
            $videoUrl=$this->common_video->getSourceVideo($deeppath, $data['upload_data']['file_name']);
            $data['upload_data']['videoUrl']=$videoUrl;
            $data['upload_data']['deeppath']=$deeppath;
            return $data;
        }
    }
    
    /**
     * 跳转
     */
    public function msg($msg,$url,$type,$jumpurl="")
    {
        F()->Resource_module->setTitle('页面跳转中。。。');
        F()->Resource_module->setJsAndCss(array(
                'home_page',
                'common'
        ), array(
                'backend'
        ));
        $this->smarty->assign("msg",$msg);
        $this->smarty->assign("url",$url);
        $this->smarty->assign("index_page",'true');
        if($url AND !$jumpurl)
        {
            $jumpurl=$url;
        }
        $this->smarty->assign("jumpurl",$jumpurl);
        if($type=="ok"){
            $tp="layout/jumpurl.phtml";
        }
        if($type=="error"){
            $tp="layout/jumpurl.phtml";
        }
        if(!$tp) {
            exit("未指定 core->msg()::type");
        }
        $this->smarty->view($tp);
        //exit;
    }
    
    /**
     * 生成写入图片数组
     */
    public function getPicData($pictureData,$type_id,$attribute_id){
        $time=time();
        $data['type_id'] = $type_id;
        $data['attribute_id'] = $attribute_id;
        $data['filename'] = $pictureData['upload_data']['file_name'];
        $data['deeppath'] = $pictureData['upload_data']['deeppath'];
        $data['create_time'] = date("y-m-d h:i:s",$time);
        $data['create_time_str'] = $time;
        return $data;
    }
    
    /**
     * 获得图片信息data
     */
    public function getPictureData($pictureData){
        $this->load->library('common_picture');
        $data['id']=$pictureData['id'];
        $data['pic_url']=$this->common_picture->getSourcePicture($pictureData['deeppath'], $pictureData['filename']);
        return $data;
    }
    
    /**
     * 获得优酷视频ID
     */
    private function getYkVideoId($url){
        //$url='http://player.youku.com/player.php/sid/XMTM5NDgwMzE4NA==/v.swf';
        $url=str_replace('http://','',$url);
        $urlArray=explode('/', $url);
        $videoId=str_replace('==','',$urlArray[3]);
        return $videoId;
        echo '<iframe src="http://likeyou.x9.fjjsp01.com/youku/videoyk.jsp?token=v&width=620&height=400&auto=no&id='.$videoId.'" width="620" height="400" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>';
    }
    
    /**
     * 生成视频播放器代码
     */
    public function createPlayer1($url,$width='620',$height='400',$auto='on'){
        $id=$this->getYkVideoId($url);
        $player='<iframe src="http://likeyou.x9.fjjsp01.com/youku/videoyk.jsp?token=v&width='.$width.'&height='.$height.'&auto='.$auto.'&id='.$id.'" width="'.$width.'" height="'.$height.'" marginheight="0" marginwidth="0" frameborder="0" scrolling="no"></iframe>';
        return $player;
    }
    
    /**
     * 生成优酷视频播放器
     */
    public function createPlayer($url,$videoId,$width='620',$height='400',$auto='on'){
        $id=$this->getYkVideoId($url);
        $player='<div id="youkuplayer'.$videoId.'" style="width:'.$width.'px;height:'.$height.'px;margin:auto;"></div>';
        $player.='<script type="text/javascript" src="http://player.youku.com/jsapi">';
        $player.="player = new YKU.Player('youkuplayer".$videoId."',{
                  styleid: '0',
                  client_id: '4e2565eaa3d835df',
                  vid: '".$id."'
                  });";
        $player.='</script>';
        return $player;
    }
}

// END Controller class

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */
