<?php
require DEFAULT_SYSTEM_PATH.'libraries/phpqrcode/phpqrcode.php';
class Qrcode_module {
    private $CI;
    private $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_config();
    }
    
    /**
     * 生成二维码
     * @param unknown $userId
     * @param unknown $pushUserIdArray
     * @param unknown $content
     * @param unknown $platform
     */
    public function makeQrcode($url='')
    {
        $value = $url;                  //二维码内容  
      
        $errorCorrectionLevel = 'L';    //容错级别   
        $matrixPointSize = 5;           //生成图片大小    
          
        //生成二维码图片  
        $filename = RESOURCE_URL.'/qrcode/'.microtime().'.png';  
        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);    
        
        $QR = $filename;                //已经生成的原始二维码图片文件    
      
      
        $QR = imagecreatefromstring(file_get_contents($QR));    
        
        //输出图片    
        imagepng($QR, 'qrcode.png');    
        imagedestroy($QR);  
        return '<img src="qrcode.png" alt="使用微信扫描支付">';
        
    }
    
    /**
     * 生成带logo的二维码
     */
    public function makeLogoQrcode($url=''){  
        $value = $url;                  //二维码内容    
        $errorCorrectionLevel = 'H';    //容错级别    
        $matrixPointSize = 6;           //生成图片大小    
        //生成二维码图片  
        $filename = 'qrcode/'.microtime().'.png';  
        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);    
          
        $logo = 'qrcode/logo.jpg';  //准备好的logo图片     
        $QR = $filename;            //已经生成的原始二维码图    
      
        if (file_exists($logo)) {     
            $QR = imagecreatefromstring(file_get_contents($QR));        //目标图象连接资源。  
            $logo = imagecreatefromstring(file_get_contents($logo));    //源图象连接资源。  
            $QR_width = imagesx($QR);           //二维码图片宽度     
            $QR_height = imagesy($QR);          //二维码图片高度     
            $logo_width = imagesx($logo);       //logo图片宽度     
            $logo_height = imagesy($logo);      //logo图片高度     
            $logo_qr_width = $QR_width / 4;     //组合之后logo的宽度(占二维码的1/5)  
            $scale = $logo_width/$logo_qr_width;    //logo的宽度缩放比(本身宽度/组合后的宽度)  
            $logo_qr_height = $logo_height/$scale;  //组合之后logo的高度  
            $from_width = ($QR_width - $logo_qr_width) / 2;   //组合之后logo左上角所在坐标点  
              
            //重新组合图片并调整大小  
            /* 
             *  imagecopyresampled() 将一幅图像(源图象)中的一块正方形区域拷贝到另一个图像中 
             */  
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,$logo_qr_height, $logo_width, $logo_height);   
        }     
        
        //输出图片    
        imagepng($QR, 'qrcode.png');    
        imagedestroy($QR);  
        imagedestroy($logo);  
        return '<img src="qrcode.png" alt="使用微信扫描支付">';     
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