<?php
class Voice_message_module {

    public function __construct() {
        $this->log = & get_log();
        $this->CI = & get_instance();
    }
    
    protected $account = 'VM48966293';//用户名
    protected $password = '658c91a9f6ab2df439c7de272fc44a05';//密码
    protected $sendVoiceUrl = 'http://api.vm.ihuyi.com/webservice/voice.php?method=Submit';//语音消息接口提交地址

    /**
     * 语音消息发送接口
     * @param unknown $data
     */
    public function sendVoiceMessage($data){
        $uri="account=".$this->account."&password=".$this->password."&mobile=".$data['mobile']."&content=".$data['content']."&format=json";
        $gets =  json_decode($this->curlPost($uri, $this->sendVoiceUrl),true);
        if($gets['code']==2){
            $outPut['status']='ok';
            $outPut['code']='2000';
            $outPut['msg']=$gets['msg'];
        }else{
            $outPut['status']='error';
            $outPut['code']=$gets['code'];
            $outPut['msg']=$gets['msg'];
        }
        return $outPut;
    }
    
    /**
     * curl发送post请求
     * @param unknown $curlPost
     * @param unknown $url
     */
    private function curlPost($curlPost,$url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }
    
//     /**
//      * xml转数组
//      * @param unknown $xml
//      * @return unknown
//      */
//     private function xmlToArray($xml){
//         $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
//         if(preg_match_all($reg, $xml, $matches)){
//             $count = count($matches[0]);
//             for($i = 0; $i < $count; $i++){
//                 $subxml= $matches[2][$i];
//                 $key = $matches[1][$i];
//                 if(preg_match( $reg, $subxml )){
//                     $arr[$key] = $this->xmlToArray( $subxml );
//                 }else{
//                     $arr[$key] = $subxml;
//                 }
//             }
//         }
//         return $arr;
//     }
}
