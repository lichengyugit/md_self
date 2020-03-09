<?php
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/lib/StatisticClient.php';

// 鍋囧鏈変釜User::getInfo鏂规硶瑕佺洃鎺�

class Testworkcount_module{
     public function inform($name,$coordinate,$type,$msg){
         // 缁熻寮�濮�
         // StatisticClient::tick("鑹剧帥鐢靛姩杞�", '璧ゆ湀璺�223鍙�1011');
         // 缁熻鐨勪骇鐢燂紝鎺ュ彛璋冪敤鏄惁鎴愬姛銆侀敊璇爜銆侀敊璇棩蹇�
         $success = true;
         //$code = 0;
         //$msg = '';
         $a = StatisticClient::report($name, $coordinate, true, $type, $msg);
         return $a;
     }
 }
