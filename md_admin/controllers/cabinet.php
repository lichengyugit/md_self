<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class cabinet extends MY_Controller
{      

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "cabinet");
        $this->smarty->assign('cabinetType',$this->cabinetType());
    }


    /**
     * 机柜列表及搜索
     */
    public function cabinetList()
    {
        $parames=$this->parames;
        $this->checkAuth();
        F()->Resource_module->setTitle('机柜列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $uri=$this->makeSearchUrl($this->parames);
        $url = "cabinetList?".$uri;
        $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany([]);
        $cabinetCompanyNums=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetCompanyData('',$parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($cabinetCompanyNums));
        $limit=" LIMIT ".$showpage['limit'];
        $cabinetCompanyData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetCompanyData($limit,$parames);
        $parames['input_data']=isset($parames['input_data'])?$parames['input_data']:'';
        $parames['company_id']=isset($parames['company_id'])?$parames['company_id']:'';
        $parames['cabinet_type']=isset($parames['cabinet_type'])?$parames['cabinet_type']:'';
        $parames['operation_type']=isset($parames['operation_type'])?$parames['operation_type']:'';
        $parames['create_time']=isset($parames['create_time'])?$parames['create_time']:'';
        $parames['status']=isset($parames['status'])?$parames['status']:'';
        //如果为execl按钮走下面
        if(key_exists('execlbutton',$parames)){
            $execlData='';
            $title=['所属集团','机柜编号','机柜名称','业务类型','机柜类型','机柜地址','创建时间'];
            $operationType=[1=>'共享区',2=>'大B端',3=>'北海'];
            $cabinetType=[1=>'12轨机柜',2=>'9轨机柜'];
            for($i=0;$i<count($cabinetCompanyNums);$i++){
                $execlData[$i]['name'] =$cabinetCompanyNums[$i]['name'];
                $execlData[$i]['cabinet_number'] =$cabinetCompanyNums[$i]['cabinet_number'];
                $execlData[$i]['cabinet_name']   =$cabinetCompanyNums[$i]['cabinet_name'];
                $execlData[$i]['operation_type'] =$operationType[$cabinetCompanyNums[$i]['operation_type']];
                $execlData[$i]['cabinet_type']   =$cabinetType[$cabinetCompanyNums[$i]['cabinet_type']];
                $execlData[$i]['location']       =$cabinetCompanyNums[$i]['location'];
                $execlData[$i]['create_date']    =$cabinetCompanyNums[$i]['create_date'];
            }
            F()->Excel_module->exportExcel($title,$execlData,'机柜数据Execl列表','./',true);
        }
        $this->smarty->assign('cabinetCompanyData',$cabinetCompanyData);
        $this->smarty->assign('companyData',$companyData);
        $this->smarty->assign('parames',$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('cabinet/list.phtml');
    }


    
    /**
     * 新增与修改机柜页面
     */
    public function actionCabinet(){
        $this->checkAuth();
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(array_key_exists('id', $this->parames)){
            F()->Resource_module->setTitle('修改机柜');
            $info=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr($this->parames);
            $data['user_flag']=4;
            $agent=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttr($data);
            $jia[0]=0;
            $site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfo($jia);
            $this->smarty->assign('agent',$agent);
            $this->smarty->assign('site',$site);
            $this->smarty->assign('info',$info);
            $this->smarty->view('cabinet/update.phtml');
        }else{
            F()->Resource_module->setTitle('添加机柜');
            $this->smarty->view('cabinet/insert.phtml');
        }
    }
    
    /**
     * 新增机柜
     */
    public function saveCabinet(){
        if($_COOKIE['cabinet_type']==NULL){
            $this->msg('请先选择机柜类型','/actionCabinet','error');exit;
        }
        $parames=$this->parames;
        $this->form_validation->set_data($parames);
        $this->form_validation->set_rules('cabinet_number','机柜码','alpha_numericmin_length[1]|max_length[28]|required');
        $this->form_validation->run();
        if($this->form_validation->run()===FALSE){
            $this->msg($this->form_validation->validation_error(),'/selectCabinetType','error');
        }
        if(M_Mysqli_Class('md_lixiang','CabinetModel')->getAllBoxByAttr($parames)){
            $this->msg('此机柜已存在','/selectCabinetType','error');exit;
        }
        if($_COOKIE['cabinet_type']==1){
            $parames['cabinet_type']=1;
            $parames['port']=12;
        }elseif($_COOKIE['cabinet_type']==2){
            $parames['cabinet_type']=2;
            $parames['port']=9;
        }
        
        $save=M_Mysqli_Class('md_lixiang','CabinetModel')->saveCabinets($parames);

        if($save>0){

            //==============================操作内容记录
            $tableName[0]='md_cabinet';
            $insertData[0]=$parames;
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            //==============================

            $this->msg('添加成功','/selectCabinetType','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->msg('添加失败','/selectCabinetType','error');
        }
    }
    
    /**
     * 修改机柜
     */
    public function updateCabinet(){
        $parames=$this->parames;
        if(empty($parames['agent_id'])){
            unset($parames['agent_id']);
        }
        if(empty($parames['site_id'])){
            unset($parames['site_id']);
        }
        if($parames['cabinet_type']==1){
            $parames['port']=12;
        }elseif($parames['cabinet_type']==2){
            $parames['port']=9;
        }

        $update=M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinetById($parames);
        if($update){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('修改成功','/actionCabinet?id='.$this->parames['id'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/actionCabinet?id='.$this->parames['id'],'error');
        }
    }
    
    /**
     * 启用或禁用机柜
     */
    public function actionCabinetStatus(){
        $this->checkAuth();
        $beforeData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$this->parames['id']]);
        $updateCabinet=M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinetById($this->parames);
        if($updateCabinet){

            //====================================操作内容记录
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['cabinet_number'=>$beforeData['cabinet_number'], 'cabinet_name'=>$beforeData['cabinet_name'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_admin';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //======================================

            $this->msg('操作成功','/cabinetList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/cabinetList','error');
        }
    }

    /*
     * 删除机柜
     * */
    public function expurgateCabinet()
    {
        $this->checkAuth();
        $beforeData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$this->parames['id']]);
        $updateCabinet=M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinetById($this->parames);
        if($updateCabinet){

            //====================================操作内容记录
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['cabinet_number'=>$beforeData['cabinet_number'], 'cabinet_name'=>$beforeData['cabinet_name'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_admin';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //======================================

            $this->msg('操作成功','/cabinetList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/cabinetList','error');
        }
    }

    /*
     * 机柜撤柜
     * */
    public function recallCabinet(){
        $this->checkAuth();
        $beforeData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$this->parames['id']]);
        $updateCabinet=M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinetById($this->parames);
        if($updateCabinet){

            //====================================操作内容记录
            $status=[0=>'启用',1=>'禁用',2=>'删除',3=>'撤柜'];
            $insertData[0]=['cabinet_number'=>$beforeData['cabinet_number'], 'cabinet_name'=>$beforeData['cabinet_name'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_admin';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //======================================

            $this->msg('操作成功','/cabinetList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/cabinetList','error');
        }
    }

    /**
     * 机柜类型
     */
    private function cabinetType(){
        return array("1"=>"12轨机柜"
                    ,"2"=>"9轨机柜",
        );
    }
    
    //查看机柜下电池 (换至battery文件下index方法)
    public function cabinetBatteryList(){
        $this->checkAuth();
        $parames=$this->parames;
        F()->Resource_module->setTitle('机柜下电池列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/cabinetBatteryList?id=".$parames['id'];
        $data['cabinet_id']=$parames['id'];
        $nums=M_Mysqli_Class('md_lixiang','BatteryModel')->getBatteryByAttr($data);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $arr=M_Mysqli_Class('md_lixiang','BatteryModel')->getAllBatteryPages($showpage['limit'],$data);
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
        //print_r($arr);exit;
        $this->smarty->assign('arr',$arr);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('battery/list.phtml');
    }
    
    /**
     * 添加机柜之选择机柜类型
     */
    public function selectCabinetType(){
        if(isset($this->parames['cabinet_type'])){
           $this->input->set_cookie('cabinet_type',$this->parames['cabinet_type'],86400);
        }
        F()->Resource_module->setTitle('添加机柜');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('cabinet/insertNumber.phtml');
    }
    
    /**
     * 机柜入库
     */
    /* public function cabinetPutIn(){
        $action=$_SERVER['REQUEST_METHOD'];
        if($action=='POST'){
            
        }else{
            F()->Resource_module->setTitle('机柜入库');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $data['user_flag']=4;
            $agent=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttr($data);
            $jia[0]=0;
            $site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfo($jia);
            $this->smarty->assign('agent',$agent);
            $this->smarty->assign('site',$site);
            $this->smarty->view('cabinet/putIn.phtml');
        }
    } */

    /**
     * 机柜搬迁
     */
    public function cabinetRemoval(){
        $this->checkAuth();
        $parames=$this->parames;
        $action=$_SERVER['REQUEST_METHOD'];
        if($action=='POST'){
            if($verifyCibinetName=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['cabinet_name'=>trim($parames['cabinet_name'])])){
                if($verifyCibinetName['id']!=$parames['id']){
                    $this->msg('机柜名称已存在', '/cabinetRemoval?id='.$parames['id'] , 'error');die;
                }
            }
                $lnglat=explode(',', F()->Gaode_module()->addTransformCoordinate($parames['location'])['geocodes'][0]['location']);
                $parames['longitude']=$lnglat[0];
                $parames['latitude']=$lnglat[1];
                $beforeData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$parames['id']]);

                if(M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinetById($parames)){

                    //=============================操作内容记录
                    $operationType=[1=>'共享区',2=>'大B端',3=>'北海'];
                    $afterData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['id'=>$parames['id']]);
                    $cabinetRes=$this->arrayNewWornData($afterData,$beforeData);
                    if($cabinetRes){
                        for($i=0 ; $i<count($cabinetRes) ; $i++){
                            if(isset($cabinetRes[$i]['clm_name']) && $cabinetRes[$i]['clm_name']=='site_id'){
                                $cabinetRes[$i]['old_string']=isset($cabinetRes[$i]['old_string'])?M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$cabinetRes[$i]['old_string']])['site_name']:'';
                                $cabinetRes[$i]['new_string']=isset($cabinetRes[$i]['new_string'])?M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$cabinetRes[$i]['new_string']])['site_name']:'';
                            }
                            if(isset($cabinetRes[$i]['clm_name']) && $cabinetRes[$i]['clm_name']=='company_id'){
                                $cabinetRes[$i]['old_string']=!empty($cabinetRes[$i]['old_string'])?M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($cabinetRes[$i]['old_string'])[0]['name']:'';
                                $cabinetRes[$i]['new_string']=!empty($cabinetRes[$i]['new_string'])?M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($cabinetRes[$i]['new_string'])[0]['name']:'';
                            }
                            if(isset($cabinetRes[$i]['clm_name']) && $cabinetRes[$i]['clm_name']=='operation_type'){
                                $cabinetRes[$i]['old_string']=$operationType[$cabinetRes[$i]['new_string']];
                                $cabinetRes[$i]['new_string']=$operationType[$cabinetRes[$i]['new_string']];
                            }
                        }
                        $insertData[0]=$cabinetRes;
                    }else{
                        $insertData='';
                    }

                    $tableName[0]['table_name']='md_cabinet--机柜编号:'.$afterData['cabinet_number'];
                    $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
                    //=============================

                    $this->msg('更改成功','/cabinetList','ok');
                }else{
                    $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                    $this->msg('更改失败','/cabinetRemoval?id='.$parames['id'],'error');
                }


        }elseif(IS_GET){
            F()->Resource_module->setTitle('机柜搬迁');
            $info=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr($parames);
            $company_id=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
            $siteName=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$info['site_id']]);
            $this->smarty->assign('company_id',$company_id);
            $this->smarty->assign('info',$info);
            $this->smarty->assign('siteName',$siteName);
            $this->smarty->view('cabinet/cabinetRemoval.phtml');
        }else{
            $parames['site_status']=1;
            $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteData('',$parames);
                $this->setOutPut($siteData);die;
        }
    }








    
}
