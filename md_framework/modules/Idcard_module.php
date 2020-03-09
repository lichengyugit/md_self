<?php
class Idcard_module{
    private $CI;
    private $log;
    private $key="e796007fc21f318e84310ef4c1fa5fb2";

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->log = & get_config();

    }
    //拼接数据
    private function getIdCardData($name,$cardNum) {
        $data['url']="https://way.jd.com/fegine/idCert";
        $data['name']=$name;
        $data['id']=$cardNum;
        $data['appkey']=$this->key;
        return $data;
    }

    //认证
    //不通过格式:Array([code]=>10000[charge]=>1[msg]=>查询成功,扣费[result]=>Array([id]=>410522199905065614[name]=>王小明[success]=>[msg]=>实名认证不通过[status] => 02 ) )
    //通过格式:Array ( [code] => 10000 [charge] => 1 [msg] => 查询成功,扣费 [result] => Array ( [id] => 410522199******** [name] => 常** [success] => 1 [msg] => 实名认证通过 [sex] => 男 [area] => 河南省 [birthday] => 1996- [status] => 01 ) )
    public function identification($name,$carNum,$userId){
        $data=$this->getIdCardData($name,$carNum);
        $data1=[
            'name'=>$data['name'],
            'id'=>$data['id'],
            'appkey'=>$data['appkey']
        ];
        get_log()->log_api('<接口测试> #### 接口名：调用身份证认证接口参数：'.$data1['name'].','.$data1['id']);
        $result=$this->wx_http_request($data['url'],$data1,'',true);
        get_log()->log_api('<接口测试> #### 接口名：调用身份证认证接口返回值：'.$result);
        $arr=json_decode($result,true);
        $res=$arr['result'];
        //写入日志文件
        if($arr['code']==10000){
            if($arr['remain']==100 || $arr['remain']==50){
                F()->Sms_module->caution($arr['remain'],2);
            }
            if($res['status']==2){
                $log=[
                    'user_id'=>$userId,
                    'code'=>$arr['code'],
                    'charge'=>$arr['charge'],
                    'msg'=>$arr['msg'],
                    'idcard'=>$res['id'],
                    'name'=>$res['name'],
                    'msg1'=>$res['msg'],
                    'status'=>$res['status']
                ];
            }elseif($res['status']==1){
                //写入日志文件
                $log=[
                    'user_id'=>$userId,
                    'code'=>$arr['code'],
                    'charge'=>$arr['charge'],
                    'msg'=>$arr['msg'],
                    'idcard'=>$res['id'],
                    'name'=>$res['name'],
                    'msg1'=>$res['msg'],
                    'status'=>$res['status'],
                    'success'=>$res['success'],
                    'sex'=>$res['sex'],
                    'area'=>$res['area'],
                    'birthday'=>$res['birthday']
                ];
            }
            M_Mysqli_Class('md_lixiang','IdCardLogModel')->addLog($log);
        }
        return $res;
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
    
    //后台添加用户 认证身份证
    public function UserIdentification($name,$cardNumber){
        $data=$this->getIdCardData($name,$cardNumber);
        $data1=[
            'name'=>$data['name'],
            'id'=>$data['id'],
            'appkey'=>$data['appkey']
        ];
        $result=$this->wx_http_request($data['url'],$data1);
        $arr=json_decode($result,true);
        return $arr;
    }
}