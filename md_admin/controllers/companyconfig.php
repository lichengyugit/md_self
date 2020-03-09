<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class companyconfig extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "company");
    }

    /**
     * 集团配置列表
     */
    public function companyConfigList()
    {   $this->checkAuth();
        //$this->msg('添加成功','companyList','ok');exit;
        F()->Resource_module->setTitle('集团配置列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/companyConfigList";
        $nums=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $companyConfigList=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getAllCompanyConfigByAttr($showpage['limit'],$this->parames);
        foreach($companyConfigList as$k=>$v){
            $companyIdArray[]=$v['company_id'];
            $companyConfigList[$k]['card_categories']=json_decode($v['card_categories'],true);
        }
//        echo '<pre />';
//        var_dump($companyConfigList);die;
        $companyList=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn(implode(",", $companyIdArray));
        if(is_array($companyList)){
            foreach($companyConfigList as $k=>$v){
                foreach($companyList as $k1=>$v1){
                    if($v['company_id']==$v1['id']){
                        $companyConfigList[$k]['company_name']=$v1['name'];
                        $companyConfigList[$k]['company_status']=$v1['status'];
                    }
                }
            }
        }
        $this->smarty->assign('List',$companyConfigList);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('companyConfig/list.phtml');
    }
    
    
    /**
     * 新增与修改集团页面
     */
    public function actionCompanyConfig(){
        $this->checkAuth();
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $payType=$this->payType();
        $this->smarty->assign('payType',$payType);
        $companyWhere=array('id'=>$this->parames['company_id']);
        if(array_key_exists('id', $this->parames)){
            $configWhere=array('id'=>$this->parames['id']);
            //$moneyWhere=array('company_id'=>$this->parames['company_id']);
        }else{
            //$moneyWhere=$configWhere=array('company_id'=>$this->parames['company_id']);
            $configWhere=array('company_id'=>$this->parames['company_id']);
        }
        $this->parames['id']=$this->parames['company_id'];
        unset($this->parames['company_id']);
        $companyInfo=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr($companyWhere);    
        $this->smarty->assign('companyInfo',$companyInfo);
        $companyConfigInfo=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getCompanyConfigInfoByAttr($configWhere);
        if($companyConfigInfo['pay_type']=='1'){
            $where=array('company_id'=>$companyConfigInfo['company_id']);
            $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($where);
            $companyConfigInfo['balance']=$CompanyMoneyInfo['balance'];
            $companyConfigInfo['is_over']=$CompanyMoneyInfo['is_over'];
            $companyConfigInfo['over_time']=$CompanyMoneyInfo['over_time'];
            $companyConfigInfo['give_money']=$CompanyMoneyInfo['give_money'];
        }
        if(count($companyConfigInfo)>0){
            F()->Resource_module->setTitle('修改集团配置');
            if($companyConfigInfo['pay_type']=='3'){//单次收费
                $companyConfigInfo['charge_rule_count']=count(json_decode($companyConfigInfo['charge_rule'],true));
                $companyConfigInfo['charge_rule']=json_encode($companyConfigInfo['charge_rule']);
            }elseif($companyConfigInfo['pay_type']=='2'||$companyConfigInfo['pay_type']=='4'||$companyConfigInfo['pay_type']=='5'){
                $companyConfigInfo['charge_rule_count']='0';
            }elseif($companyConfigInfo['pay_type']=='8'){
                $companyConfigInfo['card_categories']=json_decode($companyConfigInfo['card_categories'],true);
                $companyConfigInfo['charge_rule_count']='0';
            }
//             elseif($companyConfigInfo['pay_type']=='1'){//资金池
//                 $companyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($moneyWhere);
//                 $companyConfigInfo['charge_rule_count']='0';
//                 $companyConfigInfo['charge_rule']=json_encode($companyConfigInfo['charge_rule']);
//                 $this->smarty->assign('companyMoneyInfo',$companyMoneyInfo);
//             }
            else{
                $companyConfigInfo['charge_rule_count']='0';
                $companyConfigInfo['charge_rule']=json_encode($companyConfigInfo['charge_rule']);
            }
            if(empty($companyConfigInfo['charge_rule'])){
                $companyConfigInfo['charge_rule']='0';
            }
            $companyConfigInfo['over_time']=date('Y-m-d H:i:s',isset($companyConfigInfo['over_time'])?$companyConfigInfo['over_time']:0);
            $this->smarty->assign('companyConfigInfo',$companyConfigInfo);
            $this->smarty->view('companyConfig/update.phtml');
        }else{
            F()->Resource_module->setTitle('添加集团配置');
            $this->smarty->view('companyConfig/insert.phtml');
        }
    }
   



    /**
     * 新增集团配置
     */
    public function saveCompanyConfig(){
        $this->parames['name']=$this->payType()[$this->parames['pay_type']];
        if($this->parames['pay_type']=='3'){
            $this->parames['charge_rule']=json_encode($this->parames['charge_rule_price']);
            unset($this->parames['charge_rule_price']);
        }elseif($this->parames['pay_type']=='1'){
            $data['company_id']=$this->parames['company_id'];
            //$data['balance']=$this->parames['company_money'];
            unset($this->parames['company_money']);
            $saveCompanyMoney=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->saveCompanyMoney($data);
        }elseif($this->parames['pay_type']=='8'){
            $parames=$this->parames;
            $data=[];
            if(isset($parames['month_categories'])){
                $data['0']['money']=$parames['month_categories'];
                $data['0']['name']='包月';
                $data['0']['card_type']=3;
                $data['0']['m_set_time']=isset($parames['m_set_time'])?$parames['m_set_time']:0;
                $data['0']['m_giving_time']=isset($parames['m_giving_time'])?$parames['m_giving_time']:0;
                $data['0']['time']=$parames['m_set_time']*24*3600+$parames['m_giving_time']*24*3600;
//                $data['0']['time']=2592000;
                unset($this->parames['month_categories']);
                unset($this->parames['m_set_time']);
                unset($this->parames['m_giving_time']);
            }
            if(isset($parames['quarter_categories'])){
                $data['1']['money']=$parames['quarter_categories'];
                $data['1']['name']='包季';
                $data['1']['card_type']=2;
                $data['1']['q_set_time']=isset($parames['q_set_time'])?$parames['q_set_time']:0;
                $data['1']['q_giving_time']=isset($parames['q_giving_time'])?$parames['q_giving_time']:0;
                $data['1']['time']=$parames['q_set_time']*24*3600+$parames['q_giving_time']*24*3600;
//                $data['1']['time']=7776000;
                unset($this->parames['quarter_categories']);
                unset($this->parames['q_set_time']);
                unset($this->parames['q_giving_time']);
            }
            if(isset($parames['half_year_categories'])){
                $data['2']['money']=$parames['half_year_categories'];
                $data['2']['name']='半年';
                $data['2']['card_type']=4;
                $data['2']['hy_set_time']=isset($parames['hy_set_time'])?$parames['hy_set_time']:0;
                $data['2']['hy_giving_time']=isset($parames['hy_giving_time'])?$parames['hy_giving_time']:0;
                $data['2']['time']=$parames['hy_set_time']*24*3600+$parames['hy_giving_time']*24*3600;
//                $data['2']['time']=15552000;
                unset($this->parames['half_year_categories']);
                unset($this->parames['hy_set_time']);
                unset($this->parames['hy_giving_time']);
            }
            if(isset($parames['year_categories'])){
                $data['3']['money']=$parames['year_categories'];
                $data['3']['name']='包年';
                $data['3']['card_type']=1;
                $data['3']['y_set_time']=isset($parames['y_set_time'])?$parames['y_set_time']:0;
                $data['3']['y_giving_time']=isset($parames['y_giving_time'])?$parames['y_giving_time']:0;
                $data['3']['time']=$parames['y_set_time']*24*3600+$parames['y_giving_time']*24*3600;
//                $data['3']['time']=31104000;
                unset($this->parames['year_categories']);
                unset($this->parames['y_set_time']);
                unset($this->parames['y_giving_time']);
            }
            $this->parames['card_categories']=json_encode($data);
        }
//        echo '<pre />';
//        var_dump($this->parames);die;
        $save=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->saveCompanyConfig($this->parames);
        if($save>0){
//            $comdata=[
//                'lp01'=>date('Y-m-d H:i:s',time()),
//                'em_info'=>[[
//                    'lp02'=>$save,
//                    'lp03'=>$this->parames['name'],
//                    'lp04'=>[
//                        'pledge_money'=>$this->parames['pledge_money'],
//                        'constraint_card'=>$this->parames['constraint_card'],
//                        'is_pledge'=>$this->parames['is_pledge'],
//                        'charge_rule'=>$this->parames['charge_rule'],
//                        'vip_money'=>isset($this->parames['vip_money'])?$this->parames['vip_money']:'',
//                        'prepay'=>isset($this->parames['prepay'])?$this->parames['prepay']:'',
//                        'card_categories'=>isset($data)?$data:'',
//                        'is_bottom'=>$this->parames['is_bottom'],
//                        'bottom_cost'=>$this->parames['bottom_cost'],
//                        'how_battery'=>$this->parames['how_battery'],
//                        'pay_type'=>$this->parames['pay_type'],
//                    ],
//                    'lp05'=>$this->parames['company_id']
//                ]]
//
//            ];
//            $url=ML_URL."a52c6ce6";
////            $url="http://47.100.19.81/TESTDogWood_api/wechat/wechat/a52c6ce6";
//            $outPut=$this->apiAndData($url, $comdata);
//            get_log()->log_api('<接口测试> #### 接口名：saveCompanyConfig 作用：调用魔力同步企业配置通知接口参数：'.json_encode($data));
//            get_log()->log_api('<接口测试> #### 接口名：saveCompanyConfig 作用：调用魔力同步企业配置通知接口后获取返回值：'.json_encode($outPut));
            //===========================================操作内容记录
            $isPledge=[0=>'企业支付',1=>'用户支付',2=>'资金池'];
            $cardCategories=[1=>'年卡',2=>'季卡',3=>'月卡',4=>'半年卡'];
            $isBottom=[0=>'企业支付',1=>'用户支付'];
            $howBattery=[0=>'单颗',1=>'多颗'];
            $this->parames['is_pledge']=$isPledge[$this->parames['is_pledge']];
            $this->parames['card_categories']=$cardCategories[$this->parames['card_categories']];
            $this->parames['is_bottom']=$isBottom[$this->parames['is_bottom']];
            $this->parames['how_battery']=$howBattery[$this->parames['how_battery']];
            $insertData[0]=$this->parames;
            $tableName[0]['table_name']='md_company_config : 集团配置表';
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            //===========================================
            $this->msg('添加成功','/companyConfigList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->msg('添加失败','/companyConfigList','error');
        }
    }
    
    
    /**
     * 修改集团
     */
    public function updateCompanyConfig(){
        $vip_money='0';
        $prepay='0';
        $charge_rule=null;
        $parames=$this->parames;

        if(array_key_exists('vip_money', $this->parames)){
            $this->parames['prepay']=null;
        }elseif(array_key_exists('prepay', $this->parames)){
            $this->parames['vip_money']=null;
            $this->parames['charge_rule']=null;
        }else{
            $this->parames['vip_money']=null;
            $this->parames['prepay']=null;
        }
        if($this->parames['pay_type']=='3'){
            $this->parames['charge_rule']=json_encode($this->parames['charge_rule_price']);
            unset($this->parames['charge_rule_price']);
        }
        if(empty($this->parames['constraint_card'])){
            $this->parames['constraint_card']=0;
        }
        if($this->parames['pay_type']=='8'){
            $parames=$this->parames;
            $data=[];
            if(isset($parames['month_categories'])){
                $data['0']['money']=$parames['month_categories'];
                $data['0']['name']='包月';
                $data['0']['card_type']=3;
                $data['0']['m_set_time']=isset($parames['m_set_time'])?$parames['m_set_time']:0;
                $data['0']['m_giving_time']=isset($parames['m_giving_time'])?$parames['m_giving_time']:0;
                $data['0']['time']=$parames['m_set_time']*24*3600+$parames['m_giving_time']*24*3600;
//                $data['0']['time']=2592000;
                unset($this->parames['month_categories']);
                unset($this->parames['m_set_time']);
                unset($this->parames['m_giving_time']);
            }

            if(isset($parames['quarter_categories'])){
                $data['1']['money']=$parames['quarter_categories'];
                $data['1']['name']='包季';
                $data['1']['card_type']=2;
                $data['1']['q_set_time']=isset($parames['q_set_time'])?$parames['q_set_time']:0;
                $data['1']['q_giving_time']=isset($parames['q_giving_time'])?$parames['q_giving_time']:0;
                $data['1']['time']=$parames['q_set_time']*24*3600+$parames['q_giving_time']*24*3600;
//                $data['1']['time']=7776000;
                unset($this->parames['quarter_categories']);
                unset($this->parames['q_set_time']);
                unset($this->parames['q_giving_time']);
            }

            if(isset($parames['half_year_categories'])){
                $data['2']['money']=$parames['half_year_categories'];
                $data['2']['name']='半年';
                $data['2']['card_type']=4;
                $data['2']['hy_set_time']=isset($parames['hy_set_time'])?$parames['hy_set_time']:0;
                $data['2']['hy_giving_time']=isset($parames['hy_giving_time'])?$parames['hy_giving_time']:0;
                $data['2']['time']=$parames['hy_set_time']*24*3600+$parames['hy_giving_time']*24*3600;
//                $data['2']['time']=15552000;
                unset($this->parames['half_year_categories']);
                unset($this->parames['hy_set_time']);
                unset($this->parames['hy_giving_time']);
            }

            if(isset($parames['year_categories'])){
                $data['3']['money']=$parames['year_categories'];
                $data['3']['name']='包年';
                $data['3']['card_type']=1;
                $data['3']['y_set_time']=isset($parames['y_set_time'])?$parames['y_set_time']:0;
                $data['3']['y_giving_time']=isset($parames['y_giving_time'])?$parames['y_giving_time']:0;
                $data['3']['time']=$parames['y_set_time']*24*3600+$parames['y_giving_time']*24*3600;
//                $data['3']['time']=31104000;
                unset($this->parames['year_categories']);
                unset($this->parames['y_set_time']);
                unset($this->parames['y_giving_time']);
            }
            $this->parames['card_categories']=json_encode($data);
        }
/*        if($this->parames['is_bottom']==0){
            $this->parames['bottom_cost']=0;
        }*/
        // if($this->parames['pay_type']!='1'){
        //     $where=array('company_id'=>$this->parames['company_id']);
        //     $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($where);
        //     if($CompanyMoneyInfo['balance']==0 && $CompanyMoneyInfo['give_money']==0){
        //         $deleteCompanyMoney=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->deleteBath($where);
        //     }else{
        //         $this->msg('资金池集团在池内金额使用完之前不可切换支付模式','/companyConfigList','error');
        //     }
        // }
        // else{
        //     $parames['company_money']=trim($parames['company_money']);
        //     $parames['give_money']=trim($parames['give_money']);
        //     $this->form_validation->set_data($parames);
        //     $this->form_validation->set_rules('company_money','充值金额','integer|required');
        //     $this->form_validation->set_rules('give_money','赠送资金池','integer|required');
        //     $this->form_validation->run();
        //     if($this->form_validation->run() === FALSE){
        //          $this->msg($this->form_validation->validation_error(),'/actionCompanyConfig?company_id='.$parames['company_id'],'error');
        //     }else{
        //         $where=array('company_id'=>$this->parames['company_id']);
        //         $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($where);
        //         if(array_key_exists('balance',$CompanyMoneyInfo)){
        //             $MoneyData=[
        //                 'company_id'=>$parames['company_id'],
        //                 'balance'=>$CompanyMoneyInfo['balance']+$parames['company_money'],
        //                 'give_money'=>$CompanyMoneyInfo['give_money']+$parames['give_money'],
        //                 'create_time'=>time(),
        //                 'create_date'=>date("Y-m-d H:i:s",time())
        //             ];
        //             if(array_key_exists('is_over',$parames)){
        //                 $MoneyData['is_over']=1;
        //                 $MoneyData['over_time']=strtotime($parames['isOverTime']);
        //             }else{
        //                 $MoneyData['is_over']=0;
        //                 $MoneyData['over_time']=time();
        //             }
        //             $CompanyMoneyResult=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->updateCompanyMoneyByAttr($MoneyData);
        //             //记录
        //             $reData=[
        //                 'company_id'=>$parames['company_id'],
        //                 'amount'=>$parames['company_money'],
        //                 'give_pay'=>$parames['give_money'],
        //                 'user_id'=>$_SESSION['user_id']
        //             ];
        //             $record=M_Mysqli_Class('md_lixiang','CompanyMoneyRecord')->saveCompanyMoneyRecord($reData);
        //             $result=$CompanyMoneyResult;
        //             unset($this->parames['company_money']);
        //             unset($this->parames['give_money']);
        //             unset($this->parames['is_over']);
        //             unset($this->parames['isOverTime']);
        //         }else{
        //             //如果配置不存在 则新增配置
        //             //无配置需新增配置
        //             $MoneyData=[
        //                 'company_id'=>$parames['company_id'],
        //                 'balance'=>$CompanyMoneyInfo['balance']+$parames['company_money'],
        //                 'give_money'=>$CompanyMoneyInfo['give_money']+$parames['give_money'],
        //                 'create_time'=>time(),
        //                 'create_date'=>date("Y-m-d H:i:s",time())
        //             ];
        //             if(array_key_exists('is_over',$parames)){
        //                 $MoneyData['is_over']=1;
        //                 $MoneyData['over_time']=strtotime($parames['isOverTime']);
        //             }else{
        //                 $MoneyData['is_over']=0;
        //                 $MoneyData['over_time']=time();
        //             }
        //             $saveCompanyMoney=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->saveCompanyMoney($MoneyData);
        //             //记录
        //             $reData=[
        //                 'company_id'=>$parames['company_id'],
        //                 'amount'=>$parames['company_money'],
        //                 'give_pay'=>$parames['give_money'],
        //                 'user_id'=>$_SESSION['user_id']
        //             ];
        //             $record=M_Mysqli_Class('md_lixiang','CompanyModelRecord')->saveCompanyMoneyRecord($reData);
        //             $result=$saveCompanyMoney;
        //             unset($this->parames['company_money']);
        //             unset($this->parames['give_money']);
        //             unset($this->parames['is_over']);
        //             unset($this->parames['isOverTime']);
        //         }
            //     $this->parames['name']=$this->payType()[$this->parames['pay_type']];
            //     $update=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->updateCompanyConfigById($this->parames);
            //     if($update || $result){
            //         $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            //         $this->msg('修改成功','/companyConfigList','ok');die;
            //     }else{
            //         $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            //         $this->msg('修改失败','/companyConfigList','error');die;
            //     }
            //     die;
            // }
        //}
        if($this->parames['pay_type']!=8){
            $this->parames['card_categories']=null;
        }
        $this->parames['name']=$this->payType()[$this->parames['pay_type']];
        $beforeData=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getAllCompanyConfigByAttr(1,['id'=>$this->parames['id']]);
        $update=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->updateCompanyConfigById($this->parames);
        if($update){
            //测试服务器a52c6ce6
//            $comdata=[
//                'lp01'=>date('Y-m-d H:i:s',time()),
//                'em_info'=>[[
//                    'lp02'=>$this->parames['id'],
//                    'lp03'=>$this->parames['name'],
//                    'lp04'=>[
//                        'pledge_money'=>$this->parames['pledge_money'],
//                        'constraint_card'=>$this->parames['constraint_card'],
//                        'is_pledge'=>$this->parames['is_pledge'],
//                        'charge_rule'=>$this->parames['charge_rule'],
//                        'vip_money'=>isset($this->parames['vip_money'])?$this->parames['vip_money']:'',
//                        'prepay'=>isset($this->parames['prepay'])?$this->parames['prepay']:'',
//                        'card_categories'=>isset($data)?$data:'',
//                        'is_bottom'=>$this->parames['is_bottom'],
//                        'bottom_cost'=>$this->parames['bottom_cost'],
//                        'how_battery'=>$this->parames['how_battery'],
//                        'pay_type'=>$this->parames['pay_type'],
//                    ],
//                    'lp05'=>$this->parames['company_id']
//                ]]
//
//            ];
//            $url=ML_URL."a52c6ce6";
////            $url="http://47.100.19.81/TESTDogWood_api/wechat/wechat/a52c6ce6";
//            $outPut=$this->apiAndData($url, $comdata);
//            get_log()->log_api('<接口测试> #### 接口名：updateCompanyConfig 作用：调用魔力同步企业修改配置通知接口参数：'.json_encode($data));
//            get_log()->log_api('<接口测试> #### 接口名：updateCompanyConfig 作用：调用魔力同步企业修改配置通知接口后获取返回值：'.json_encode($outPut));

            //=================================操作内容记录
            $afterData=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getAllCompanyConfigByAttr(1,['id'=>$this->parames['id']]);
            $tableName[0]['table_name']='md_company_config : 集团配置表';
            $isPledge=[0=>'企业支付',1=>'用户支付',2=>'资金池'];
            $cardCategories=[1=>'年卡',2=>'季卡',3=>'月卡',4=>'半年卡'];
            $isBottom=[0=>'企业支付',1=>'用户支付'];
            $howBattery=[0=>'单颗',1=>'多颗'];
            $companyConfigRes=$this->arrayNewWornData($afterData[0],$beforeData[0]);
            if($companyConfigRes){
                for($i=0; $i<count($companyConfigRes); $i++){
                    if(isset($companyConfigRes[$i]['clm_name']) && $companyConfigRes[$i]['clm_name']=='is_pledge'){
                        $companyConfigRes[$i]['old_string']=$isPledge[$companyConfigRes[$i]['old_string']];
                        $companyConfigRes[$i]['new_string']=$isPledge[$companyConfigRes[$i]['new_string']];
                    }
                    if(isset($companyConfigRes[$i]['clm_name']) && $companyConfigRes[$i]['clm_name']=='card_categories'){
                        $companyConfigRes[$i]['old_string']=$cardCategories[$companyConfigRes[$i]['old_string']];
                        $companyConfigRes[$i]['new_string']=$cardCategories[$companyConfigRes[$i]['new_string']];
                    }
                    if(isset($companyConfigRes[$i]['clm_name']) && $companyConfigRes[$i]['clm_name']=='is_bottom'){
                        $companyConfigRes[$i]['old_string']=$isBottom[$companyConfigRes[$i]['old_string']];
                        $companyConfigRes[$i]['new_string']=$isBottom[$companyConfigRes[$i]['new_string']];
                    }
                    if(isset($companyConfigRes[$i]['clm_name']) && $companyConfigRes[$i]['clm_name']=='how_battery'){
                        $companyConfigRes[$i]['old_string']=$howBattery[$companyConfigRes[$i]['old_string']];
                        $companyConfigRes[$i]['new_string']=$howBattery[$companyConfigRes[$i]['new_string']];
                    }
                }
                $insertData[0]=$companyConfigRes;
            }else{
                $insertData=[];
            }

            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
            //==================================

            $this->msg('修改成功','/companyConfigList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/companyConfigList','error');
        }
    }
    



    /**
     * 集团用户支付方式配置列表
     */
    public function payType(){
        return array(
            "1"=>"资金池",
            "2"=>"月卡",
            "3"=>"单次收费",
            "4"=>"季卡",
            "5"=>"年卡",
            "6"=>"预付费",
            "7"=>"固定金额",
            "8"=>"卡种"
        );
    }
    
    /**
     * 修改集团配置状态
     */
    public function actionCompanyConfigStatus(){
        $this->checkAuth();
        $beforeData=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getAllCompanyConfigByAttr(1,['id'=>$this->parames['id']]);
        $updateCompany=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->updateCompanyConfigById($this->parames);
        if($updateCompany){

            //============================操作内容记录
            $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($beforeData[0]['company_id']);
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['company_name'=>$companyData[0]['name'], 'name'=>$beforeData[0]['name'],'status'=>$status[$this->parames['status']].','.$status[$beforeData[0]['status']]];
            $tableName[0]['table_name']='md_company_config : 集团配置表';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //============================

            $this->msg('操作成功','/companyConfigList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/companyConfigList','error');
        }
    }


    /*
     * 添加集团用户
     */
    public function addcompanyuser(){
        F()->Resource_module->setTitle('添加集团用户');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $parames+=[
            'user_flag'=>1,
            'user_type'=>1
        ];
        $_FILES['sig']='sig';
        if(array_key_exists('user_name', $this->parames)){
            $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$parames['site_id']]);
            $adress=F()->Gaode_module->getAddress($siteData['longitude']);
            if($adress['status'] == 0){
                $array=[
                    'province'=>'',
                    'city'=>'',
                    'district'=>''
                ];
            }else{
                if(empty($adress['regeocode']['addressComponent']['city'])){
                    $adress['regeocode']['addressComponent']['city']=$adress['regeocode']['addressComponent']['province'];
                }
                $array=[
                    'province'=>empty($adress['regeocode']['addressComponent']['province'])?'':$adress['regeocode']['addressComponent']['province'],
                    'city'=>empty($adress['regeocode']['addressComponent']['city'])?'':$adress['regeocode']['addressComponent']['city'],
                    'district'=>empty($adress['regeocode']['addressComponent']['district'])?'':$adress['regeocode']['addressComponent']['district']
                ];
            }
            $parames['mobile']=preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/','',$parames['mobile']);
            $parames['card_number']=preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/','',$parames['card_number']);
            $parames['user_name']=preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/','',$parames['user_name']);
            $arr=[
            'mobile'=>$parames['mobile'],
            'card_number'=>$parames['card_number'],
            'attr_id'=>$parames['attr_id']
            ];
            $result=M_Mysqli_Class('md_lixiang','UserModel')->inspectUser($arr);
            if($result>0){
                $arr+=['user_flag'=>1];
                M_Mysqli_Class('md_lixiang','UserModel')->updateUserByAttr(['site_id'=>$parames['site_id']],$arr);                          //新增方法
                $this->msg('该用户已存在','/addcompanyuser','error');
            }else{
                $this->form_validation->set_data($parames);
                $this->form_validation->set_rules('card_number','身份证号','exact_length[18]|required');
                $this->form_validation->set_rules('mobile','手机号','exact_length[11]|required');
                $this->form_validation->run();
                if($this->form_validation->run() === FALSE){
                    $this->msg($this->form_validation->validation_error(),'/addcompanyuser','error');
                }else{
                    md5($parames['password']);
                    $array+=[
                        'user_name'=>$parames['user_name'],
                        'mobile'=>$parames['mobile'],
                        'password'=>md5($parames['password']),
                        'attr_id'=>$parames['attr_id'],
                        'user_flag'=>$parames['user_flag'],
                        'user_type'=>$parames['user_type'],
                        'invite_code'=>$this->make_invite_codes(),
                        'nick_name'=>urlencode($parames['user_name']),
                        'identification'=>1,
                        'name'=>$parames['user_name'],
                        'card_number'=>$parames['card_number'],
                        'site_id'=>$parames['site_id']
                    ];
                    $user=M_Mysqli_Class('md_lixiang','UserModel')->addUser($array);
                    $card=[
                        'user_id'=>$user,
                        'name'=>$parames['user_name'],
                        'card_number'=>$parames['card_number']
                    ];
                    $cardmod=M_Mysqli_Class('md_lixiang','IdCardModel')->addIdCard($card);
                    $wallet=M_Mysqli_Class('md_lixiang','UserWalletModel')->addWallet(['user_id'=>$user]);
                    if(count($user)>0 && count($cardmod)>0 && count($wallet)){
                                    $some=[
                                       'mw04'=>$parames['user_name'],
                                       'mw15'=>$parames['card_number'],
                                       'mw14'=>$parames['mobile'],
                                       'mw12'=>$user,
                                       'mw13'=>date('Y-m-d H:i:s'),
                                   ];
                                    $conmpanyConfig=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getCompanyConfigInfoByAttr(['company_id'=>$parames['attr_id']]);
                                    if($conmpanyConfig['how_battery']==1){
                                        $some+=[
                                            'mw18'=>'F'
                                        ];
                                    }
                                  $outPut['call']= $this->regInform($some);
                                  get_log()->log_api('<接口测试> #### 接口名：checkIdCard 作用：调用魔力后台微信注册会员通知接口参数：'.json_encode($some));
                                  get_log()->log_api('<接口测试> #### 接口名：checkIdCard 作用：调用魔力后台微信注册会员通知接口后获取返回值：'.json_encode($outPut['call']));


                        //============================操作内容记录
                        $tableName[0]['table_name']='md_user : 用户表';
                        $tableName[1]['table_name']='md_user_wallet : 用户钱包表';
                        $userFlag=[0=>'普通用户',1=>'集团用户'];
                        $userType=[0=>'普通用户',1=>'集团用户'];
                        $array['password']=$this->parames['password'];
                        $array['nick_name']=$this->parames['nick_name'];
                        $array['user_type']=$userType[$array['user_type']];
                        $array['user_flag']=$userType[$array['user_flag']];
                        $array['attr_id']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($array['attr_id'])[0]['name'];;
                        $array['site_id']=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$array['site_id']])['site_name'];
                        $insertData=[
                            0=>$array,
                            1=>['user_id'=>$user]
                        ];
                        $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
                        //============================
                        $this->msg('添加成功','/addcompanyuser','ok');
                    }else{
                        $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);  //处理
                        $this->msg('添加失败','/addcompanyuser','error');
                    }
                }
            }
        }elseif(array_key_exists('excel_file',$_FILES)){
            $post=$_POST;
            $post+=$parames;
            $ex = $_FILES['excel_file'];
            $some['em_info']=[];
            $n=0;
            $excel=F()->Excel_module->importExecl($ex['tmp_name']);
            $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$post['site_id']]);
            $adress=F()->Gaode_module->getAddress($siteData['longitude']);
            if($adress['status'] == 0){
                $province='';
                $city='';
                $district='';
            }else{
                if(empty($adress['regeocode']['addressComponent']['city'])){
                    $adress['regeocode']['addressComponent']['city']=$adress['regeocode']['addressComponent']['province'];
                }
                $province=$adress['regeocode']['addressComponent']['province'];
                $city=$adress['regeocode']['addressComponent']['city'];
                $district=$adress['regeocode']['addressComponent']['district'];
            }
            /*$company=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getCompanyConfigInfoByAttr(['id'=>$_POST['attr_id']]);*/
            $arr=[];
            foreach ($excel as $k => $v) {
                if($excel[$k]['A']==''){
                    unset($excel[$k]);
                }else{
                    $passWrod=$excel[$k]['D'];
                    $arr[$k]=[
                        'user_name' => preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/','',$excel[$k]['A']),
                        'mobile' => preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/','',$excel[$k]['C']),
                        'password' => md5($excel[$k]['D']),
                        'attr_id' => $post['attr_id'],
                        'user_flag' => $post['user_flag'],
                        'user_type' => $post['user_type'],
                        'invite_code'=>$this->make_invite_codes(),
                        'nick_name'=>urlencode($excel[$k]['A']),
                        'identification'=>1,
                        'name'=>$excel[$k]['A'],
                        'site_id'=>$post['site_id'],
                        'card_number'=>preg_replace('/(\s|\&nbsp\;|　|\xc2\xa0)/','',$excel[$k]['B']),
                        'province'=>$province,
                        'city'=>$city,
                        'district'=>$district
                    ];
                }
            }
            $some['em_info']=[];
            $n=0;
            for( $i=2 ; $i<count($arr)+1 ; $i++ ){
                $Inspect=[
                    'mobile' => $arr[$i]['mobile'],
                    'card_number'=>$arr[$i]['card_number'],
                    'attr_id'=>$arr[$i]['attr_id']
                ];
                $result=M_Mysqli_Class('md_lixiang','UserModel')->inspectUser($Inspect);
                if($result>0){
                    M_Mysqli_Class('md_lixiang','UserModel')->updateUserByAttr(['site_id'=>$post['site_id']],$Inspect);
                    continue;
                }else{
                    $this->form_validation->set_data($arr[$i]);
                    $this->form_validation->set_rules('card_number',$arr[$i]['user_name'].'身份证号','exact_length[18]|required');
                    $this->form_validation->set_rules('mobile',$arr[$i]['user_name'].'手机号','exact_length[11]|required');
                    $this->form_validation->run();
                    if($this->form_validation->run() === FALSE){
                        $this->msg($this->form_validation->validation_error(),'/addcompanyuser','error');
                    }else{
                        $insert=M_Mysqli_Class('md_lixiang','UserModel')->addUser($arr[$i]);
                        if(isset($insert)){
                            $data=[
                                'user_id'=>$insert,
                                'name'=>$arr[$i]['user_name'],
                                'card_number'=>$arr[$i]['card_number']
                            ];
                            $idcard=M_Mysqli_Class('md_lixiang','IdCardModel')->addIdCard($data);
                            $wallet=M_Mysqli_Class('md_lixiang','UserWalletModel')->addWallet(['user_id'=>$insert]);

                            //============================操作内容记录
                            $tableName[0]['table_name']='md_user : 用户表';
                            $tableName[1]['table_name']='md_user_wallet : 用户钱包表';
                            $userFlag=[0=>'普通用户',1=>'集团用户'];
                            $userType=[0=>'普通用户',1=>'集团用户'];
                            $arr[$i]['password']=$passWrod;
                            $arr[$i]['nick_name']=$this->parames['nick_name'];
                            $arr[$i]['user_type']=$userType[$arr[$i]['user_type']];
                            $arr[$i]['user_flag']=$userType[$arr[$i]['user_flag']];
                            $arr[$i]['site_id']=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$arr[$i]['site_id']])['site_name'];
                            $arr[$i]['attr_id']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($arr[$i]['attr_id'])[0]['name'];
                            $insertData[$i]=[
                                0=>$arr[$i],
                                1=>['user_id'=>$insert]
                            ];
                            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData[$i],$tableName);
                            //============================

                            if(!isset($idcard)){
                                $this->msg('第'.$i.'条数据关联idcard表失败');die;
                            }
                            $some['em_info'][$n++]=[
                               'mw04'=>$arr[$i]['user_name'],
                               'mw15'=>$arr[$i]['card_number'],
                               'mw14'=>$arr[$i]['mobile'],
                               'mw12'=>$insert,
                               'mw13'=>date('Y-m-d H:i:s')
                           ];
                            $conmpanyConfig=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getCompanyConfigInfoByAttr(['company_id'=>$post['attr_id']]);
                            if($conmpanyConfig['how_battery']==1){
                                $some['em_info'][$n]+=[
                                    'mw18'=>'F'
                                ];
                            }
                        }else{
                            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);  //处理
                            $this->msg('第'.$i.'条数据添加user表失败');die;
                        }
                    }
                }
            }
            /////////////////////接口传送
            $outPut['call']= $this->regInforms($some);
            get_log()->log_api('<接口测试> #### 接口名：checkIdCard 作用：调用魔力后台微信注册会员通知接口参数：'.json_encode($some));
            get_log()->log_api('<接口测试> #### 接口名：checkIdCard 作用：调用魔力后台微信注册会员通知接口后获取返回值：'.json_encode($outPut['call']));

            $this->msg('操作成功','/addcompanyuser','ok');
        }elseif(IS_AJAX){
            $parames['site_status']=1;
            $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteData('',$parames);
            $this->setOutPut($siteData);die;
        }else{
            $url = "/addcompanyuser";
            $nums=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->getNumByAttr($this->parames);
            $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
            $conmpany=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompanyByAttr($showpage['limit'],[]);
            $this->smarty->assign('arr',$conmpany);
            $this->smarty->view('companyConfig/adduser.phtml');
        }
    }


    //批量请求接口
    private function regInforms($data){
        $str='http://47.100.19.81/TESTDogWood_api/wechat/wechat/c9a4b934d';
        $url=ML_URL."c9a4b934d";
       return $this->apiAndData($url, $data);
    }

    //单独请求接口
    private function regInform($data){
        $url=ML_URL."d31387a9";
       return $this->apiAndData($url, $data);
    }

    /*
     * 绑定业务员
     */
    public function companyBindUser(){
//        $this->checkAuth();
        F()->Resource_module->setTitle('用户列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        if(array_key_exists('companybinduuser', $parames)){
            $bind=M_Mysqli_Class('md_lixiang','CompanyModel')->updateCompanyByAttr(['bind_user'=>$parames['companybinduuser']],['id'=>$parames['id']]);
            if(isset($bind)){
                 $this->msg('绑定成功','/companyList','ok');die;
            }else{
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
                 $this->msg('绑定失败','/companyList','error');die;
            }
        }else{
            $comuser=M_Mysqli_Class('md_lixiang','UserModel')->getConditionUser(['user_flag'=>3,'status'=>0]);
            $this->smarty->assign('id',['id'=>$parames['id']]);
            $this->smarty->assign('user',$comuser);
            $this->smarty->view('companyConfig/bindCompany.phtml');
        }
    }


    /*
     * 集团资金池
     */
    public function companyCapitalPool(){
        $this->checkAuth();
        F()->Resource_module->setTitle('集团资金池配置');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames=$this->parames;
        $where=array('company_id'=>$parames['company_id']);
        //1.检查集团是否有资金池
        $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($where);
        if(!empty($CompanyMoneyInfo)){
            //3.如果集团有资金池 跳到修改资金池页面
            //跳转到 更新资金池页面
            $this->smarty->assign('companyInfo',$parames['company_id']);
            $this->smarty->assign('companyConfigInfo',$CompanyMoneyInfo);
            $this->smarty->view('companyConfig/CapitalPool.phtml');
        }else{
            //2.如果集团没有资金池 则跳到 创建资金池页面 走创建资金池流程
            //如果配置不存在 则新增配置
            //无配置需新增配置
            //跳转到创建资金池页面
            $MoneyData=[
                'company_id'=>$parames['company_id'],
                'balance'=>0,
                'give_money'=>0,
                'create_time'=>time(),
                'create_date'=>date("Y-m-d H:i:s",time()),
                'is_over'=>0,
                'over_time'=>time()
            ];
            $saveCompanyMoney=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->saveCompanyMoney($MoneyData);
            $where=array('company_id'=>$parames['company_id']);
            $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($where);
            $this->smarty->assign('companyInfo',$parames['company_id']);
            $this->smarty->assign('companyConfigInfo',$CompanyMoneyInfo);
            $this->smarty->view('companyConfig/CapitalPool.phtml');
        }
    }


    /*
     * 集团基金池更新
     */
    public function CapitalPoolUpConfig(){
        $parames=$this->parames;
        $where=array('company_id'=>$parames['company_id']);
        //1.检查集团是否有资金池
        $CompanyMoneyInfo=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->getCompanyMoneyInfoByAttr($where);
        $MoneyData=[
            'company_id'=>$parames['company_id'],
            'balance'=>$CompanyMoneyInfo['balance']+$parames['company_money'],
            'give_money'=>$CompanyMoneyInfo['give_money']+$parames['give_money'],
            'create_time'=>time(),
            'create_date'=>date("Y-m-d H:i:s",time())
        ];
        if(array_key_exists('is_over',$parames)){
            $MoneyData['is_over']=1;
            $MoneyData['over_time']=strtotime($parames['isOverTime']);
        }else{
            $MoneyData['is_over']=0;
            $MoneyData['over_time']=time();
        }
        $CompanyMoneyResult=M_Mysqli_Class('md_lixiang','CompanyMoneyModel')->updateCompanyMoneyByAttr($MoneyData);
        if($CompanyMoneyResult){
            //记录
            $reData=[
                'company_id'=>$parames['company_id'],
                'amount'=>$parames['company_money'],
                'give_pay'=>$parames['give_money'],
                'user_id'=>$_SESSION['user_id']
            ];
            $record=M_Mysqli_Class('md_lixiang','CompanyMoneyRecordModel')->saveCompanyMoneyRecord($reData);
            $this->msg('操作成功','/companyList','ok');
        }else{
            $this->msg('操作失败','/companyList','error');
        }
    }
















}












