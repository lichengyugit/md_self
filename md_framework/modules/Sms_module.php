<?php
class Sms_module {
    private $CI;
    private $log;
    private $key="e796007fc21f318e84310ef4c1fa5fb2";

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_config();
    }

    private function getSmsData($mobile,$content) {
        $data['url']="https://way.jd.com/chuangxin/dxjk";
        $data['mobile']=$mobile;
        $data['content']=$content;
        $data['appkey']=$this->key;
        return $data;
    }

    private function sendSms($mobile,$content,$userId){
        $data=$this->getSmsData($mobile,$content);
        $data1=[
            'mobile'=>$data['mobile'],
            'content'=>$data['content'],
            'appkey'=>$data['appkey']
        ];
        //print_r($data1);
        /* $sendStr=$data['url'];
         $sendStr.="?mobile=".$data['mobile'];
         $sendStr.="&content=".$data['content'];
         $sendStr.="&appkey=".$data['appkey'];*/
        get_log()->log_api('<接口测试> #### 接口名：调用短信发送接口参数：'.$data1['mobile'].','.$data1['content']);
        $result=$this->wx_http_request($data['url'],$data1);
        //返回格式{"code": "10000","charge": true,"remain": 2355,"msg": "查询成功,扣费","result": {"ReturnStatus": "Success","Message": "ok","RemainPoint": 430666,"TaskID": 18762423,"SuccessCounts": 1}}
        get_log()->log_api('<接口测试> #### 接口名：调用短信发送接口返回值：'.$result);
        $arr=json_decode($result,true);
        if($arr['code']==10000){
            if($arr['remain']==100 || $arr['remain']==50){
                $this->caution($arr['remain']);
            }
            $res=$arr['result'];
            //写入日志文件
            $log=[
                'user_id'=>$userId,
                'send_mobile'=>$data['mobile'],
                'code'=>$arr['code'],
                'charge'=>$arr['charge'],
                'msg'=>$arr['msg'],
                'status'=>$res['ReturnStatus'],
                'message'=>$res['Message'],
                'task_id'=>$res['TaskID'],
                'success_counts'=>$res['SuccessCounts']
            ];
            M_Mysqli_Class('md_lixiang','SmsLogModel')->addLog($log);
            if($arr['code']=='10000'){
                $message="发送成功，手机号为".$mobile."，内容为".$content;
            }
            else{
                $message="发送失败，手机号为".$mobile."，内容为".$content." ，错误状态码为".$arr['code'];
            } 
        }
        //$this->CI->log_info($message);
        //print_r($arr1);
        return $arr;
    }

    /**
     * 短信发送
     */
    public function rSendSms($mobile,$userId,$code_type=1,$password=1){
        $code=rand(100000,999999);
        if($password!=1){
            $code=$password;
        }
        $time=time();
        if($code_type==1){//登录
            $decribe = '【魔动新能源】验证码:'.$code.'，您正在绑定手机号,此验证码15分钟内有效（为保证帐号安全，请勿向他人透露）';
        }elseif($code_type==2){//找回密码
            $decribe = '【魔动新能源】验证码:'.$code.'，您正在找回密码,此验证码15分钟内有效（为保证帐号安全，请勿向他人透露）';
        }elseif($code_type==3){
            $decribe = '【魔动新能源】恭喜您注册成功。您的初始密码为：'.$code.',为保证账号安全，请及时登录修改密码';
        }
        $res=M_Mysqli_Class('md_lixiang','UserSmsModel')->getUserSms($mobile,$code_type);
        if($res['c']==0){
            //$this->load->library('Sms/ChuanglanSmsApi');
            $result = $this->sendSms($mobile,$decribe,$userId);
            if($result['code']==10000){
                $data['mobile']=$mobile;
                $data['code_type']=$code_type;
                $data['code']=$code;
                $data['resend_time']=$time+60;
                $data['over_time']=$time+60*15;
                $addSms=M_Mysqli_Class('md_lixiang','UserSmsModel')->addSms($data);
                $outPut['status']="ok";
                $outPut['code']='2000';
                $outPut['msg']='已发送';
                $outPut['data']="";
            }else{
                $outPut['status']="error";
                $outPut['code']="1001";
                $outPut['msg']="服务器繁忙";
                $outPut['data']="";
            }
        }else{
            if($res['resendTime']>$time){
                $outPut['status']="error";
                $outPut['code']="1006";
                $outPut['msg']='请不要重复发送短信';
                $outPut['data']="";
            }else{
                $result=$this->sendSms($mobile,$decribe,$userId);
                if($result['code']==10000){
                    $data['mobile']=$mobile;
                    $data['code_type']=$code_type;
                    $data['code']=$code;
                    $data['resend_time']=$time+60;
                    $data['over_time']=$time+60*15;
                    $sms=M_Mysqli_Class('md_lixiang','UserSmsModel')->updateSms($data);
                    if($sms){
                        $outPut['status']="ok";
                        $outPut['code']='2000';
                        $outPut['msg']='已发送';
                        $outPut['data']="";
                    }else{
                        $outPut['status']="error";
                        $outPut['code']='1001';
                        $outPut['msg']='服务器繁忙';
                        $outPut['data']="";
                    }

                }else{
                    $outPut['status']="error";
                    $outPut['code']='1001';
                    $outPut['msg']='服务器繁忙';
                    $outPut['data']="";
                }
            }
        }
        /*if(!is_null($result)){
            //$output = json_decode($result,true);
            if(isset($result['code'])  && $result['code']=='10000'){
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
        }*/
        return $outPut;
    }

    //curl请求
    private function wx_http_request($url, $params, $body="", $isPost=false, $isImage=false ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url."?".http_build_query($params));
        if($isPost){
            if($isImage){
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: multipart/form-data;',
                        "Content-Length: ".strlen($body)
                    )
                );
            }else{
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: text/plain'
                    )
                );
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    /**
     * 预警
     */
    public function caution($remain,$type=1){
        $mobile='13816439927';
        if($type==1){
            $content="【魔动新能源】魔动提醒,短信发送接口还剩余:".$remain."次";
        }else{
            $content="【魔动新能源】魔动提醒,实名认证接口还剩余:".$remain."次";
        }
        $data=$this->getSmsData($mobile, $content);
        $data1=$data;
        unset($data1['url']);
        get_log()->log_api('<接口测试> #### 接口名：调用短信发送接口参数：'.$data['mobile'].','.$data['content']);
        $result=$this->wx_http_request($data['url'],$data1);
        get_log()->log_api('<接口测试> #### 接口名：调用短信发送接口返回值：'.$result);
    }
}