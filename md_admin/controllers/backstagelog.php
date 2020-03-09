<?php
require DEFAULT_SYSTEM_PATH.'libraries/Excel/PHPExcel.php';
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class backstagelog extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "backstagelog");
    }


    /**
     * 后台操作日志管理列表
     */
    public function backstageLogList()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('后台操作日志列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $Role=M_Mysqli_Class('md_lixiang','RoleModel')->getAllsRoleByAttr([1=>1]);
        if(empty($this->parames)){
            $url = "/backstageLogList";
            $nums=M_Mysqli_Class('md_lixiang','BackstageLogModel')->getNumByAttr([1=>1]);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $backstageList=M_Mysqli_Class('md_lixiang','BackstageLogModel')->getAllCabinetByAttr($showpage['limit'],[1=>1]);
            $parames['input_data']='';
            $parames['operation_type']='';
            $parames['operation_state']='';
            $parames['user_flag']='';
            $parames['create_time']='';
        }else{
            $uri=$this->makeSearchUrl($this->parames);
            $url = "backstageLogList?".$uri;
            $backstageNums=M_Mysqli_Class('md_lixiang','BackstageLogModel')->getSearchBackstageData($this->parames,'');
            $showpage= $this->page($url,$this->commonDefine['pagesize'],count($backstageNums));
            $limit=' limit '.$showpage['limit'];
            $backstageList=M_Mysqli_Class('md_lixiang','BackstageLogModel')->getSearchBackstageData($this->parames,$limit);
            $parames=$this->parames;
        }

        $operationType=[0=>'启用',1=>'禁用',2=>'删除',3=>'更新',4=>'添加'];
        $operationState=[1=>'操作成功',2=>'操作失败'];
        $status=[0=>'正常',1=>'禁用',2=>'删除'];
        for($i=0;$i<count($backstageList);$i++){
           $backstageList[$i]['operation_type']=isset($operationType[$backstageList[$i]['operation_type']])?$operationType[$backstageList[$i]['operation_type']]:'';
           $backstageList[$i]['operation_state']=isset($operationState[$backstageList[$i]['operation_state']])?$operationState[$backstageList[$i]['operation_state']]:'';
           $backstageList[$i]['status']=$status[$backstageList[$i]['status']];
        }
        $this->smarty->assign('backstageList',$backstageList);
        $this->smarty->assign('role',$Role);
        $this->smarty->assign('parames',$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('backstageLog/list.phtml');
    }


    /*
     * 查看操作详情内容
     * */
    public function getBackStageContent()
    {
        F()->Resource_module->setTitle('操作详情内容列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));

        $contentDatas=M_Mysqli_Class('md_lixiang','BackstageContentModel')->getBackstageContentData(['back_id'=>$this->parames['back_id']]);
        $contentData = [];
        foreach ($contentDatas as $k => $v) {
            $contentData[$v['table_name']][] = $v;

        }
        $contents=[];
        $i=0;
        foreach ($contentData as $k=>$v){
            $contents[$i]  =$v;
            $tablesName[$i]=$k;
            $i++;
        }
//        for($i=0;$i<count($tablesName); $i++){
//            for($k=0;$k<count($contents[$i]);$k++){
//                $data[$k]=[
//                    'clm_name'=>$contents[$i][$k]['clm_name'],
//                    'old_string'=>$contents[$i][$k]['old_string'],
//                    'new_string'=>$contents[$i][$k]['new_string'],
//                    'create_date'=>$contents[$i][$k]['create_date'],
//                ];
//            }
//        }
//        echo '<pre />';
        $this->smarty->assign("contents", isset($contents)?$contents:'');
        $this->smarty->assign("tablesName", isset($tablesName)?$tablesName:'' );
        $this->smarty->view('backstageLog/contentList.phtml');
    }


    /*
        * 收益人员统计数据列表
        * */
    public function revenueStatis()
    {
        $this->checkAuth();
        $parames=$this->parames;
        F()->Resource_module->setTitle('收益明细管理列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(empty($this->parames['create_time'])){

            $todayStart= date('Y-m-d 00:00:00', time());
            $todayEnd= date('Y-m-d 23:59:59', time());
            $parames['create_time']=$todayStart. ' - '.$todayEnd;
        }
        //集团用户数量
        $parames['where']=' AND user_flag=1';
        $parames['identification']=1;
        $userType1=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser('',$parames);
        //普通用户数量
        $parames['where']=' AND user_flag=0';
        $userType0=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser('',$parames);
        //交押金人数金额
        $userPledgeOrder=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getUserPledOrderData('',['create_time'=>$parames['create_time']]);
        $userPledgeOrder1['pledge_money']='';
        $userPledgeOrder0['pledge_money']='';
        $userPledgeOrder0['user_count']=[];
        $userPledgeOrder1['user_count']=[];
        for($i=0;$i<count($userPledgeOrder);$i++){
            if($userPledgeOrder[$i]['user_flag']==1){
                $userPledgeOrder1['pledge_money']+=$userPledgeOrder[$i]['pledge_money'];   //集团用户押金金额
                $userPledgeOrder1['user_count'][]+=$userPledgeOrder[$i]['id'];              //集团用户交押金人数
            }else{
                $userPledgeOrder0['pledge_money']+=$userPledgeOrder[$i]['pledge_money'];   //普通用户押金金额
                $userPledgeOrder0['user_count'][]=$userPledgeOrder[$i]['id'];              //普通用户交押金人数
            }
        }
        //退押金数量
        $userRefundPledgeOrder=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getUserPledOrderRefundData('',['agree_time'=>$parames['create_time']]);
        $userPledgeRefund1['pledge_money']='';
        $userPledgeRefund0['pledge_money']='';
        $userPledgeRefund0['user_count']=[];
        $userPledgeRefund1['user_count']=[];
        for($i=0;$i<count($userRefundPledgeOrder);$i++){
            if($userRefundPledgeOrder[$i]['user_flag']==1){
                $userPledgeRefund1['pledge_money']+=$userRefundPledgeOrder[$i]['recede_money'];   //集团用户退押金金额
                $userPledgeRefund1['user_count'][]+=$userRefundPledgeOrder[$i]['id'];              //集团用户退押金人数
            }else{
                $userPledgeRefund0['pledge_money']+=$userRefundPledgeOrder[$i]['recede_money'];   //普通用户退押金金额
                $userPledgeRefund0['user_count'][]=$userRefundPledgeOrder[$i]['id'];              //普通用户退押金人数
            }
        }
        //月卡收益
        $cardpaymenData=M_Mysqli_Class('md_lixiang','CardPaymentModel')->getMonthCardOrder('',['create_time'=>$parames['create_time'],'pay_status'=>1]);
        $cardpaymenMoney['card_money1']='';
        $cardpaymenMoney['card_money0']='';
        for($i=0;$i<count($cardpaymenData);$i++){
            if($cardpaymenData[$i]['user_type']==1){
                $cardpaymenMoney['card_money1']+=$cardpaymenData[$i]['payment_amount'];   //集团用户月卡金额
            }else{
                $cardpaymenMoney['card_money0']+=$cardpaymenData[$i]['payment_amount'];   //普通用户月卡金额
            }
        }
        $returnDate='';
        //单次交易次数，收益
        if(empty($this->parames['create_time'])){
            //当天0点
            $today = date("Ymd", time());
            $startDate = strtotime($today);
            //当天 23:59:59
            $endDate = strtotime("{$today} + 1 day") - 1;
        }else{
            $str=preg_split('/\s-\s/',$this->parames['create_time']);
            $startDate=strtotime($str[0]);
            $endDate=strtotime($str[1]);
//            $str[2]=strtotime($str[1])-86400;
            $returnDate=substr($str[0],0,10).' ~ '.date('Y-m-d',$endDate);
        }
        //机柜交易次数
        $cabinetData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetData('',$startDate,$endDate,'');
        //单次收益
        $orderMoney=M_Mysqli_Class('md_lixiang','OrderModel')->getCondOrderData($startDate,$endDate);
        $sum['sum'] = '';
        $sum['pay_num'] = '';
        $sum['pay_ment_num0']='';
        $sum['pay_ment_num1']='';
        $sum['pay_ment_num2']='';
        $sum['pay_ment_num3']='';
        for ($i = 0; $i < count($cabinetData); $i++) {
            //如果为空就赋值为0
            //订单总数
            $sum['sum']    += $cabinetData[$i]['quantity_number'];
            //月卡支付总数
            $sum['pay_ment_num0']+=$cabinetData[$i]['pay_ment0'];
            //微信支付总数
            $sum['pay_ment_num1']+=$cabinetData[$i]['pay_ment1'];
            //月余额支付总数
            $sum['pay_ment_num2']+=$cabinetData[$i]['pay_ment2'];
            //满次免单总数
            $sum['pay_ment_num3']+=$cabinetData[$i]['pay_ment3'];
//            微信支付总金额
//            $sum['pay_num']+=empty($cabinetData[$i]['pay_num']) ? 0 :$cabinetData[$i]['pay_num'];
        }
        //var_dump(isset($userPledgeOrder1['user_count'])?count($userPledgeOrder1['user_count']):0);die;
        $data=[
            'userType1'=>isset($userType1)?count($userType1):0,//集团用户数量
            'userType0'=>isset($userType0)?count($userType0):0,//普通用户数量
            'userTypeSum'=>count($userType1)+count($userType0),//用户总数
            $userPledgeOrderMoney1=!empty($userPledgeOrder1['pledge_money'])?$userPledgeOrder1['pledge_money']/100:0,        //集团用户押金金额
            'userPledgeOrder1'=>$userPledgeOrderMoney1,
            'userPleUserCount1'=>isset($userPledgeOrder1['user_count'])?count($userPledgeOrder1['user_count']):0,       //集团用户交押金人数
            $userPledgeOrderMoney0=!empty($userPledgeOrder0['pledge_money'])?$userPledgeOrder0['pledge_money']/100:0,        //普通用户交押金金额
            'userPledgeOrder0'=>$userPledgeOrderMoney0,
            'userPleUesrCount0'=>isset($userPledgeOrder0['user_count'])?count($userPledgeOrder0['user_count']):0,        //普通用户交押金人数
            $userPledgeOrderMoneySum=$userPledgeOrderMoney1+$userPledgeOrderMoney0,
            'userPledgeOrderMoneySum'=>$userPledgeOrderMoneySum,                                                         //用户交押金金额总额
            'userPledgeOrderCountSum'=>count($userPledgeOrder1['user_count'])+count($userPledgeOrder0['user_count']),    //用户交押金总数

            $userPledgeRefundMoney1=!empty($userPledgeRefund1['pledge_money'])?$userPledgeRefund1['pledge_money']:0,  //集团用户退押金金额
            'userPledgeRefundMoney1'=>$userPledgeRefundMoney1,
            'userPledgeRefundCount1'=>isset($userPledgeRefund1['user_count'])?count($userPledgeRefund1['user_count']):0,  //集团用户退押金人数
            $userPledgeRefundMoney0=!empty($userPledgeRefund0['pledge_money'])?$userPledgeRefund0['pledge_money']:0,  //普通用户退押金金额
            'userPledgeRefundMoney0'=>$userPledgeRefundMoney0,
            'userPledgeRefundCount0'=>isset($userPledgeRefund0['user_count'])?count($userPledgeRefund0['user_count']):0,  //普通用户退押金人数
            $userPledgeRefundMoneySum=$userPledgeRefundMoney1+$userPledgeRefundMoney0,
            'userPledgeRefundMoneySum'=>$userPledgeRefundMoneySum,                                                        //用户退押金总额
            'userPledgeRefundCountSum'=>count($userPledgeRefund1['user_count'])+count($userPledgeRefund0['user_count']),  //用户退押金数量

            'userPledgeRece1'=>$userPledgeOrderMoney1-$userPledgeRefundMoney1,                                                 //集团用户实收押金金额
            'userPledgeRece0'=>$userPledgeOrderMoney0-$userPledgeRefundMoney0,                                                 //普用户实收押金金额
            'userPledgeReceSum'=>($userPledgeOrderMoney1+$userPledgeOrderMoney0)-($userPledgeRefundMoney1+$userPledgeRefundMoney0),      //实收押金金额

            'cardpaymenMoney1'=>!empty($cardpaymenMoney['card_money1'])?$cardpaymenMoney['card_money1']:0,                //集团用户月卡金额
            'cardpaymenMoney0'=>!empty($cardpaymenMoney['card_money0'])?$cardpaymenMoney['card_money0']:0,                //普通用户月卡金额
            $cardpaymenMoneySum=$cardpaymenMoney['card_money0']+$cardpaymenMoney['card_money1'],
            'cardpaymenMoneySum'=>$cardpaymenMoneySum,                                                                    //用户月卡总金额

            'pay_ment_num0'=>isset($sum['pay_ment_num0'])?$sum['pay_ment_num0']:0,                                        //月卡支付总数
            'pay_ment_num1'=>isset($sum['pay_ment_num1'])?$sum['pay_ment_num1']:0 ,                                       //微信支付次数
            'pay_ment_num2'=>isset($sum['pay_ment_num2'])?$sum['pay_ment_num2']:0,                                        //余额支付总数
            'pay_ment_num3'=>isset($sum['pay_ment_num3'])?$sum['pay_ment_num3']:0,                                        //满次免单总数
            'sum'=>isset($sum['sum'])?$sum['sum']:0,                                                                      //交易总数
            $payNum=isset($orderMoney)?$orderMoney/100:0,
            'pay_num'=>$payNum,                                                                                           //交易总金额
            $earningsSum=$userPledgeOrderMoneySum+$payNum+$cardpaymenMoneySum,                                            //总收益  押金收益+单次交易收益+月卡收益
            'earningsSum'=>$earningsSum,

            'netProceeds'=>$earningsSum-$userPledgeRefundMoneySum,                                                        //净收益
            'payAndCardMoney'=>$payNum+$cardpaymenMoneySum                                                                //单次|+月卡收益
        ];
        //如果打印按钮走这
        if(!empty($this->parames['execlbutton'])){
            $fileName='收益明细统计列表';
            $isDown=true;
            $savePath='./';

            $obj = new PHPExcel();
            if (empty($returnDate)){
                $dateName="本日";
            }else{
                $dateName=$returnDate;
            }
            $eTval=$dateName."  收益说明 总收入 ￥ ".$data['earningsSum']."  净收益  ￥".$data['netProceeds']."   其中押金收入  ￥".$data['userPledgeOrderMoneySum']."   月卡以及单次收费收入  ￥".$data['payAndCardMoney']."   退押金金额 ￥".$data['userPledgeRefundMoneySum'];
            $styleArray = array(
                'font'=> array(
                    'bold'=> true,
                    'color'=>array('rgb' => 'FF0000'),
                    'size'=> 13,
                )
            );
            $styleArray2 = array(
                'font'=> array(
                    'bold'=> true,
                    'color'=>array('rgb' => 'FF0000'),
                )
            );
            $obj->setactivesheetindex(0);
            $cellTitle1 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O');
            $obj->getActiveSheet(0)->setTitle('收益明细');   //设置sheet名称
            $obj->getActiveSheet(0)->getStyle('A1') -> applyFromArray($styleArray);          //设置字体样式
            $obj->getActiveSheet(0)->getStyle('A5:G5') -> applyFromArray($styleArray2);          //设置字体样式
            $obj->getActiveSheet(0)->setCellValue('A1', $eTval);                                //填充数据
            $obj->getActiveSheet(0)->mergeCells('A1:O1');                                           //合并单元格
            $obj->getActiveSheet(0)->getRowDimension('1')->setRowHeight(25);                   //设置第一行高度
            for($i=0;$i<count($cellTitle1);$i++){
                $obj->getActiveSheet(0)->getColumnDimension($cellTitle1[$i])->setWidth('15');           //循环设置宽度
                $obj->getActiveSheet(1)->getColumnDimension($cellTitle1[$i])->setWidth('15');           //循环设置宽度
            }
            $cellTitle2=array('A2','B2','C2','D2','E2','F2','G2');
            $cellName1=array('','新增用户数','交押金人数','交押金金额','退押金人数','退押金金额','押金實收金額');
            for($i=0;$i<count($cellTitle2);$i++){   //设置列标题
                $obj->getActiveSheet(0)->setCellValue($cellTitle2[$i], $cellName1[$i]);
            }
            $style_array = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    )
                ) );
            $obj->getActiveSheet(0)->getStyle('A2:G2')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->getStyle('A3:G3')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->getStyle('A4:G4')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->getStyle('A5:G5')->applyFromArray($style_array);

            $obj->getActiveSheet(0)->setCellValue('A3', '集团用户');   //标题
            $obj->getActiveSheet(0)->setCellValue('A4', '普通用户');
            $obj->getActiveSheet(0)->setCellValue('A5', '合计：');

            $obj->getActiveSheet(0)->setCellValue('B3',$data['userType1'].'  /人');                                          //新增集团用户
            $obj->getActiveSheet(0)->setCellValue('B4',$data['userType0'].'  /人');                                          //新增普通用户
            $obj->getActiveSheet(0)->setCellValue('B5',$data['userTypeSum'].'  /人');                                        //新增用户总数

            $obj->getActiveSheet(0)->setCellValue('C3',$data['userPleUserCount1'].'  /人');                                  //集团用户交押金人数
            $obj->getActiveSheet(0)->setCellValue('C4',$data['userPleUesrCount0'].'  /人');                                  //普通用户交押金人数
            $obj->getActiveSheet(0)->setCellValue('C5',$data['userPledgeOrderCountSum'].'  /人');                                //交押金总人数

            $obj->getActiveSheet(0)->setCellValue('D3','￥ '.$userPledgeOrderMoney1);                                        //集团用户交押金金额
            $obj->getActiveSheet(0)->setCellValue('D4','￥ '.$userPledgeOrderMoney0);                                        //普通用户交押金金额
            $obj->getActiveSheet(0)->setCellValue('D5','￥ '.$userPledgeOrderMoneySum);                                      //交押金总金额

            $obj->getActiveSheet(0)->setCellValue('E3',$data['userPledgeRefundCount1'].'  /人');                             //集团用户退押金人数
            $obj->getActiveSheet(0)->setCellValue('E4',$data['userPledgeRefundCount0'].'  /人');                             //普通用户退押金人数
            $obj->getActiveSheet(0)->setCellValue('E5',$data['userPledgeRefundCountSum'].'  /人');                           //退押金总人数

            $obj->getActiveSheet(0)->setCellValue('F3','￥ '.$userPledgeRefundMoney1);                                       //集团用户退押金金额
            $obj->getActiveSheet(0)->setCellValue('F4','￥ '.$userPledgeRefundMoney0);                                       //普通用户退押金金额
            $obj->getActiveSheet(0)->setCellValue('F5','￥ '.$userPledgeRefundMoneySum);                                     //退押金总金额

            $obj->getActiveSheet(0)->setCellValue('G3','￥ '.$data['userPledgeRece1']);                                      //集团用户押金实收金额
            $obj->getActiveSheet(0)->setCellValue('G4','￥ '.$data['userPledgeRece0']);                                      //普通用户押金实收金额
            $obj->getActiveSheet(0)->setCellValue('G5','￥ '.$data['userPledgeReceSum']);                                    //押金实收金额

            $obj->getActiveSheet(0)->getStyle('I2:N2')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->getStyle('I3:N3')->applyFromArray($style_array);
            $cellTitle3=array('I2','J2','K2','L2','M2','N2');
            $cellName2=array('','单次','月卡','余额','满次','合计');
            for($i=0;$i<count($cellTitle3);$i++){   //设置列标题
                $obj->getActiveSheet(0)->setCellValue($cellTitle3[$i], $cellName2[$i]);
            }
            $obj->getActiveSheet(0)->setCellValue('I3', '次数');   //标题
            $obj->getActiveSheet(0)->getStyle('N2:N3') -> applyFromArray($styleArray2);                                          //设置字体样式
            $obj->getActiveSheet(0)->setCellValue('J3',$data['pay_ment_num1'].'  /次');                                      //单次
            $obj->getActiveSheet(0)->setCellValue('K3',$data['pay_ment_num0'].'  /次');                                      //月卡
            $obj->getActiveSheet(0)->setCellValue('L3',$data['pay_ment_num2'].'  /次');                                      //余额
            $obj->getActiveSheet(0)->setCellValue('M3',$data['pay_ment_num3'].'  /次');                                      //满次
            $obj->getActiveSheet(0)->setCellValue('N3',$data['sum'].'  /次');                                                 //合计


            $obj->getActiveSheet(0)->getStyle('A9:B9'  )->applyFromArray($style_array);                                           //设置边框
            $obj->getActiveSheet(0)->getStyle('A10:B10')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->getStyle('A11:B11')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->getStyle('A12:B12')->applyFromArray($style_array);
            $obj->getActiveSheet(0)->setCellValue('A9', '单次交易收益');   //标题
            $obj->getActiveSheet(0)->setCellValue('A10', '押金收益');
            $obj->getActiveSheet(0)->setCellValue('A11', '月卡收益：');
            $obj->getActiveSheet(0)->setCellValue('A12', '总收益： ');
            $obj->getActiveSheet(0)->setCellValue('B9',  '￥ '.$payNum);
            $obj->getActiveSheet(0)->setCellValue('B10', '￥ '.$userPledgeOrderMoneySum);
            $obj->getActiveSheet(0)->setCellValue('B11', '￥ '.$cardpaymenMoneySum);
            $obj->getActiveSheet(0)->setCellValue('C11', '集团用户： ￥ '.$data['cardpaymenMoney1'].'      普通用户： ￥'.$data['cardpaymenMoney0']);
            $obj->getActiveSheet(0)->setCellValue('B12', '￥ '.$earningsSum);
            $obj->getActiveSheet(0)->mergeCells('C11:E11');                                                                 //合并单元格
            $obj->getActiveSheet(0)->getStyle('A12:B12') -> applyFromArray($styleArray2);                             //设置字体样式


            //文件名处理
            if(!$fileName){
                $fileName = uniqid(time(),true);
            }


            $objWrite = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');

            if($isDown){   //网页下载
                ob_end_clean();
                header('pragma:public');
                header("Content-Disposition:attachment;filename=$fileName.xls");
                $objWrite->save('php://output');exit;
            }

            $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码

            $_savePath = $savePath.$_fileName.'.xlsx';
            $objWrite->save($_savePath);

            return $savePath.$fileName.'.xlsx';die;
        }
        $this->smarty->assign("data", $data);
        $this->smarty->assign("returnDate", $returnDate);
        $this->smarty->assign("parames", $this->parames);
        $this->smarty->view('managedata/revenueStatis.phtml');
    }

    /*
     * 查询交押金与退押金用户信息
     * */
    public function getUserPledgeData()
    {
        $html='';
        $parames=$this->parames;
        if(empty($parames['create_time'])){
            $todayStart= date('Y-m-d 00:00:00', time());
            $todayEnd= date('Y-m-d 23:59:59', time());
            $parames['create_time']=$todayStart. ' - '.$todayEnd;
            $returnDate='本日';

        }else{
            $str=preg_split('/\s-\s/',$parames['create_time']);
            $str[2]=strtotime($str[1])-86400;
            $returnDate=substr($str[0],0,10).' ~ '.substr($str[1],0,10);
        }

        if($parames['type']==1){
            $html.="<div style='text-align: center;font-size: 20px'>{$returnDate}交押金用户信息</div>";
            $userPledgeOrderData=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->getUserPledOrderData('',['create_time'=>$parames['create_time']]);
        }else{
            $html.="<div style='text-align: center;font-size: 20px'>{$returnDate}退押金用户信息</div>";
            $userPledgeOrderData=M_Mysqli_Class('md_lixiang','DepositRefundModel')->getUserPledOrderRefundData('',['agree_time'=>$parames['create_time']]);
        }


        $html.='<table class="layui-table" lay-size="sm">

                                    <thead>
                                      <tr>
                                        <th style="text-align: center;font-weight:bold">用户名</th>
                                        <th style="text-align: center;font-weight:bold">身份证</th>
                                        <th style="text-align: center;font-weight:bold">手机号</th>
                                        <th style="text-align: center;font-weight:bold">用户身份</th>
                                      </tr> 
                                    </thead>
                                    <tbody>';
        $userType=[0=>'普通用户',1=>'集团用户',2=>'无',4=>'商家'];
//        foreach ($userPledgeOrderData as $key => $value) {
        if($userPledgeOrderData){
            for($i=0;$i<count($userPledgeOrderData);$i++){
//                $Type[$i]=$userType[isset($userPledgeOrderData[$i]['user_flag'])?$userPledgeOrderData[$i]['user_flag']:2];
                $html.='<tr><td>'.$userPledgeOrderData[$i]['name'].'</td><td>'.$userPledgeOrderData[$i]['card_number'].'</td><td>'.$userPledgeOrderData[$i]['mobile'].'</td><td>'.$userType[isset($userPledgeOrderData[$i]['user_flag'])?$userPledgeOrderData[$i]['user_flag']:2].'</td></tr>';
            }
        }else{
            $html.="<tr><td colspan='10'>无</td></tr>";
        }

//var_dump($Type);die;
//        }
        $html.='</tbody></table>';
        $this->setOutPut($html);die;
    }


    /*
     * 获取token
     * */
    public function getTokenAndJssdk()
    {
          $url=WEIAPI_URL."/jsticket?action=actionGetJssdk";
          $outPut=file_get_contents($url);
          if($outPut['code']==2000){
              $this->msg($outPut['msg'], '' , 'ok');die;
          }else{
              $this->msg($outPut['msg'], '' , 'error');die;
          }
    }
}
