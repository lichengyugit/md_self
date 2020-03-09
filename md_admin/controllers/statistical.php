<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class statistical extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "statistical");
    }


    /**
     * 统计
     */
    public function CashierStatistics()
    {   $this->checkAuth();
        F()->Resource_module->setTitle('收银统计');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
/*        $url = "/statistical";
        $nums=M_Mysqli_Class('md_lixiang','AdminModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $adminList=M_Mysqli_Class('md_lixiang','AdminModel')->getAllAdminByAttr($showpage['limit'],$this->parames);*/
/*        $this->smarty->assign('adminList',$adminList);
        $this->smarty->assign("pages", $showpage['show']);*/
        $this->smarty->view('statistical/index.phtml');
    }

    /**
    * [userSearch 收银统计页面搜索]
    * @return [type] [description]
    */
    public function MoneySearch(){
      $parames=$this->parames;
          switch ($parames['test'])
          {
          case 'month':
            /*
             *  公用部分
             */
            $DayRule=['月统计'=>'%Y-%m','天统计'=>'%Y-%m-%d','周统计'=>'%Y-%u','日期'=>'%Y-%m-%d'];
            $TypeRule=['普通用户单次'=>' AND order_type=0','集团用户单次'=>' AND order_type=1','普通用户月卡'=>' AND user_type=0','集团用户月卡'=>' AND user_type=1'];
            $date = $parames['range'];
            $a = date('Y-m-01', strtotime($date)).' 00:00:00';
            $b = date('Y-m-d', strtotime("$a +1 month -1 day")).' 24:00:00';
            //月第一天时间戳
            $firstday = strtotime(date('Y-m-01', strtotime($date)).' 00:00:00');
            //月
            $month=date('m',$firstday);
            //月最后一天时间戳
            $lastday = strtotime(date('Y-m-d', strtotime("$a +1 month -1 day")).' 23:59:59');
            //月最后一天
            $last=date('d',$lastday);
            /*
                订单表单次收入
             */
            //拼装X轴数据
            $Xdata='';
            for($i=1; $i<$last+1;$i++){
              $Xdata.=$month.'.'.$i.',';
            }
            $Xdata=rtrim($Xdata, ','); 
          
            //普通用户单次消费总额
            $UserSingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['月统计'],$firstday,$lastday,$TypeRule['普通用户单次']);
            $UserSingles = $UserSingle ? $UserSingle[0]['money']/100 : 0;
            //集团用户单次消费总额
            $CompanySingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['月统计'],$firstday,$lastday,$TypeRule['集团用户单次']);
            $CompanySingles = $CompanySingle ? $CompanySingle[0]['money']/100 : 0;
            //普通用户月卡充值总额
            $UserMonth=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['月统计'],$firstday,$lastday,$TypeRule['普通用户月卡']);
            $UserMonths = $UserMonth ? $UserMonth[0]['money'] : 0;
            //集团用户月卡充值总额
            $CompanyMonth=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['月统计'],$firstday,$lastday,$TypeRule['集团用户月卡']);
            $CompanyMonths = $CompanyMonth ? $CompanyMonth[0]['money'] : 0;
            //全部总额
            $count=$UserSingles+$CompanySingles+$UserMonths+$CompanyMonths;

            $XUserSingleStr='';
            $XCompanySingleStr='';
            $XUserMonthStr='';
            $XCompanyMonthStr='';
            //普通用户单次X轴数据
            $XUserSingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['普通用户单次']);
            $arr=$this->DataProcessing($last,$XUserSingle);
            foreach ($arr as $k => $v) {
              $XUserSingleStr.=($arr[$k]/100).',';
            }
            $XUserSingleStr=rtrim($XUserSingleStr, ','); 
            //集团用户单次X轴数据
            $XCompanySingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['集团用户单次']);
            $arr=$this->DataProcessing($last,$XCompanySingle);
            foreach ($arr as $k => $v) {
              $XCompanySingleStr.=($arr[$k]/100).',';
            }
            $XCompanySingleStr=rtrim($XCompanySingleStr, ','); 
            //普通用户月卡X轴数据
            $UserMonthX=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['普通用户月卡']);
            $arr=$this->DataProcessing($last,$UserMonthX);
            foreach ($arr as $k => $v) {
              $XUserMonthStr.=floor($arr[$k]).',';
            }
            $XUserMonthStr=rtrim($XUserMonthStr, ','); 
            //集团用户月卡X轴数据
            $CompanyMonthX=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['集团用户月卡']);
            $arr=$this->DataProcessing($last,$CompanyMonthX);
            foreach ($arr as $k => $v) {
              $XCompanyMonthStr.=floor($arr[$k]).',';
            }
            $XCompanyMonthStr=rtrim($XCompanyMonthStr, ','); 
            //押金总收入
            $pledge_order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->CountPledgeOrderSum();
            $pledge=0;
            foreach ($pledge_order as $key => $value) {
              $pledge+=$pledge_order[$key]['pledge_money'];
            }
            $arr=[
              'user'=>$XUserSingleStr,                          //普通用户单次X轴数据
              'company'=>$XCompanySingleStr,                    //集团用户单次X轴数据
              'payMentUserXmoney'=>$XUserMonthStr,              //普通用户月卡X轴数据
              'payMentCompanyXmoney'=>$XCompanyMonthStr,        //集团用户月卡X轴数据
              'Xdata'=>$Xdata,                                  //X轴数据
              'count'=>$count,                                  //总收入
              'pledge'=>$pledge/100,                            //所有押金
              'UserSingle'=>$UserSingles,                       //普通用户单次消费总额         
              'CompanySingle'=>$CompanySingles,                 //集团用户单次消费总额
              'UserMonth'=>sprintf("%.2f",$UserMonths),         //普通用户月卡充值总额
              'CompanyMonth'=>sprintf("%.2f",$CompanyMonths),   //集团用户月卡充值总额
            ];
            $this->setOutPut($arr);
            break;  
          case 'week':
            $this->setOutPut('周');
            break;
          case 'day':
            $this->setOutPut('天');
            break;
          case 'date':
            /*
             *  公用部分
             */
            $DayRule=['月统计'=>'%Y-%m','天统计'=>'%Y-%m-%d','周统计'=>'%Y-%u','日期'=>'%Y-%m-%d'];
            $TypeRule=['普通用户单次'=>' AND order_type=0','集团用户单次'=>' AND order_type=1','普通用户月卡'=>' AND user_type=0','集团用户月卡'=>' AND user_type=1'];
            $date = $parames['range'];
            $str=preg_split('/\s-\s/',$date);
            $firstday=strtotime($str[0]);
            $lastday=strtotime($str[1]);
            $diff = diffBetweenTwoDays($str[0], $str[1]);
            $strmonth=date('m',$firstday);
            $endmonth=date('m',$lastday);

            
            /*
                订单表单次收入
             */
            //拼装X轴数据
            $Xdata='';
            for($i=1; $i<$strmonth;$i++){
              $Xdata.=$strmonth.'.'.$i.',';
            }
            $Xdata=rtrim($Xdata, ','); 
            


            //普通用户单次消费总额
            $UserSingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['日期'],$firstday,$lastday,$TypeRule['普通用户单次']);
            $UserSingles = $UserSingle ? $UserSingle[0]['money']/100 : 0;
            //集团用户单次消费总额
            $CompanySingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['日期'],$firstday,$lastday,$TypeRule['集团用户单次']);
            $CompanySingles = $CompanySingle ? $CompanySingle[0]['money']/100 : 0;
            //普通用户月卡充值总额
            $UserMonth=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['日期'],$firstday,$lastday,$TypeRule['普通用户月卡']);
            $UserMonths = $UserMonth ? $UserMonth[0]['money'] : 0;
            //集团用户月卡充值总额
            $CompanyMonth=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['日期'],$firstday,$lastday,$TypeRule['集团用户月卡']);
            $CompanyMonths = $CompanyMonth ? $CompanyMonth[0]['money'] : 0;
            //全部总额
            $count=$UserSingles+$CompanySingles+$UserMonths+$CompanyMonths;

            $XUserSingleStr='';
            $XCompanySingleStr='';
            $XUserMonthStr='';
            $XCompanyMonthStr='';
            //普通用户单次X轴数据
            $XUserSingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['普通用户单次']);
            $arr=$this->DataProcessing($last,$XUserSingle);
            foreach ($arr as $k => $v) {
              $XUserSingleStr.=($arr[$k]/100).',';
            }
            $XUserSingleStr=rtrim($XUserSingleStr, ','); 
            //集团用户单次X轴数据
            $XCompanySingle=M_Mysqli_Class('md_lixiang','OrderModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['集团用户单次']);
            $arr=$this->DataProcessing($last,$XCompanySingle);
            foreach ($arr as $k => $v) {
              $XCompanySingleStr.=($arr[$k]/100).',';
            }
            $XCompanySingleStr=rtrim($XCompanySingleStr, ','); 
            //普通用户月卡X轴数据
            $UserMonthX=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['普通用户月卡']);
            $arr=$this->DataProcessing($last,$UserMonthX);
            foreach ($arr as $k => $v) {
              $XUserMonthStr.=floor($arr[$k]).',';
            }
            $XUserMonthStr=rtrim($XUserMonthStr, ','); 
            //集团用户月卡X轴数据
            $CompanyMonthX=M_Mysqli_Class('md_lixiang','CardPaymentModel')->FlexibleSearch($DayRule['天统计'],$firstday,$lastday,$TypeRule['集团用户月卡']);
            $arr=$this->DataProcessing($last,$CompanyMonthX);
            foreach ($arr as $k => $v) {
              $XCompanyMonthStr.=floor($arr[$k]).',';
            }
            $XCompanyMonthStr=rtrim($XCompanyMonthStr, ','); 
            //押金总收入
            $pledge_order=M_Mysqli_Class('md_lixiang','PledgeOrderModel')->CountPledgeOrderSum();
            $pledge=0;
            foreach ($pledge_order as $key => $value) {
              $pledge+=$pledge_order[$key]['pledge_money'];
            }
            $arr=[
              'user'=>$XUserSingleStr,                          //普通用户单次X轴数据
              'company'=>$XCompanySingleStr,                    //集团用户单次X轴数据
              'payMentUserXmoney'=>$XUserMonthStr,              //普通用户月卡X轴数据
              'payMentCompanyXmoney'=>$XCompanyMonthStr,        //集团用户月卡X轴数据
              'Xdata'=>$Xdata,                                  //X轴数据
              'count'=>$count,                                  //总收入
              'pledge'=>$pledge/100,                            //所有押金
              'UserSingle'=>$UserSingles,                       //普通用户单次消费总额         
              'CompanySingle'=>$CompanySingles,                 //集团用户单次消费总额
              'UserMonth'=>sprintf("%.2f",$UserMonths),         //普通用户月卡充值总额
              'CompanyMonth'=>sprintf("%.2f",$CompanyMonths),   //集团用户月卡充值总额
            ];


            $this->setOutPut('日期');
            break;
          default:
            $this->setOutPut('未知');
          }
          die;
    }

    function DataProcessing($last,$data){
        $arr=[];
        for($i=1 ; $i<$last+1 ; $i++){
          $arr[$i]=0;
        }
        foreach($data as $k => $v){
          $timestrap=date('d',strtotime($data[$k]['time']));
          $arr[preg_replace('/^0*/', '', $timestrap)]=$data[$k]['money'];
        }
        return $arr;
    }


     /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后，求之前可以采用泰勒公式)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    function diffBetweenTwoDays ($day1, $day2)
    {
      $second1 = strtotime($day1);
      $second2 = strtotime($day2);
        
      if ($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
      }
      return ($second1 - $second2) / 86400;
    }





}
