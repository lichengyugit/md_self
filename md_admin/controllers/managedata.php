<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}

class managedata extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "managedata");
    }

    /**
     * 机柜数据统计管理列表
     */
    public function cabinetStaticList()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('机柜数据统计管理');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $companyData    =M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
        $this->smarty->assign("companyData", $companyData);
        $this->smarty->view('managedata/list.phtml');
    }


    /**
     * 机柜数据统计管理列表
     * @parames  cabinet_number       机柜编号
     * @parames  cabinet_type         机柜类型
     * @parames  opration_type        业务类型
     * @parames  date_type            时间类型
     * @parames  year_date            年份
     * @parames  month_date           月份
     * @parames  currentPage          分页当前页
     *
     * @return   data[
     *               sum[                     (全部机柜总交易)
     *                   sum                  交易总次数
     *                   pay_num              交易总金额
     *                   pay_ment_num0        月卡交易总次数
     *                   pay_ment_num1        微信支付总次数
     *                   pay_ment_num2        余额交易总次数
     *                   pay_ment_num3        满次交易总次数
     *                   pay_num0             月卡交易总金额
     *                   pay_num1             微信支付总金额
     *                   pay_num2             余额支付总金额
     *                   pay_num3             满次交易总金额
     *                  ]
     *           parames[                     (下拉框和输入框的值)
     *                   cabinet_number       机柜编号
     *                   cabinet_type         机柜类型
     *                   opration_type        业务类型
     *                   date_type            时间类型
     *                   year_date            年份
     *                   month_date           月份
     *                  ]
     *     cabinetDatas[                      (每台机柜的交易数据)
     *                  cabinet_number         机柜编号
     *                  cabinet_name           机柜名称
     *                  cabinet_type           机柜类型
     *                  opration_type          业务类型
     *                  quantity_number        机柜交易总数
     *                  pay_num                交易金额总数
     *                  pay_ment0              月卡交易数量
     *                  pay_ment1              微信支付交易数量
     *                  pay_ment2              余额支付交易数量
     *                  pay_ment3              面次免单交易数量
     *                  pay0                   月卡交易金额
     *                  pay1                   微信支付金额
     *                  pay2                   余额交易金额
     *                  pay3                   满次免单交易金额
     *                 ]
     *               ]
     */
    public function cabinetDataList()
    {
        $this->checkAuth();
            if(empty($this->parames['date_type']) && empty($this->parames['create_time'])){
                //当天0点
                $today = date("Ymd", time());
                $startDate = strtotime($today);
                //当天 23:59:59
                $endDate = strtotime("{$today} + 1 day") - 1;
            }elseif(!empty($this->parames['create_time'])){
                $str=preg_split('/\s-\s/',$this->parames['create_time']);
                $startDate=strtotime($str[0]);
                $endDate=strtotime($str[1]);
            }elseif(!empty($this->parames['date_type'])){
                $dateType = $this->parames['date_type'];
                if ($dateType == 'day') {
                    //当天0点
                    $today = date("Ymd", time());
                    $startDate = strtotime($today);
                    //当天 23:59:59
                    $endDate = strtotime("{$today} + 1 day") - 1;
                } elseif ($dateType == 'week') {
                    $w = date('w', time());
                    //获取本周开始日期，如果$w是0，则表示周日，减去 6 天 
                    $first = 1;
                    //周一
                    $week = date('Y-m-d H:i:s', strtotime(date("Ymd") . "-" . ($w ? $w - $first : 6) . ' days'));
                    $startDate = strtotime(date("Ymd") . "-" . ($w ? $w - $first : 6) . ' days');
                    //本周结束日期 
                    //周天
                    $endDate = strtotime("{$week} +1 week") - 1;
                } elseif ($dateType == 'month') {
                    $month_begindate = date('Y-m-01 H:i:s', strtotime(date("Y-m-d")));
                    $month_enddate = date('Y-m-d H:i:s', strtotime("$month_begindate +1 month -1 day"));
                    //转换为时间戳
                    $startDate = strtotime($month_begindate);
                    $endDate = strtotime($month_enddate);
                }
            }
            $this->parames['cabinet_type']  =isset($this->parames['cabinet_type'] )?$this->parames['cabinet_type']:'';
            $this->parames['operation_type']=isset($this->parames['operation_type'] )?$this->parames['operation_type']:'';
            $this->parames['date_type']     =isset($this->parames['date_type'] )?$this->parames['date_type']:'day';
            $this->parames['year_date']     =isset($this->parames['year_date'] )?$this->parames['year_date']:date('Y',time());
            $this->parames['month_date']    =isset($this->parames['month_date'] )?$this->parames['month_date']:date('n',time());
            $this->parames['day_date']      =isset($this->parames['day_date'] )?$this->parames['day_date']:0;
            $this->parames['create_time']   =isset($this->parames['create_time'] )?$this->parames['create_time']:'';
            //查询出符合条件的交易总数量,(月卡,微信,余额,满次各总交易次数)
//            $cabinetDataSum    =M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetSum($this->parames);
            $cabinetDataSum    =M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetData('',$startDate,$endDate,$this->parames);
            //过滤参数
//            $uri=$this->makeSearchUrl($this->parames);
            //把参数带在地址后面
            $url='cabinetDataList';
            $showpage= $this->newpage($url,$this->commonDefine['pagesize'],count($cabinetDataSum));
//            $showpage= $this->newpage($url,$this->commonDefine['pagesize'],$cabinetDataSum);
            $limit=' limit '.$showpage['limit'];
            //分页查询出符合条件的每台机柜的总交易数,( 月卡,微信,余额,满次各总交易次数)
            $cabinetDatas    =M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetData($limit,$startDate,$endDate,$this->parames);
            //传过来的参数带回页面
            $parames=$this->parames;
//        }
        $sum['sum'] = 0;
//        $sum['pay_num'] = '';
        $sum['pay_ment_num0']=0;
        $sum['pay_ment_num1']=0;
        $sum['pay_ment_num2']=0;
        $sum['pay_ment_num3']=0;
        for ($i = 0; $i < count($cabinetDataSum); $i++) {
            //订单总数
            $sum['sum']    += $cabinetDataSum[$i]['quantity_number'];
            //月卡支付总数
            $sum['pay_ment_num0']+=$cabinetDataSum[$i]['pay_ment0'];
            //微信支付总数
            $sum['pay_ment_num1']+=$cabinetDataSum[$i]['pay_ment1'];
            //月余额支付总数
            $sum['pay_ment_num2']+=$cabinetDataSum[$i]['pay_ment2'];
            //满次免单总数
            $sum['pay_ment_num3']+=$cabinetDataSum[$i]['pay_ment3'];
        }
        for($i=0;$i<count($cabinetDatas);$i++){

            //月卡交易次数
            $cabinetDatas[$i]['pay_ment0'] = empty($cabinetDatas[$i]['pay_ment0']) ? 0 : $cabinetDatas[$i]['pay_ment0'];
            //微信支付交易次数
            $cabinetDatas[$i]['pay_ment1'] = empty($cabinetDatas[$i]['pay_ment1']) ? 0 : $cabinetDatas[$i]['pay_ment1'];
            //余额支付交易次数
            $cabinetDatas[$i]['pay_ment2'] = empty($cabinetDatas[$i]['pay_ment2']) ? 0 : $cabinetDatas[$i]['pay_ment2'];
            //满次免单交易次数
            $cabinetDatas[$i]['pay_ment3'] = empty($cabinetDatas[$i]['pay_ment3']) ? 0 : $cabinetDatas[$i]['pay_ment3'];
        }
        //如果打印按钮走这
        if(!empty($this->parames['execlbutton'])){
            $execlData='';
            $title=['机柜编号','机柜名称','业务类型','机柜类型','交易次数'];
            $operationType=[1=>'共享区',2=>'大B端',3=>'北海'];
            $cabinetType=[1=>'12轨机柜',2=>'9轨机柜'];
            for($i=0;$i<count($cabinetDataSum);$i++){
                $execlData[$i]['cabinet_number'] =$cabinetDataSum[$i]['cabinet_number'];
                $execlData[$i]['cabinet_name']   =$cabinetDataSum[$i]['cabinet_name'];
                $execlData[$i]['operation_type'] =$operationType[$cabinetDataSum[$i]['operation_type']];
                $execlData[$i]['cabinet_type']   =$cabinetType[$cabinetDataSum[$i]['cabinet_type']];
                $execlData[$i]['quantity_number']=$cabinetDataSum[$i]['quantity_number']==''?0:$cabinetDataSum[$i]['quantity_number'];
            }
            F()->Excel_module->exportExcel($title,$execlData,'机柜统计数据Execl列表','./',true);
        }
        $data['sum']                    =$sum;
        $data['cabinetDatas']           =$cabinetDatas;
        $data['parames']                =$parames;
        $data['paging']                 =$showpage['show'];
        $this->setOutPut($data);die;
    }



    /*
     * 机柜历史统计 年月
     * @parames   year_date                  年份
     * @parames   month_date                 月份
     *
     * @return    data[
     *                year_date              年份
     *                month_date             月份
     *                historyDatas[
     *                             pay_now   交易金额
     *                             order_now 交易数量
     *                             dates     时间
     *                            ]
     *                ]
     * */
    public function cabinetHistoryData()
    {
        $parames=$this->parames;
//        $dayDate=isset($parames['day_date'])?$parames['day_date']:0;
//        //如果没有选择月份走这
//        if($parames['month_date']==0 && $dayDate==0){
//
//            //获取指定年份的1月1日 ~ 12月31 23:59:59 时间戳
//            $startTime=strtotime($parames['year_date'].'-01');
//            $endTime=strtotime($parames['year_date'].'-12-31 23:59:59');
//
//        }elseif($parames['month_date']!=0 && $dayDate==0){
//
//            //指定月份第一天和最后一天的23:59:59 时间戳
//            $startTime=strtotime($parames['year_date'].'-'.$parames['month_date']);
//            $endTime=strtotime(date('Y-m-t',$startTime).' 23:59:59');
//
//        }elseif(!empty($parames['day_date'])){
//
//            //指定年月日的0点和23:59:59的时间戳
//            $startTime=strtotime($parames['year_date'].'-'.$parames['month_date'].'-'.$parames['day_date']);
//            $endTime=strtotime(date('Y-m-d',$startTime).' 23:59:59');
//        }
//        $historyDatas    =M_Mysqli_Class('md_lixiang','OrderModel')->HistoryOrder($parames,$startTime,$endTime);
//        if($parames['month_date']==0){
//            if($historyDatas){
//                //每月数量  每日平均数
//                for($i = 0 ; $i<count($historyDatas) ; $i++){
//                    $subDate[$i]=substr($historyDatas[$i]['create_date'],5,2);
//                }
//                $theValue=array_count_values ($subDate);
//            }else{
//                $theValue='';
//            }
//
//            $chartFont='日';
//            $avg=30;
//        }elseif($parames['month_date']!=0 && empty($parames['day_date'])){
//
//            if($historyDatas){
//                //每日数量  没小时平均数
//                for($i=0;$i<count($historyDatas);$i++){
//                    $subDate[$i]=substr($historyDatas[$i]['create_date'],8,2);
//                }
//                $theValue=array_count_values ($subDate);
//            }else{
//                $theValue='';
//            }
//            $avg=24;
//            $chartFont='时';
//        }else{
//            if($historyDatas){
//                //每小时数量   每分钟平均数
//                for($i = 0 ; $i<count($historyDatas) ; $i++){
//                    $subDate[$i]=substr($historyDatas[$i]['create_date'],11,2);
//                }
//                $theValue=array_count_values ($subDate);
//            }else{
//                $theValue='';
//            }
//
//            $chartFont='分钟';
//            $avg=60;
//
//        }
//        $returnData=[];
//        $y=0;
//        if($theValue){
//            foreach ( $theValue as $k=>$v){
//                $returnData[$y]['dates']=$k.'日';
//                $returnData[$y]['order_now']=isset($v)?$v:0;
//                $returnData[$y]['pay_avg']=ceil($v/$avg);
//                $y++;
//            }
//        }else{
//            $returnData['dates']='';
//            $returnData['order_now']=0;
//            $returnData['order_now']=0;
//        }
//        $data['chartFont']=$chartFont;
//        $data['historyDatas']=$returnData;
//        $data['year_date']   =$parames['year_date'];
//        $data['month_date']  =$parames['month_date'];
//        $data['day_date']  =isset($parames['day_date'])?$parames['day_date']:0;
//        $this->setOutPut($data);

        $dayDate=isset($parames['day_date'])?$parames['day_date']:0;
        //如果没有选择月份走这
        if($parames['month_date']==0 && $dayDate==0){
            $parames['the_date']='%Y%m';
            $parames['con_date']='%Y';
            $parames['max_data']=",max(date_format(create_date,'%d')) as max_date";
            $parames['date']=$parames['year_date'];
        }elseif($parames['month_date']!=0 && $dayDate==0){
            $parames['the_date']='%d';
            $parames['con_date']='%Y%c';
            $parames['max_data']=",max(date_format(create_date,'%H')+1) as max_date";
            $parames['date']=$parames['year_date'].$parames['month_date'];
        }elseif(!empty($parames['day_date'])){
            $parames['the_date']='%H';
            $parames['con_date']='%Y%c%e';
            $parames['date']=$parames['year_date'].$parames['month_date'].$parames['day_date'];
        }
        $historyDatas    =M_Mysqli_Class('md_lixiang','OrderModel')->HistoryOrder($parames);
        if($parames['month_date']==0){
            for($i = 0 ; $i<count($historyDatas) ; $i++){
                $historyDatas[$i]['pay_avg']=[];
                //交易总额
                $historyDatas[$i]['order_now']   = empty($historyDatas[$i]['order_now']) ? 0 : $historyDatas[$i]['order_now'];
                $historyDatas[$i]['pay_avg']=ceil($historyDatas[$i]['order_now']/$historyDatas[$i]['max_date']);
                //时间年份月份,截取月份
                $historyDatas[$i]['dates']=substr($historyDatas[$i]['dates'],-2).'月';

            }
            $chartFont='日';
        }elseif($parames['month_date']!=0 && empty($parames['day_date'])){
            for($i=0;$i<count($historyDatas);$i++){
                $historyDatas[$i]['order_now']   = empty($historyDatas[$i]['order_now']) ? 0 : $historyDatas[$i]['order_now'];
                $historyDatas[$i]['pay_avg']=ceil($historyDatas[$i]['order_now']/$historyDatas[$i]['max_date']);
                $historyDatas[$i]['dates']=$historyDatas[$i]['dates'].'日';
            }
            $chartFont='时';
        }else{
            for($i=0;$i<count($historyDatas);$i++){
                $historyDatas[$i]['order_now']   = empty($historyDatas[$i]['order_now']) ? 0 : $historyDatas[$i]['order_now'];
                $historyDatas[$i]['pay_avg']=ceil($historyDatas[$i]['order_now']/60);
                $historyDatas[$i]['dates']=$historyDatas[$i]['dates'].'时';
            }
            $chartFont='分钟';
        }
        $data['chartFont']=$chartFont;
        $data['historyDatas']=$historyDatas;
        $data['year_date']   =$parames['year_date'];
        $data['month_date']  =$parames['month_date'];
        $data['day_date']  =isset($parames['day_date'])?$parames['day_date']:0;
        $this->setOutPut($data);

    }

    /*
     * 单台机柜信息
     * */
    public function getCabinetInfo()
    {
        $parames=$this->parames;
        F()->Resource_module->setTitle('机柜数据统计详情');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $dateType = $this->parames['date_type'];
        if(empty($parames['create_time'])){
            if ($dateType == 'day') {
                //当天0点
                $today = date("Ymd", time());
                $startDate = strtotime($today);
                //当天 23:59:59
                $endDate = strtotime("{$today} + 1 day") - 1;
            } elseif ($dateType == 'week') {
                $w = date('w', time());
                //获取本周开始日期，如果$w是0，则表示周日，减去 6 天 
                $first = 1;
                //周一
                $week = date('Y-m-d H:i:s', strtotime(date("Ymd") . "-" . ($w ? $w - $first : 6) . ' days'));
                $startDate = strtotime(date("Ymd") . "-" . ($w ? $w - $first : 6) . ' days');
                //本周结束日期 
                //周天
                $endDate = strtotime("{$week} +1 week") - 1;
            } elseif ($dateType == 'month') {
                $month_begindate = date('Y-m-01 H:i:s', strtotime(date("Y-m-d")));
                $month_enddate = date('Y-m-d H:i:s', strtotime("$month_begindate +1 month -1 day"));
                //转换为时间戳
                $startDate = strtotime($month_begindate);
                $endDate = strtotime($month_enddate);
            }
        }else{
            $str=preg_split('/\s-\s/',$this->parames['create_time']);
            $startDate=strtotime($str[0]);
            $endDate=strtotime($str[1]);
        }

        $cabinetDatas   =M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetData('',$startDate,$endDate,$this->parames);
        for($i=0;$i<count($cabinetDatas);$i++){
            $cabinetData['quantity_number']=empty($cabinetDatas[$i]['quantity_number'])? 0 :$cabinetDatas[$i]['quantity_number'];
            $cabinetData['pay_ment_num0']  =empty($cabinetDatas[$i]['pay_ment0'])      ? 0 :$cabinetDatas[$i]['pay_ment0'];
            $cabinetData['pay_ment_num1']  =empty($cabinetDatas[$i]['pay_ment1'])      ? 0 :$cabinetDatas[$i]['pay_ment1'];
            $cabinetData['pay_ment_num2']  =empty($cabinetDatas[$i]['pay_ment2'])      ? 0 :$cabinetDatas[$i]['pay_ment2'];
            $cabinetData['pay_ment_num3']  =empty($cabinetDatas[$i]['pay_ment3'])      ? 0 :$cabinetDatas[$i]['pay_ment3'];
        }
        $date['date_type']     =$dateType;
        $date['year_date']     =isset($parames['year_date'])?$parames['year_date']:date('Y',time());
        $date['month_date']    =isset($parames['month_date'])?$parames['month_date']:date('n',time());
        $date['day_date']      =isset($parames['day_date'])?$parames['day_date']:0;
        $date['create_time']   =isset($parames['create_time'] )?$parames['create_time']:'';
        $this->smarty->assign("cabinetData",$cabinetData);
        $this->smarty->assign("date", $date);
        $this->smarty->assign("cabinet_number", $parames['cabinet_number']);
        $this->smarty->view('managedata/cabinetInfo.phtml');
    }



}
