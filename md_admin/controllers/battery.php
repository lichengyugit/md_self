<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
Class Battery extends MY_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->parames=$this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);       
        $this->smarty->assign('function','battery');     
    }
    
    public function index(){
            $this->checkAuth();
            F()->Resource_module->setTitle('电池列表');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
//            $url="/batteryList";
            $batteryConfig= require_once MAIL_SRC_PATH.'Battery_config.php';
//            $nums=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttr($this->parames);
//            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
//            $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getAllBatteryPages($showpage['limit'],$this->parames);
        $parames=$this->parames;
        $uri=$this->makeSearchUrl($this->parames);
        $url='batteryList?'.$uri;
        $nums=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryData($parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($nums));
        $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryData($parames,' LIMIT '.$showpage['limit']);
//            echo '<pre />';
//            var_dump($batteryConfig);die;
            foreach ($arr as $k=>$v){
                switch ($v['battery_status']){
                    case 0:
                        $arr[$k]['battery_status']='已入库';
                    break;
                    case 1  :
                        $arr[$k]['battery_status']='已出库';
                    break;
                    case 2:
                        $arr[$k]['battery_status']='未绑定机柜';
                    break;
                    case 3:
                        $arr[$k]['battery_status']='已绑定机柜';
                    break;
                    case 4:
                        $arr[$k]['battery_status']='损坏';
                    break;
                    case 5:
                        $arr[$k]['battery_status']='返厂';
                    break;
                    case 6:
                        $arr[$k]['battery_status']='检修中';
                    break;
                    case 7:
                        $arr[$k]['battery_status']='退电中';
                        break;
                    case 8:
                        $arr[$k]['battery_status']='带回中';
                        break;
                }

                //电芯
//                switch ($v['battery_cells']){
//                    case 'D':
                         $arr[$k]['battery_cells']=$batteryConfig['battery_cells'][$arr[$k]['battery_cells']];
//                         break;
//                    case 'L':
//                        $arr[$k]['battery_cells']=$batteryConfig['battery_cells'][$arr[$k]['battery_cells']];
//                        break;
//                    case 'F':
//                        $arr[$k]['battery_cells']=$batteryConfig['battery_cells'][$arr[$k]['battery_cells']];
//                        break;
//                        default;
//                }

                //规格
//                switch ($v['specification']){
//                    case '01':
                        $arr[$k]['specification']=$batteryConfig['specification'][$arr[$k]['specification']];
//                        break;
//                    case '02':
//                        $arr[$k]['specification']=$batteryConfig['specification'][$arr[$k]['specification']];
//                        break;
//                    case '31':
//                        $arr[$k]['specification']=$batteryConfig['specification'][$arr[$k]['specification']];
//                        break;
//                    case '32':
//                        $arr[$k]['specification']=$batteryConfig['specification'][$arr[$k]['specification']];
//                        break;
//                    case '71':
//                        $arr[$k]['specification']=$batteryConfig['specification'][$arr[$k]['specification']];
//                        break;
//                    default;
//                }

                //组装厂
//                switch ($v['battery_manufacturer']){
//                    case '05':
                        $arr[$k]['battery_manufacturer']=$batteryConfig['battery_manufacturer'][$arr[$k]['battery_manufacturer']];
//                        break;
//                    case '06':
//                        $arr[$k]['battery_manufacturer']=$batteryConfig['battery_manufacturer'][$arr[$k]['battery_manufacturer']];
//                        break;
//                    case '15':
//                        $arr[$k]['battery_manufacturer']=$batteryConfig['battery_manufacturer'][$arr[$k]['battery_manufacturer']];
//                        break;
//                    case '16':
//                        $arr[$k]['battery_manufacturer']=$batteryConfig['battery_manufacturer'][$arr[$k]['battery_manufacturer']];
//                        break;
//                    case '57':
//                        $arr[$k]['battery_manufacturer']=$batteryConfig['battery_manufacturer'][$arr[$k]['battery_manufacturer']];
//                        break;
//                    default;
//                }
            }
            $this->smarty->assign('arr',$arr);
            $this->smarty->assign('batteryConfig',$batteryConfig);
            $this->smarty->assign('parames',$parames);
            $this->smarty->assign("pages", $showpage['show']);
            $this->smarty->view('battery/list.phtml');
        }   

   /**
    * [BatterySearch 电池页面搜索]
    * @return [type] [description]
    */
   public function BatterySearch(){
    F()->Resource_module->setTitle('电池列表');
    F()->Resource_module->setJsAndCss(array(
        'home_page'
    ), array(
        'main'
    ));
      $parames=$this->parames;
      $uri=$this->makeSearchUrl($this->parames);
      $url='BatterySearch?'.$uri;
      $nums=M_Mysqli_Class('md_lixiang','BatteryModel')->getSearchCountBatteryByAttr($parames);
      $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
      $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->BatteryQuery($parames['select'],' LIMIT '.$showpage['limit']);
      foreach ($arr as $k=>$v){
        switch ($v['battery_status']){
                case 0:
                    $arr[$k]['battery_status']='已入库';
                break;
                case 1  :
                    $arr[$k]['battery_status']='已出库';
                break;
                case 2:
                    $arr[$k]['battery_status']='未绑定机柜';
                break;
                case 3:
                    $arr[$k]['battery_status']='已绑定机柜';
                break;
                case 4:
                    $arr[$k]['battery_status']='损坏';
                break;
                case 5:
                    $arr[$k]['battery_status']='返厂';
                break;
                case 6:
                    $arr[$k]['battery_status']='检修中';
                break;
            }
        }
      $this->smarty->assign('arr',$arr);
      $this->smarty->assign('search',$this->parames['select']);
      $this->smarty->assign("pages", $showpage['show']);
      $this->smarty->view('battery/list.phtml');
   }

    //编辑电池
    public function changeBattery(){
        $this->checkAuth();
        $action=$_SERVER['REQUEST_METHOD'];
        if($action=='POST'){
            $parames=$this->parames;

            //=============================================操作内容记录
            $berforeData=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttrs(['id'=>$parames['id']]);
            $rentStatus=[1=>'闲置中',2=>'已使用'];
            $batteryStatus=[0=>'已入库',1=>'已出库',2=>'未绑定机柜',3=>'已绑定机柜',4=>'损坏',5=>'返厂',6=>'检修中',7=>'退电中',8=>'带回中'];
            //<------------------------------------------------->

            $parames['cabinetName']=trim($parames['cabinetName']);
            if($parames['cabinetName']==NULL){
                        $res['id']=$parames['id'];
                        $res['rent_status']=$parames['rentStatus'];
                        $res['battery_status']=$parames['batteryStatus'];
                        $res['cabinet_id']=empty($parames['cabinet_id'])?null:$parames['cabinet_id'];
                        $res['site_id']=empty($parames['site_id'])?0:$parames['site_id'];
                        if(M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($res)){

                            //==============================================操作内容记录
                            $afterData=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttrs(['id'=>$parames['id']]);
                            $batteryData=$this->arrayNewWornData($afterData,$berforeData);
                            if($batteryData){
                                for($i=0 ; $i<count($batteryData); $i++){
                                    if(isset($batteryData[$i]['clm_name']) && $batteryData[$i]['clm_name']=='rent_status'){
                                        $batteryData[$i]['old_string']=$rentStatus[$batteryData[$i]['old_string']];
                                        $batteryData[$i]['new_string']=$rentStatus[$batteryData[$i]['new_string']];
                                    }
                                    if(isset($batteryData[$i]['clm_name']) && $batteryData[$i]['clm_name']=='battery_status'){
                                        $batteryData[$i]['old_string']=$batteryStatus[$batteryData[$i]['old_string']];
                                        $batteryData[$i]['new_string']=$batteryStatus[$batteryData[$i]['new_string']];
                                    }
                                }
                                $insertData[0]=$batteryData;
                            }else{
                                $insertData='';
                            }

                            $tableName[0]['table_name']='md_battery : 电池表';
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
                            //==============================================

                            $this->msg('更改成功','/batteryList','ok');exit;
                        }else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                            $this->msg('更改失败','/changeBattery?id='.$this->parames['batteryNum'],'error');exit;
                        }
            }else{
                $data['cabinet_name']=$parames['cabinetName'];
                if($arr=M_Mysqli_Class('md_lixiang','CabinetModel')->getBoxByAttr($data)){
                    $res['id']=$parames['id'];
                    $res['cabinet_id']=$arr['id'];
                    $res['rent_status']=$parames['rentStatus'];
                    $res['battery_status']=$parames['batteryStatus'];
                    $res['cabinet_id']=empty($parames['cabinet_id'])?0:$parames['cabinet_id'];
                    $res['site_id']=empty($parames['site_id'])?0:$parames['site_id'];
                    if(M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($res)){

                        //=====================================================操作内容记录
                        $afterData=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttrs(['id'=>$parames['id']]);
                        $batteryData=$this->arrayNewWornData($afterData,$berforeData);
                        if($batteryData){
                            for($i=0 ; $i<count($batteryData); $i++){
                                if(isset($batteryData[$i]['clm_name']) && $batteryData[$i]['clm_name']=='rent_status'){
                                    $batteryData[$i]['old_string']=$rentStatus[$batteryData[$i]['old_string']];
                                    $batteryData[$i]['new_string']=$rentStatus[$batteryData[$i]['new_string']];
                                }
                                if(isset($batteryData[$i]['clm_name']) && $batteryData[$i]['clm_name']=='battery_status'){
                                    $batteryData[$i]['old_string']=$batteryStatus[$batteryData[$i]['old_string']];
                                    $batteryData[$i]['new_string']=$batteryStatus[$batteryData[$i]['new_string']];
                                }
                                if(isset($batteryData[$i]['clm_name']) && $batteryData[$i]['clm_name']=='cabinet_id'){
                                    $batteryData[$i]['old_string']=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$batteryData[$i]['old_string']])['cabinet_number'];
                                    $batteryData[$i]['new_string']=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$batteryData[$i]['new_string']])['cabinet_number'];
                                }
                            }
                            $insertData[0]=$batteryData;
                        }else{
                            $insertData='';
                        }

                        $tableName[0]['table_name']='md_battery : 电池表';
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
                        //======================================================

                        $this->msg('更改成功','/batteryList','ok');exit;
                    }else{
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                        $this->msg('更改失败','/changeBattery?id='.$this->parames['batteryNum'],'error');exit;
                    }
                }else{
                    $this->msg('不存在的机柜名','/changeBattery?id='.$this->parames['batteryNum'],'error');exit;
                }
                //print_r($arr);exit;
            }
        }else{
            F()->Resource_module->setTitle('修改电池');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $parames=$this->parames;
            $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByNumber($parames['id']);
            $res=M_Mysqli_Class('md_lixiang','CabinetModel')->getAllBox();
//            echo '<pre>';
//            print_r($res);die;
            $box=[];
            foreach ($res as $k=>$v){
                $box[$k]['cabinet_number']=$v['cabinet_number'];
                $box[$k]['cabinet_name']=$v['cabinet_name'];
                $box[$k]['id']=$v['id'];
                $box[$k]['site_id']=$v['site_id'];
            }
            //print_r($arr);exit;
            $this->smarty->assign('arr',$arr);   
            $this->smarty->assign('box',$box);
            $this->smarty->view('battery/update.phtml');
            }         
    }

    
    //添加电池
    public function addBattery(){
        $this->checkAuth();
        $action=$_SERVER['REQUEST_METHOD'];
        if($action=='POST'){
            $parames=$this->parames;  
            $this->form_validation->set_data($parames);
            $this->form_validation->set_rules('batteryNum','电池编号','alpha_numeric|exact_length[13]|required');
            $this->form_validation->run();
            if($this->form_validation->run() === FALSE){
                $this->msg($this->form_validation->validation_error(), '/addBattery', 'error');
            }else{
             if($arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBattery($parames['batteryNum'])){
                 if($arr['status']==2){
                     $update=[
                         'battery_num'=>$parames['batteryNum'],
                         'create_time'=>time(),
                         'create_date'=>date("Y-m-d H:i:s",time()),
                         'battery_status'=>0,
                         'rent_status'=>1,
                         'user_id'=>NULL,
                         'binding_time'=>NULL,
                         'bad_time'=>NULL,
                         'status'=>0
                     ];
                     if(M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($update)){

                         //=====================================操作内容记录
                         $insertData[0]=$update;
                         $tableName[0]['table_name']='md_battery : 电池表';
                         $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                         //=====================================操作内容记录

                         $this->msg('添加成功', '/addBattery', 'ok');
                     }else{
                         $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                         $this->msg('服务器繁忙', '/addBattery', 'error');
                     } 
                 }else{
                     $this->msg('此电池已经存在', '/addBattery', 'error');
                 }                                  
                }else{
                    $data['battery_num']=$parames['batteryNum'];
                    if(M_Mysqli_Class('md_lixiang','BatteryModel')->addBattery($data)){

                        //=====================================操作内容记录
                        $insertData[0]=$data;
                        $tableName[0]['table_name']='md_battery : 电池表';
                        $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                        //=====================================操作内容记录

                        $this->msg('添加成功', '/addBattery', 'ok');
                    }else{
                        $this->msg('服务器繁忙', '/addBattery', 'error');
                    }
                }
            }
        }else{            
            $this->smarty->view('battery/insert.phtml');
        }  
    }


    /**
     * 修改电池状态
     */
    public function actionBatteryStatus(){
        $this->checkAuth();
        $beforeData=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttrs(['id'=>$this->parames['id']]);
        $updateCompany=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($this->parames);
        if($updateCompany){

            //==============================================操作内容记录
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['battery_num'=>$beforeData['battery_num'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_battery : 电池表';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //==============================================

            $this->msg('删除成功','/batteryList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('删除失败','/batteryList','error');
        }
    }


    /**
     * 解绑电池
     */
    public function reBattery(){
        $parames=$this->parames;
        if(isset($parames['battery_num'])){
            $parames+=[
                'rent_status'=>1,
                'user_id'=>0
            ];
            $updateCompany=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($parames);
            $data=[
                'br01'=>date('Y-m-d H:i:s',time()),
                'br02'=>'01040053',
                'br03'=>'',
                'br04'=>'',
                'br05'=>$parames['battery_num']
            ];
            get_log()->log_api('<接口测试> #### 接口名：reBattery 作用：魔动调用魔力电池解绑接口参数：'.json_encode($data));
            $result=$this->regInform($data);
            get_log()->log_api('<接口测试> #### 接口名：reBattery 作用：魔动调用魔力电池解绑接口返回值：'.json_encode($result));
            if($result['rt_cd']=='0000'){

                //=============================操作内容记录
                $tableName[0]['table_name']='md_battery : 电池表';
                $insertData[0]=$data;
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                //=============================
                $this->msg('解绑成功','/reBattery','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg($result['rt_msg'],'/reBattery','error');
            }
        }else{
            F()->Resource_module->setTitle('解绑电池');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('battery/reBattery.phtml');
        }
    }

    //单独请求接口
    private function regInform($data){
        $url=ML_URL."d8bdf6c3"; 
       return $this->apiAndData($url,$data);
    }



    //电池入库
    public function backBattery(){
        $this->checkAuth();
        $action=$_SERVER['REQUEST_METHOD'];
        if($action=='POST'){
            $parames=$this->parames;  
            $this->form_validation->set_data($parames);
            $this->form_validation->set_rules('batteryNum','电池编号','alpha_numeric|exact_length[13]|required');
            $this->form_validation->run();
            if($this->form_validation->run() === FALSE){
                $this->msg($this->form_validation->validation_error(), '/backbattery', 'error');
            }else{
                if($parames['backType']==0){
                    if($arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBattery($parames['batteryNum'])){
                     if($arr['status']==2){
                         $update=[
                             'battery_num'=>$parames['batteryNum'],
                             'create_time'=>time(),
                             'create_date'=>date("Y-m-d H:i:s",time()),
                             'battery_status'=>0,
                             'rent_status'=>1,
                             'user_id'=>NULL,
                             'binding_time'=>NULL,
                             'bad_time'=>NULL,
                             'status'=>0
                         ];
                         if(M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($update)){
                             $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                             $this->msg('添加成功', '/backbattery', 'ok');
                         }else{
                             $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                             $this->msg('服务器繁忙', '/backbattery', 'error');
                         } 
                     }else{
                         $this->msg('此电池已经存在', '/backbattery', 'error');
                     }                                  
                    }else{
                        $data['battery_num']=$parames['batteryNum'];
                        if(M_Mysqli_Class('md_lixiang','BatteryModel')->addBattery($data)){
                            $this->msg('添加成功', '/backbattery', 'ok');
                        }else{
                            $this->msg('服务器繁忙', '/backbattery', 'error');
                        }
                    }
                }elseif($parames['backType']==4){
                    if($arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBattery($parames['batteryNum'])){
                        $update=[
                             'battery_num'=>$parames['batteryNum'],
                             'create_time'=>time(),
                             'create_date'=>date("Y-m-d H:i:s",time()),
                             'battery_status'=>4,
                             'rent_status'=>1,
                             'user_id'=>NULL,
                             'binding_time'=>NULL,
                             'bad_time'=>NULL,
                             'cabinet_id'=>NULL,
                             'status'=>0
                         ];
                         if(M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($update)){
                             $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                             $this->msg('操作成功', '/backbattery', 'ok');
                         }else{
                             $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                             $this->msg('操作失败', '/backbattery', 'error');
                         } 
                    }else{
                        $this->msg('无此电池编号记录', '/backbattery', 'error');
                    }
                }elseif($parames['backType']==5){
                    if($arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getBattery($parames['batteryNum'])){
                        $update=[
                             'battery_num'=>$parames['batteryNum'],
                             'create_time'=>time(),
                             'create_date'=>date("Y-m-d H:i:s",time()),
                             'battery_status'=>5,
                             'rent_status'=>1,
                             'user_id'=>NULL,
                             'binding_time'=>NULL,
                             'bad_time'=>NULL,
                             'cabinet_id'=>NULL,
                             'status'=>0
                         ];
                         if(M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($update)){
                             $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                             $this->msg('操作成功', '/backbattery', 'ok');
                         }else{
                             $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                             $this->msg('操作失败', '/backbattery', 'error');
                         } 
                    }else{
                        $this->msg('无此电池编号记录', '/backbattery', 'error');
                    }
                }
            }
        }else{            
            $this->smarty->view('battery/backBattery.phtml');
        }

    }


       /*
        * 电池反馈信息列表
        * */
       public function batteryFeedbackList()
       {
           $this->checkAuth();
           $parames=$this->parames;
           $breakConfig= require_once MAIL_SRC_PATH.'Battery_breakdown_config.php';
           F()->Resource_module->setTitle('电池反馈信息列表');
           F()->Resource_module->setJsAndCss(array(
               'home_page'
           ), array(
               'main'
           ));
           $parames['type']=isset($parames['type'])?$parames['type']:'';
           $breakNums=M_Mysqli_Class('md_lixiang','BreakdownBatteryModel')->getBreakdownBatteryData($parames,'');
           $uri=$this->makeSearchUrl($parames);
           $url = "batteryFeedbackList?".$uri;
           $showpage= $this->page($url,$this->commonDefine['pagesize'],count($breakNums));
           $limit=' LIMIT '.$showpage['limit'];
           $breakDatas=M_Mysqli_Class('md_lixiang','BreakdownBatteryModel')->getBreakdownBatteryData($parames,$limit);
           for($i=0;$i<count($breakDatas);$i++){
               $breakDatas[$i]['brack_type']=explode(',',$breakDatas[$i]['brack_type']);
               for($y=0;$y<count($breakDatas[$i]['brack_type']);$y++){
                  $breakDatas[$i]['brack_type'][$y]=$breakConfig[$breakDatas[$i]['brack_type'][$y]];

               }
               $breakDatas[$i]['brack_type']=implode(',',$breakDatas[$i]['brack_type']);
           }
           $this->smarty->assign('breakConfig',$breakConfig);
           $this->smarty->assign('breakDatas',$breakDatas);
           $this->smarty->assign('parames',$parames);
           $this->smarty->assign("pages", $showpage['show']);
           $this->smarty->view('battery/feedback_list.phtml');
       }

       /*
        * 电池历史log列表
        * */
       public function batteryLogList()
       {
           $this->checkAuth();
           $parames=$this->parames;
           F()->Resource_module->setTitle('电池操作日志列表');
           F()->Resource_module->setJsAndCss(array(
               'home_page'
           ), array(
               'main'
           ));
           $logNums=M_Mysqli_Class('md_lixiang','BatteryLogModel')->getBatteryCount($parames);
           $uri=$this->makeSearchUrl($parames);
           $url = "batteryLogList?".$uri;
           $showpage= $this->page($url,$this->commonDefine['pagesize'],$logNums);
           $pageSize=explode(',',$showpage['limit']);;
           $batteryDatas=M_Mysqli_Class('md_lixiang','BatteryLogModel')->getBatteryLogAll($parames,$pageSize[0]);
           $types=[0=>'借出',1=>'归还',2=>'损坏',3=>'返厂',4=>'检修',5=>'首次使用',6=>'入库',7=>'出库',8=>'检修完成',9=>'绑定机柜',10=>'绑定站点'];
           foreach ($batteryDatas as $k=>$v){
               $batteryDatas[$k]['type']=$types[$v['type']];
           }
           $this->smarty->assign('types',$types);
           $this->smarty->assign('parames',$parames);
           $this->smarty->assign("pages", $showpage['show']);
           $this->smarty->assign("batteryDatas",$batteryDatas);
           $this->smarty->view('battery/batteryLog.phtml');
       }
}
