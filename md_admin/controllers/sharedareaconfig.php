<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}

Class Sharedareaconfig extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);        
        $this->smarty->assign("function", "Sharedareaconfig");
    }
    
    //添加区域用户消费配置
    public function Addsharedareaconfig(){
        F()->Resource_module->setTitle('添加共享区用户区域消费配置');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        if(array_key_exists('name',$parames)){

          if(array_key_exists('deposit',$parames)){
            $data=[
              'deposit'=>$parames['deposit']
            ];
            unset($parames['deposit']);
            if(array_key_exists('price', $parames)){
              $data+=[
                'price'=>json_encode($parames['price']),
                'totle'=>$parames['totle']
              ];
              unset($parames['totle']);
              unset($parames['price']);
            }
            $data+=[
              'province'=>$parames['province'],
              'city'=>$parames['city'],
              'province_id'=>$parames['province_id'],
              'city_id'=>$parames['city_id'],
              'name'=>$parames['name']
            ];
            $payment=M_Mysqli_Class('md_lixiang','UserPaymentConfigModel')->addPaymentConfig($data);
            if($payment){
                $parames['card_time']=$parames['card_time']*86400;
                $parames['card_addtime']=$parames['card_addtime']*86400;
                $card=M_Mysqli_Class('md_lixiang','CardConfigModel')->addCardConfig($parames);
                if($card){
                    $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                  $this->msg('添加成功','/Addsharedareaconfig','ok');exit;
                }else{
                    $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                  $this->msg('月卡添加失败','/Addsharedareaconfig','error');exit;
                }
            }else{
              $this->msg('押金表添加失败','/Addsharedareaconfig','error');exit;
            }
          }else{
            $parames['card_time']=$parames['card_time']*86400;
            $parames['card_addtime']=$parames['card_addtime']*86400;
            $card=M_Mysqli_Class('md_lixiang','CardConfigModel')->addCardConfig($parames);
            if($card){
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
              $this->msg('添加成功','/Addsharedareaconfig','ok');exit;
            }else{
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
              $this->msg('月卡添加失败','/Addsharedareaconfig','error');exit;
            }
          }

        }else{
            $province=M_Mysqli_Class('md_lixiang','ProvinceModel')->getAllProvince([]);
            $this->smarty->assign('province',$province);
            $this->smarty->view('sharedareaconfig/insert.phtml');
        }
    }

     /**
      * 省市联动接口
      */
     public function city(){
          $parames=$this->parames;
          $city=M_Mysqli_Class('md_lixiang','CityModel')->getAllCityConfig(['ProvinceID'=>$parames['province']]);
          $str['html']="";
          foreach ($city as $k => $v) {
              $str['html'].="<option value='".$v['CityID']."'>".$v['CityName']."</option>";
          }
          $str['html'].="";
          $this->setOutPut($str);
     }

     /**
      *  查看当前城市是否有配置
      */
     public function inspectCity(){
      $parames=$this->parames;
      $city=M_Mysqli_Class('md_lixiang','UserPaymentConfigModel')->getCityname($parames['ls']);
      $this->setOutPut($city);
     }

     /**
      * 共享区区域配置列表
      */
     public function sharedareaconfigList(){
      F()->Resource_module->setTitle('区域配置列表');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $paymentConfig=M_Mysqli_Class('md_lixiang','UserPaymentConfigModel')->getConfigByAttr([]);
      $this->smarty->assign('arr',$paymentConfig);
      $this->smarty->view('sharedareaconfig/list.phtml');
     }

     /**
      * 月卡配置列表
      */
     public function AreaCardConfigList(){
      $parames=$this->parames;
      F()->Resource_module->setTitle($parames['city'].'卡券配置');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $card_config=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAllCardConfig($parames);
      $this->smarty->assign('arr',$card_config);
      $this->smarty->view('sharedareaconfig/card_list.phtml');
     }

     /**
      * 修改押金配置
      */
     public function paymentConfigUpdata(){
      $parames=$this->parames;

      if(array_key_exists('deposit',$parames)){
        $data=[];
        $data+=[
          'price'=>json_encode($parames['price']),
          'totle'=>$parames['totle'],
          'deposit'=>$parames['deposit']
        ];
        $updatePayment=M_Mysqli_Class('md_lixiang','UserPaymentConfigModel')->updatePaymentByAttr($data,['id'=>$parames['id']]);
         if($updatePayment){
             $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
             $this->msg('操作成功','/sharedareaconfigList','ok');
         }else{
             $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
             $this->msg('操作失败','/sharedareaconfigList','error');
         }
      }else{
        F()->Resource_module->setTitle('修改配置');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $paymentConfig=M_Mysqli_Class('md_lixiang','UserPaymentConfigModel')->getConfigByAttr($parames);
        $price=json_decode($paymentConfig[0]['price']);
        $str='';
        for($i=0 ; $i<count($price) ; $i++){
          $str.=$price[$i].',';
        }
        $this->smarty->assign('price',rtrim($str,','));
        $this->smarty->assign('arr',$paymentConfig[0]);
        $this->smarty->view('sharedareaconfig/updata.phtml');
        }
     }

     /**
      * 修改月卡配置
      */
     public function cardConfigUpdata(){
        $parames=$this->parames;
        if(array_key_exists('name',$parames)){
          $parames['card_time']=$parames['card_time']*86400;
          $parames['card_addtime']=$parames['card_addtime']*86400;
          $updateCard=M_Mysqli_Class('md_lixiang','CardConfigModel')->updateCard($parames);
         if($updateCard){
             $this->msg('操作成功','/sharedareaconfig/AreaCardConfigList?city='.$parames["city"].'&id='.$parames["id"].'&city_id='.$parames['city_id'].`'`,'ok');
         }else{
             $this->msg('操作失败','/sharedareaconfig/AreaCardConfigList?city='.$parames["city"].'&id='.$parames["id"].'&city_id='.$parames['city_id'].`'`,'error');
         }

        }else{
          F()->Resource_module->setTitle('修改卡券配置');
          F()->Resource_module->setJsAndCss(array(
              'home_page'
          ), array(
              'main'
          ));
          $card_config=M_Mysqli_Class('md_lixiang','CardConfigModel')->getAllCardConfig($parames);
          $this->smarty->assign('arr',$card_config[0]);
          $this->smarty->view('sharedareaconfig/card_updata.phtml');
        }
     }

     /**
      * 配置删除
      */
     public function paymentConfigDelete(){
      $parames=$this->parames;

      $Result=M_Mysqli_Class('md_lixiang','UserPaymentConfigModel')->updatePaymentByAttr(['status'=>$parames['status']],['city_id'=>$parames['city_id']]);
       if($Result){
           $this->msg('操作成功','/sharedareaconfigList','ok');
       }else{
           $this->msg('操作失败','/sharedareaconfigList','error');
       }
     }


     /**
      * 卡券配置删除
      */
     public function cardConfigDelete(){
      $parames=$this->parames;
      $Result=M_Mysqli_Class('md_lixiang','CardConfigModel')->updateCardByAttr(['status'=>$parames['status']],['id'=>$parames['id']]);
       if($Result){
           $this->msg('操作成功','/sharedareaconfigList','ok');
       }else{
           $this->msg('操作失败','/sharedareaconfigList','error');
       }
     }







}

























