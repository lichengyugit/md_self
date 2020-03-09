<?php
date_default_timezone_set('Asia/Shanghai');
define('ROOTPATH', dirname(dirname(__FILE__)) . '/');
require_once ROOTPATH . '/md_config/define.php';
$d = new DefineConfig('admin');
define('APP_NAME', 'md_admin');
/*define('DOMAIN_HOST', 'xianghuandian.com');*/
$arr=explode('.', $_SERVER['HTTP_HOST']);
$num=count($arr);
if($num==4){
   if($arr[3]=='cn'){
       $url=$arr[1].'.'.$arr[2].'.'.$arr[3];
   }else{
       $url=$arr[2].'.'.$arr[3];
   } 
}elseif ($num==5){
    $url=$arr[2].'.'.$arr[3].'.'.$arr[4];
}else{
    $url=$arr[1].'.'.$arr[2];
}
define('DOMAIN_HOST', $url);
$d->bootstrap();
require_once BASEPATH . DS . ENVIRONMENT . DS . 'CodeIgniter.php';
