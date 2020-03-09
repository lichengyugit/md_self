<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}

class storage extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "cangchu");
    }
    
    /**
     * 库存录入
     */
    public function actionStorage(){
        $this->checkAuth();
        if(array_key_exists('name', $this->parames)){
            $parames=$this->parames;

            $meterial=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->addMeterial($parames);

            if($meterial){
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                $this->msg('保存成功','/actionStorage','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                $this->msg('保存失败','/actionStorage','error');
            }
        }else{
            F()->Resource_module->setTitle('新建耗材类目');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('storage/entry.phtml');
        }
    }


    /**
     * 库存清单
     */
    public function storageList(){
        $this->checkAuth();
        F()->Resource_module->setTitle('库存列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/storageList";
        $nums=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->getMeterConfigNumByAttr([]);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->selectMeterial([],$showpage['limit']);
        $this->smarty->assign('arr',$arr);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('storage/list.phtml');
    }


    /*
     * 电池出库
     */
    public function batteryout(){
        $this->checkAuth();
        if(array_key_exists('name', $this->parames)){
            $parames=$this->parames;
            $meterial=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->addMeterial($parames);
            if($meterial){
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                $this->msg('保存成功','/actionStorage','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('保存失败','/actionStorage','error');
            }

        }else{
            F()->Resource_module->setTitle('电池出库');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('storage/batteryout.phtml');
        }
    }


    /*
     * 人员 站点信息接口
     */
    public function getSiteUserInfo(){
        $parames=$this->parames;
        if($parames['type']==2){
            $site=M_Mysqli_Class('md_lixiang','SiteModel')->tableQuery(['select'=>$parames['select']],'LIMIT 0,20');
            $html='';
            foreach ($site as $k => $v) {
                $html.="<option title=".$v['location']." value=".$v['id'].">".$v['site_name']."</option>";
            }
            $this->setOutPut($html);
        }elseif($parames['type']==1){
            $user=M_Mysqli_Class('md_lixiang','AdminModel')->selectAdminInsider($parames['select']);
            $html='';
            foreach ($user as $k => $v) {
                $html.="<option value=".$v['id'].">".$v['user_name']."</option>";
            }
            $this->setOutPut($html);
        }else{
            $this->setOutPut('错误');
        }
    }


    /*
     * 勘测需出库物料
     */
    public function surveybatcab(){
        $this->checkAuth();
        F()->Resource_module->setTitle('勘测需出库物料');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        if(array_key_exists('siteid',$parames)){
            $siteid=$parames['siteid'];
            $site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfo(['id'=>$siteid]);
            $project=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$site[0]['id']]);
            $account=M_Mysqli_Class('md_lixiang','StorageAccountModel')->selectAccount(['project_id'=>$project['id']]);
            $this->smarty->assign('storage',$account);
            $this->smarty->assign('site',$site[0]);
            $this->smarty->assign('siteid',$siteid);
            $this->smarty->view('storage/surveybatcab.phtml');
        }else{
            //站点表状态为勘测审核通过数据
            $site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfo(['state'=>2]);
            $this->smarty->assign('sites',$site);
            $this->smarty->view('storage/surveybatcab.phtml');
        }
    }


    /*
     * 机柜扫码出库
     */
    public function scan(){
        $parames=$this->parames;
        if(isset($parames['cabinet_type']) && isset($parames['cabinet_site'])){
            $this->input->set_cookie('cabinet_site',$this->parames['cabinet_site'],86400);
            $this->input->set_cookie('cabinet_type',$this->parames['cabinet_type'],86400);
        }
        F()->Resource_module->setTitle('机柜出库');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('storage/scan.phtml');
    }


    /*
     * 电池扫码出库
     */
    public function batscan(){
        $parames=$this->parames;
        if(isset($parames['battery_site'])){
            $this->input->set_cookie('battery_site',$this->parames['battery_site'],86400);
        }
        F()->Resource_module->setTitle('电池出库');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('storage/batteryout.phtml');
    }


    /**
     * 机柜扫码出库操作
     */
    public function outCabinet(){
        $parames=$this->parames;
        $orderData=[
            'order_sn'=>$this->createStorageAllotNum(),
            'order_platform'=>2,
            'type'=>1,
            'order_status'=>2,
            'attr_type'=>4,
            'site_id'=>$parames['siteid'],
            'site_name'=>$parames['site_name'],
            'creator_id'=>$_SESSION['user_id'],
            'creator_name'=>$_SESSION['userName']
        ];
        $order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->addOrder($orderData);
        $arr = explode("\n",$parames['num']);
        $recordData=[];
        foreach ($arr as $key => $value) {
           //判断机柜编码是否存在返回false或true
            $inspect=M_Mysqli_Class('md_lixiang','CabinetModel')->inspectCabinetNum($value,$parames['cabinet_type']);
            if($inspect){
                //绑定站点id到电池表 返回false或true
                $bind=M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinet(['cabinet_number'=>$value,'site_id'=>$parames['siteid'],'operation_type'=>1]);
                $recordData[]=[
                    'order_id'=>$order,
                    'code'=>$value,
                    'state'=>1,
                    'type'=>1,
                    'site_id'=>$parames['siteid'],
                    'site_name'=>$parames['site_name']
                ];
            }else{
                $this->setOutPut('机柜编号不存在库存中或机柜类型错误');die;
            }
        }
        $Record=M_Mysqli_Class('md_lixiang','StorageSurveyRecordModel')->addBatchRecord($recordData);
        if($bind){
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                $this->setOutPut('出库成功');die;
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->setOutPut('该电池编号已扫描或更新失败');die;
            }
        $this->setOutPut('成功');
        //绑定电池记录和机柜记录
    }
            
            
            





    /*
     * 电池扫码出库操作
     */
    public function outBattery(){
        /*if($_COOKIE['battery_site']==NULL){
         $this->msg('请先选择出库站点','/surveybatcab','error');exit;
        }
        $parames=$this->parames;
        echo '<pre>';
        print_r($parames);die;
        $parames['cabinet_id']=$_COOKIE['battery_site'];
        //1.更改所属机柜id 
        if(!M_Mysqli_Class('md_lixiang','BatteryModel')->getBattery($parames['battery_num'])){
            $this->msg('此电池编号不存在','/batscan','error');exit;
        }
        $update=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($parames);
        if($update>0){
            $this->msg('添加成功','/batscan','ok');
        }else{
            $this->msg('添加失败','/batscan','error');
        }*/
        $parames=$this->parames;
        $parames['battery_num'] = array_unique ($parames['battery_num']); 
        $countNum=count($parames['battery_num']);
        $errorNum=0;
        if (array_key_exists('outType', $parames)) {
            if($parames['outType']==1){
                //第一步之前：需要确定该电池没有被分配派单任务
                foreach ($parames['battery_num'] as $key => $val) {
                    $examine=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->inspectBatteryNum($val);
                    if($examine){
                        $msg.='第'.($key+1).'条  电池编号'.$val.' 已经在派单'.'<br>';
                        $errorNum+=1;
                        unset($parames['battery_num'][$key]);
                    }else{
                        continue;
                    }
                }
                if($errorNum >= $countNum){
                     $this->msg('操作完成'.'<br>'.$msg,'','ok');die;
                }
                //第一步：创建订单
                $userid=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttrOne(['id'=>$parames['outUser']]);
                $data=[
                    'order_sn'=>$this->createStorageAllotNum(),
                    'order_platform'=>1,
                    'creator_id'=>$_SESSION['user_id'],//订单创建人id
                    'type'=>2,//订单发货类型 1勘测 2电池调拨
                    'attr_type'=>$parames['outType'],//出库类型
                    'order_status'=>0,//订单状态 0已派单 1已完成
                    'user_id'=>$userid['id'],//调拨到人员id
                    'user_name'=>$userid['user_name'],//调拨到人员名称
                    'creator_name'=>$_SESSION['userName'],//订单创建人名称
                ];
                $order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->addOrder($data);
                if($order){
                    //第二步：循环提交电池信息 绑定入订单表
                    foreach ($parames['battery_num'] as $k => $v) {
                        //判断电池编码是否存在返回false或true
                        $inspect=M_Mysqli_Class('md_lixiang','BatteryModel')->inspectBatteryNum($v);
                        if ($inspect) {
                            //创建完订单之后
                            //根据创建的订单id 把订单的详情信息 电池信息绑定
                            $data=[
                                'order_id'=>$order,//订单id
                                'battery_num'=>$v,//调拨电池id
                                'details_status'=>0,
                                'user_id'=>$userid['id'],//调拨到人员id
                                'user_name'=>$userid['user_name'],//调拨到人员名称
                            ];
                            $upBattery=[
                                'battery_num'=>$v,//修改电池状态
                                'battery_status'=>1//已出库
                            ];
                            M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($upBattery);
                            $result=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->addRecord($data);
                            if($result){
                                continue;
                            }else{
                                $msg.='第'.($k+1).'条 电池编号 '.$v.'出库失败'.'<br>';
                                $errorNum+=1;
                            }
                        }else{
                            $msg.='第'.($k+1).'条 电池编号 '.$v.' 不存在库存中'.'<br>';
                            $errorNum+=1;
                        }
                    }
                    if($errorNum >= $countNum){
                        M_Mysqli_Class('md_lixiang','StorageOrderModel')->delectInfoByAttr(['id'=>$order]);
                        $this->msg('操作完成'.'<br>'.$msg,'','ok');die;
                    }
                    //第三步：判断是否有 有问题数据 如果有 则停留跳转页面 否则操作完成跳转
                    if($msg==''){
                        $this->msg('操作完成','/batteryout','ok');die;
                    }else{
                        $this->msg('操作完成'.'<br>'.$msg,'','ok');die;
                    }
                }else{
                    $this->msg('服务器繁忙','/batteryout','error');
                }
            }elseif($parames['outType']==2){
                //第一步之前：需要确定该电池没有被分配派单任务
                foreach ($parames['battery_num'] as $key => $val) {
                    $examine=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->inspectBatteryNum($val);
                    if($examine){
                        $msg.='第'.($key+1).'条  电池编号'.$val.' 已经在派单'.'<br>';
                        $errorNum+=1;
                        unset($parames['battery_num'][$key]);
                    }else{
                        continue;
                    }
                }
                if($errorNum >= $countNum){
                     $this->msg('操作完成'.'<br>'.$msg,'','ok');die;
                }
                //第一步：创建订单
                $siteid=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$parames['outSite']]);
                $data=[
                    'order_sn'=>$this->createStorageAllotNum(),
                    'order_platform'=>1,
                    'creator_id'=>$_SESSION['user_id'],//订单创建人id
                    'type'=>2,//订单发货类型 1勘测 2电池调拨
                    'attr_type'=>$parames['outType'],//出库类型
                    'order_status'=>1,//订单状态 0已派单 1已完成
                    'site_id'=>$siteid['id'],//调拨到人员id
                    'site_name'=>$siteid['site_name'],//调拨到人员名称
                    'creator_name'=>$_SESSION['userName'],//订单创建人名称
                ];
                $order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->addOrder($data);
                //第二步：循环提交电池信息 电池表更新绑定站点信息
                if($order){
                    foreach ($parames['battery_num'] as $k => $v) {
                        //判断电池编码是否存在返回false或true
                        $inspect=M_Mysqli_Class('md_lixiang','BatteryModel')->inspectBatteryNum($v);
                        if($inspect){
                            //绑定记录到记录表
                            $recordData=[
                                'order_id'=>$order,//订单id
                                'battery_num'=>$v,//调拨电池id
                                'details_status'=>2,
                                'site_id'=>$siteid['id'],//调拨到人员id
                                'site_name'=>$siteid['site_name'],//调拨到人员名称
                            ];
                            $record=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->addRecord($recordData);
                            $upBattery=[
                                'battery_num'=>$v,//修改电池状态
                                'battery_status'=>2//未绑定机柜
                            ];
                            M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($upBattery);
                            //绑定站点id到电池表 返回false或true
                            $bind=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery(['battery_num'=>$v,'site_id'=>$parames['outSite']]);
                            $logData=[
                                'user_id'=>$_SESSION['user_id'],
                                'user_name'=>$_SESSION['user_name'],
                                'type'=>10,
                                'msg'=>'后台直接调拨到站点'
                            ];
                            M_Mysqli_Class('md_lixiang','BatteryLogModel')->addLog($logData);
                            if($record){
                                continue;
                            }else{
                                 $msg.='第'.($k+1).'条 电池编号 '.$v.' 已扫描或更新失败'.'<br>';
                                 $errorNum+=1;
                            }
                        }else{
                            $msg.='第'.($k+1).'条 电池编号 '.$v.' 不存在库存中'.'<br>';
                            $errorNum+=1;
                        }
                    }
                }else{
                    $this->msg('服务器繁忙','/batteryout','error');
                }
                if($errorNum >= $countNum){
                    M_Mysqli_Class('md_lixiang','StorageOrderModel')->delectInfoByAttr(['id'=>$order]);
                    $this->msg('操作完成'.'<br>'.$msg,'','ok');die;
                }
                //第三步：判断是否有 有问题数据 如果有 则停留跳转页面 否则操作完成跳转
                if($msg==''){
                    $this->msg('操作完成','/batteryout','ok');die;
                }else{
                    $this->msg('操作完成'.'<br>'.$msg,'','ok');die;
                }
            }
        }else{
            $this->msg('未知错误','/batteryout','error');die;
        }
    }




    /*
     *  电池退回入库审核页面
     */
    public function backBatteryAudit(){
        F()->Resource_module->setTitle('调拨电池退回入库审核');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('storage/backBatteryAudit.phtml');
    }

    /*
     *  电池调拨带回入库审核页面
     */
    public function backAwayBattery(){
        F()->Resource_module->setTitle('电池带回入库审核');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('storage/backAwayBattery.phtml');
    }






    /*
     * 耗材扫码出库操作
     */
    public function outCons(){
        /*if($_COOKIE['battery_site']==NULL){
            $this->msg('请先选择出库站点','/surveybatcab','error');exit;
        }
        $parames=$this->parames;
        $parames['cabinet_id']=$_COOKIE['battery_site'];
        //1.更改所属机柜id 
        if(!M_Mysqli_Class('md_lixiang','BatteryModel')->getBattery($parames['battery_num'])){
            $this->msg('此电池编号不存在','/batscan','error');exit;
        }
        $update=M_Mysqli_Class('md_lixiang','BatteryModel')->updateBattery($parames);
        if($update>0){
            $this->msg('添加成功','/batscan','ok');
        }else{
            $this->msg('添加失败','/batscan','error');
        }*/
        $parames=$this->parames;
        $arr=explode('&',$parames['accon']);
        unset($arr[0]);
        foreach ($arr as $k => $v) {
            $data=explode('=',$arr[$k]);
            //更新出库库存
            $bind=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->updateMeterialNum($data[1],$data[0]);

            if($bind>0){
                //做记录
                $insert=M_Mysqli_Class('md_lixiang','StorageRecordModel')->addRecord(['code'=>$data[0],'site_id'=>$parames['siteid'],'num'=>$data[1]]);
                if($insert>0){
                    continue;
                }else{
                    $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                    $this->setOutPut('更新失败');die;
                }
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->setOutPut('该耗材编码已扫描或更新失败');die;
            }
        }
        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
       $this->setOutPut('出库成功');die;
    }




    /**
     * [StorageWarehousing description]
     */
    public function StorageWarehousing(){
        F()->Resource_module->setTitle('库存入库');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        if(array_key_exists('id',$parames)){
            $arr=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->selectMeterialNot($parames);
            $this->smarty->assign('arr',$arr[0]);
            $this->smarty->view('storage/storage_warehousing.phtml');
        }else{
           //更新出库库存 返回影响行数
           $data=M_Mysqli_Class('md_lixiang','StorageMeterialModel')->updateMeterialNumPlus($parames['num'],$parames['consid']);
           if($data>0){
               $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
                echo '入库成功';
           }else{
               $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                echo '入库失败';
           }
        }
    }



    /**
     * [surveyOutRecord 耗材出库记录]
     * @return [type] [description]
     */
    public function surveyOutRecord(){
        F()->Resource_module->setTitle('耗材出库明细');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/surveyOutRecord";
        $sum=M_Mysqli_Class('md_lixiang','StorageRecordModel')->categorySum();
        if(!empty($sum)){
            $key = array_search(max($sum),$sum); 
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$key+1);
            $str='';
            foreach ($sum as $key => $value) {
                $str.=$value['site_id'].',';
            }
            $str=substr($str,0,strlen($str)-1);
            $arr=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteWhereIn($str);
            
        }else{
            $arr=0;
            $showpage=$this->page($url,$this->commonDefine['pagesize'],0);
        }
       
        $this->smarty->assign('arr',$arr);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('storage/record.phtml');
    }



    /**
     * 耗材出库记录详情接口
     */
    public function stoDetails(){
        $parames=$this->parames;
        $arr=M_Mysqli_Class('md_lixiang','StorageRecordModel')->stoDetails($parames['id']);
        $html='';
        $html.='<table class="layui-table" lay-size="sm">

                                    <thead>
                                      <tr>
                                        <th>耗材名称</th>
                                        <th>耗材规格</th>
                                        <th>耗材编码</th>
                                        <th>出库数量</th>
                                        <th>出库时间</th>
                                      </tr> 
                                    </thead>
                                    <tbody>';

        foreach ($arr as $key => $value) {
            $html.='<tr><td>'.$value['name'].'</td><td>'.$value['specifications'].'</td><td>'.$value['coding'].'</td><td>'.$value['num'].'</td><td>'.$value['create_date'].'</td></tr>';
        }
        $html.='</tbody></table>';
        $this->setOutPut($html);die;
    }


    /**
     * 电池出库订单记录详情接口
     */
    public function corfimDeles(){
        $parames=$this->parames;
        $data=[
            'order_id'=>$parames['id']
        ];
        $arr=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectRule($data);
        $html='';
        $html.='<table class="layui-table" lay-size="sm">
                                    <thead>
                                      <tr>
                                        <th>电池编号</th>
                                        <th>调拨类型</th>
                                        <th>电池状态</th>
                                        <th>调拨到站点名称</th>
                                        <th>调拨到机柜名称</th>
                                        <th>记录创建时间</th>
                                      </tr> 
                                    </thead>
                                    <tbody>';
        foreach ($arr as $k => $v) {
            $html.='<tr>
                        <td>'.$v['battery_num'].'</td>';
            switch ( $v['attr_status'] ) {
                case '0':
                    $html.='<td>正常</td>';
                    break;
                case '1':
                    $html.='<td>故障带回</td>';
                    break;
                case '2':
                    $html.='<td>普通带回</td>';
                    break;
                case '3':
                    $html.='<td>调拨到机柜</td>';
                    break;
                case '4':
                    $html.='<td>调拨到站点</td>';
                    break;
                case '5':
                    $html.='<td>正常</td>';
                    break;
                default:
                    $html.='<td>未知</td>';
                    break;
            }
            switch ( $v['details_status'] ) {
                case '0':
                    $html.='<td style="color:#FF0000">派单中</td>';
                    break;
                case '1':
                    $html.='<td>已完成</td>';
                    break;
                case '2':
                    $html.='<td>直接调拨</td>';
                    break;
                case '3':
                    $html.='<td>退回仓库审核中</td>';
                    break;
                case '4':
                    $html.='<td>退回仓库</td>';
                    break;
                case '5':
                    $html.='<td>电池带回中</td>';
                    break;
                case '6':
                    $html.='<td>电池已带回</td>';
                    break;
                default:
                    $html.='<td>未知</td>';
                    break;
            }
            $html.='<td>'.$v['site_name'].'</td>
                    <td>'.$v['cabinet_name'].'</td>
                    <td>'.$v['create_date'].'</td>';
            $html.='</tr>';
        }
        $html.='</tbody></table>';
        $this->setOutPut($html);die;
    }




    /**
     * 电池调拨记录
     */
    public function batteryAllotRecord(){
        F()->Resource_module->setTitle('电池调拨记录');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('storage/batteryAllotRecord.phtml');
    }

  
    //后台电池订单调拨记录接口
    public function AdminBatteryAllotRecord(){
        $parames=$this->parames;
        $data=[
            'type'=>2
        ];
        $url='AjaxAdminBatteryAllotRecord';
        $nums=M_Mysqli_Class('md_lixiang','StorageOrderModel')->getNumByAttr($data);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageOrderModel')->QueryOrderInformation($data,$showpage['limit']);
       

        foreach ($arr as $k => $v) {
            if($v['attr_type']==1){
                $arr[$k]['synthesize_name']=$v['user_name'];
            }elseif($v['attr_type']==2){
                $arr[$k]['synthesize_name']=$v['site_name'];
            }
        }
        $arr['arr']=$arr;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }


    //电池退回仓库列表接口
    public function backBatteryAuditList(){
        $parames=$this->parames;
        $data=[
            'details_status'=>3
        ];
        $url='AjaxBackBatteryAuditList';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getNumByAttr($data);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectAllotRecord($data,$showpage['limit']);
        $arr['arr']=$arr;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }
    //电池退回仓库已审核列表接口
    public function backBatteryAlreadyList(){
        $parames=$this->parames;
        $data=[
            'details_status'=>4
        ];
        $url='AjaxBackBatteryAlreadyList';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getNumByAttr($data);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectAllotRecord($data,$showpage['limit']);
        $arr['arr']=$arr;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }

    //电池带回仓库待审核列表接口
    public function backBatteryAwayList(){
        $parames=$this->parames;
        $data=[
            'details_status'=>5
        ];
        $url='backBatteryAwayList';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getNumByAttr($data);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectAllotRecord($data,$showpage['limit']);
        $arr['arr']=$arr;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }
    //电池已带回仓库列表接口
    public function backBatteryAwayAlreadyList(){
        $parames=$this->parames;
        $data=[
            'details_status'=>6
        ];
        $url='backBatteryAwayAlreadyList';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getNumByAttr($data);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectAllotRecord($data,$showpage['limit']);
        $arr['arr']=$arr;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }

    //电池退回同意接口
    public function backBatteryPass(){
        $parames=$this->parames;
        $data=[
            'details_status'=>4
        ];
        $where=[
            'id'=>$parames['id'],
            'details_status'=>3,
        ];

        M_Mysqli_Class('md_lixiang','BatteryModel')->updateWheresBattery(['battery_status'=>0],['battery_num'=>$parames['battery_num']]);
        $upStatus=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->updateWheresRecord($data,$where);
        //正在派送的订单数据们
        $order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->QueryUserOrder($parames['user_id']);
        foreach ($order as $k => $v) {
            $result=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectRule(['details_status'=>0,'order_id'=>$v['id']]);
            if(array_key_exists('0', $result)){
                continue;
            }else{
                $upOrder=[
                    'order_status'=>1
                ];
                $whereOd=[
                    'id'=>$v['id']
                ];
                //更新订单表 订单已完成、
                M_Mysqli_Class('md_lixiang','StorageOrderModel')->updateWheresStorageOrder($upOrder,$whereOd);
            }
        }
        if($upStatus){
            $this->setOutPut('操作完成');
        }else{
            $this->setOutPut('操作失败');
        }
    }

    //电池退回同意接口
    public function backBatteryAwayPass(){
        $parames=$this->parames;
        $batteryData=[
            'site_id'=>null,    //站点id
            'cabinet_id'=>null,    //机柜id
            'rent_status'=>1,   //1.未使用 2.使用中
            'user_id'=>0,       //绑定用户
            'battery_status'=>0 //电池状态
        ];
        $batteryWhere=[
            'battery_num'=>$parames['battery_num']
        ];
        $reBattery=M_Mysqli_Class('md_lixiang','BatteryModel')->updateWheresBattery($batteryData,$batteryWhere);
        $data=[
            'details_status'=>6
        ];
        $where=[
            'id'=>$parames['id'],
        ];
        $upStatus=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->updateWheresRecord($data,$where);
        if($upStatus){
            $this->setOutPut('操作完成');
        }else{
            $this->setOutPut('操作失败');
        }
    }


    //电池退回取消接口
    public function backBatteryNoPass(){
        $parames=$this->parames;
        $data=[
            'details_status'=>0
        ];
        $where=[
            'id'=>$parames['id'],
            'details_status'=>3,
        ];
        M_Mysqli_Class('md_lixiang','BatteryModel')->updateWheresBattery(['battery_status'=>1],['battery_num'=>$parames['battery_num']]);
        $upStatus=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->updateWheresRecord($data,$where);
        //正在派送的订单数据们
        $order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->QueryUserOrder($parames['user_id']);
        if($upStatus){
            $this->setOutPut('退回已取消');
        }else{
            $this->setOutPut('操作失败');
        }
    }



    //电池调拨详情接口
    public function PhoneBatteryAllotRecord(){
        $parames=$this->parames;
        $data=[
            1=>1
        ];
        $url='AjaxPhoneBatteryAllotRecord';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getNumByAttr($data);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->selectAllotRecord($data,$showpage['limit']);
        $arr['arr']=$arr;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }



    //调拨记录搜索接口
    public function AdminBatteryAllotSearch(){
        $parames=$this->parames;
        if(!empty($parames['time'])){
            $str=preg_split('/\s-\s/',$parames['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $time=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
        }else{
            $time='';
        }
        switch ($parames['tag']) {
            case '0':
                if($parames['doType']=='x'){
                    $parames['doType']='';
                }else{
                    $parames['doType']=' AND attr_type = '.$parames['doType'];//$parames['doType'] 是 0 1 2 3 4搜索 状态的数字
                }

                if($parames['doStatus']=='x'){
                    $parames['doStatus']='';
                }else{
                    $parames['doStatus']=' AND order_status = '.$parames['doStatus'];//$parames['doStatus'] 是 0 1 2 3 4搜索 状态的数字
                }
                $url='AjaxActionSelectA';
                $nums=M_Mysqli_Class('md_lixiang','StorageOrderModel')->getAdminAllotDimSearchNum($parames['doType'],$parames['doStatus'],$time,$parames['search']);
                $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
                $result=M_Mysqli_Class('md_lixiang','StorageOrderModel')->AdminAllotDimSearch($parames['doType'],$parames['doStatus'],$time,$parames['search'],$showpage['limit']);
                foreach ($result as $k => $v) {
                    if($v['attr_type']==1){
                        $result[$k]['synthesize_name']=$v['user_name'];
                    }elseif($v['attr_type']==2){
                        $result[$k]['synthesize_name']=$v['site_name'];
                    }
                }
                $arr['arr']=$result;
                $arr['one']= $showpage['show'];
                return $this->setOutPut($arr);
                break;
            case '1':
                if($parames['doStatus']=='x'){
                    $parames['doStatus']='';
                }else{
                    $parames['doStatus']=' AND details_status = '.$parames['doStatus'];//$parames['doStatus'] 是 0 1 2 3 4搜索 状态的数字
                }
                $url='AjaxActionSelectB';
                $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getAdminAllotDimSearchNum($parames['doStatus'],$time,$parames['search']);
                $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
                $result=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->AdminAllotDimSearch($parames['doStatus'],$time,$parames['search'],$showpage['limit']);
                $arr['arr']=$result;
                $arr['one']= $showpage['show'];
                return $this->setOutPut($arr);
                break;
            default:
                return '未知错误';
                break;
        }
    }





    //电池退回仓库搜索接口
    public function BackBatterySearch(){
        $parames=$this->parames;
        $time='';
        $parames['doStatus']=' AND details_status = 3';
        $url='AjaxBackBatterySearch';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getAdminAllotDimSearchNum($parames['doStatus'],$time,$parames['search']);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $result=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->AdminAllotDimSearch($parames['doStatus'],$time,$parames['search'],$showpage['limit']);
        $arr['arr']=$result;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }

    //电池退回仓库搜索接口
    public function BackBatteryAwaySearch(){
        $parames=$this->parames;
        $time='';
        $parames['doStatus']=' AND details_status = 5';
        $url='AjaxBackBatteryAwaySearch';
        $nums=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->getAdminAllotDimSearchNum($parames['doStatus'],$time,$parames['search']);
        $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
        $result=M_Mysqli_Class('md_lixiang','StorageBatteryRecordModel')->AdminAllotDimSearch($parames['doStatus'],$time,$parames['search'],$showpage['limit']);
        $arr['arr']=$result;
        $arr['one']= $showpage['show'];
        return $this->setOutPut($arr);
    }



    //大B端勘测系统出库列表
    public function blocConsOutbound(){
        F()->Resource_module->setTitle('大B端勘测系统出库列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $siteData=[
            'od.order_platform'=>2,
            //'st.operation_type'=>2,
            'od.type'=>1
        ];
        $url = "/blocConsOutbound";
        $nums=M_Mysqli_Class('md_lixiang','SiteModel')->getConsOutboundNum($siteData);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        //查看站点和耗材订单 显示出库站点名称 所需机柜数量 电池数量 出库时间 完成状态等
        $site=M_Mysqli_Class('md_lixiang','SiteModel')->getConsOutbound($showpage['limit'],$siteData);
        //点击出库 进入出库页面 进行耗材出库操作 
        //出库结束后 详情页面
        $this->smarty->assign('arr',$site);
        $this->smarty->assign('pages',$showpage['show']);
        $this->smarty->view('storage/sharedConsOutbound.phtml');
    }



    //大B端耗材出库页面
    public function blocConsOutboundPage(){
        $parames=$this->parames;
        if(array_key_exists('outUser',$parames)){
            //需要信息
            //订单id
            //机柜编码 和电池编码
            //调拨给人员id 
            //调拨给人员姓名
            //调拨到站点id
            //调拨到站点名称
            /*
                需要
                绑定记录到订单下
                修改订单状态
                修改该站点pro表的状态 为待施工的状态
            */
            $data['create_date']=date("Y-m-d H:i:s",time());
            $data['create_time']=time();
            $recordData=[];
            if(array_key_exists('battery_num',$parames)){
                foreach ($parames['battery_num'] as $k => $v) {
                    $recordData[]=[
                        'order_id'=>$parames['order_id'],
                        'code'=>$v,
                        'state'=>2,
                        'type'=>2,
                        'user_id'=>$parames['outUser'],
                        'user_name'=>$parames['user_name'],
                        'site_id'=>$parames['site_id'],
                        'site_name'=>$parames['site_name'],
                        'create_time'=>$data['create_time'],
                        'create_date'=> $data['create_date']
                    ];
                }
            }
            if(array_key_exists('site_code',$parames)){
                foreach ($parames['site_code'] as $k => $v) {
                    $recordData[]=[
                        'order_id'=>$parames['order_id'],
                        'code'=>$v,
                        'state'=>1,
                        'type'=>1,
                        'user_id'=>$parames['outUser'],
                        'user_name'=>$parames['user_name'],
                        'site_id'=>0,
                        'site_name'=>'',
                        'create_time'=>$data['create_time'],
                        'create_date'=> $data['create_date']
                    ];
                }
            }
            //绑定电池记录和机柜记录
            $Record=M_Mysqli_Class('md_lixiang','StorageSurveyRecordModel')->addBatchRecord($recordData);
            $orderData=[
                'order_status'=>2
            ];
            $orderWhere=[
                'id'=>$parames['order_id']
            ];
            $order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->updateWheresStorageOrder($orderData,$orderWhere);
            $projectData=[
                'audit'=>1
            ];
            $projectWhere=[
                'site_id'=>$parames['site_id']
            ];
            $project=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState($projectData,$projectWhere);
            if($project && $Record){
                $this->msg('出库完成','/blocConsOutbound','ok');
            }else{
                $this->msg('出库失败','/blocConsOutbound','error');
            }
        }else{
            F()->Resource_module->setTitle('大B端勘测系统出库页面');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$parames['site_id']]);
            $site['order_status']=$parames['order_status'];
            $site['open_time']=date('Y-m-d H:i:s',$site['open_time']);
            $this->smarty->assign('parames',$parames);
            $this->smarty->assign('arr',$site);
            $this->smarty->view('storage/sharedConsOutboundPage.phtml');
        }
    }



    /**
     * 大B端勘测系统出库记录详情接口
     */
    public function blocConsDetails(){
        $parames=$this->parames;
        $data=[
            'od.id' => $parames['id']
        ];
        $arr=M_Mysqli_Class('md_lixiang','StorageSurveyRecordModel')->selectSurveyRecord($data);
        $html='';
        $html.='<table class="layui-table" lay-size="sm">
                                    <thead>
                                      <tr>
                                        <th>耗材名称</th>
                                        <th>耗材编码</th>
                                        <th>调拨人员</th>
                                        <th>调拨到站点</th>
                                        <th>出库时间</th>
                                      </tr> 
                                    </thead>
                                    <tbody>';
        foreach ($arr as $key => $value) {
            $html.='<tr><td>'.($value['type']==1?'机柜':'电池').'</td><td>'.$value['code'].'</td><td>'.$value['user_name'].'</td><td>'.$value['site_name'].'</td><td>'.$value['create_date'].'</td></tr>';
        }
        $html.='</tbody></table>';
        $this->setOutPut($html);die;
    }






    /*
     *  勘测系统耗材出库电池验证
     */
    public function batteryValidation(){
        $parames=$this->parames;
        $inspect=M_Mysqli_Class('md_lixiang','BatteryModel')->inspectBatteryNum($parames['battery']);
        if($inspect){
            $this->setOutPut('正确');die;
        }else{
            $this->setOutPut('错误');die;
        }
    }

    /*
     *  勘测系统耗材出库机柜验证
     */
    public function cabinetValidation(){
        $parames=$this->parames;
        $inspect=M_Mysqli_Class('md_lixiang','CabinetModel')->inspectCabinetNum($parames['cabinet'],1);
        if($inspect){
            $this->setOutPut('正确');die;
        }else{
            $this->setOutPut('错误');die;
        }
    }

    /*
     *  电池调拨出库 验证电池编号是否正确 验证电池是否是故障电池
     */
    public function batteryFaultValidation(){
        $parames=$this->parames;
        $inspect=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttrs(['battery_num'=>$parames['battery']]);
        if($inspect){
            $this->setOutPut($inspect);die;
        }else{
            $this->setOutPut('错误');die;
        }
    }










}







