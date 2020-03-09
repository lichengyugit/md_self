<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class site extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine=$this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "site");
    }

    /**
     * 站点列表
     */
    public function siteList()
    {
        $this->checkAuth();
        //$this->msg('添加成功','companyList','ok');exit;
        F()->Resource_module->setTitle('站点列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(isset($this->parames['agent_id'])){
            $url = "/siteList?agent_id=".$this->parames['agent_id'];
        }else{
            $url="/siteList";
        }
        $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
        $this->parames['state']=4;   //已完工
        $nums=M_Mysqli_Class('md_lixiang','SiteModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $siteList=M_Mysqli_Class('md_lixiang','SiteModel')->getAllSiteByAttr($showpage['limit'],$this->parames);
        for($i=0;$i<count($siteList);$i++){
            if($siteList[$i]['company_id']!=0){
                $siteList[$i]['company_name']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr(['id'=>$siteList[$i]['company_id']])['name'];
            }

        }
        $this->smarty->assign('siteList',$siteList);
        $this->smarty->assign('companyData',$companyData);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('site/list.phtml');
    }


   /**
    * [siteSearch 站点页面搜索]
    * @return [type] [description]
    */
   public function siteSearch(){
      F()->Resource_module->setTitle('站点列表');
      F()->Resource_module->setJsAndCss(array(
          'home_page'
      ), array(
          'main'
      ));
      $parames=$this->parames;
      $selected=$parames;
      if($parames['site_status']=='x'){
        unset($parames['site_status']);
      }
       $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
/*    print_r($parames);die;*/
      $parames['state']=4;
      $num=M_Mysqli_Class('md_lixiang','SiteModel')->getSearchCountSiteByAttr($parames);
      $searchArray=[];
      $uri=$this->makeSearchUrl($this->parames);
      $url='siteSearch?'.$uri;
      $showpage= $this->page($url,$this->commonDefine['pagesize'],$num);
      $select=' LIMIT '.$showpage['limit'];
      $arr=M_Mysqli_Class('md_lixiang','SiteModel')->tableQuery($parames,$select);
       for($i=0;$i<count($arr);$i++){
           if($arr[$i]['company_id']!=0){
               $arr[$i]['company_name']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr(['id'=>$arr[$i]['company_id']])['name'];
           }

       }
      $this->smarty->assign('selected',$selected);
      $this->smarty->assign('companyData',$companyData);
      $this->smarty->assign('siteList',$arr);
      $this->smarty->assign('search',$this->parames['select']);
      $this->smarty->assign("pages", $showpage['show']);
      $this->smarty->view('site/list.phtml');
   }



    /**
     * 新增与修改站点页面
     */
    public function actionSite(){
        $this->checkAuth();
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(array_key_exists('id', $this->parames)){
            //print_r(strtotime($this->parames['business_start_time']));
            //$business_start_time=strtotime($this->parames['business_start_time']);
            //$business_end_time=strtotime($this->parames['business_end_time']);
            F()->Resource_module->setTitle('修改站点');
            $siteInfo=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoAndAgentByAttr($this->parames['id']);
            $data['user_flag']=4;
            $agent=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminDataAll($data);
            $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
            $this->smarty->assign('siteInfo',$siteInfo);
            $this->smarty->assign('agent',$agent);
            $this->smarty->assign('companyData',$companyData);
            $this->smarty->assign('time',$this->setTime());
            $this->smarty->view('site/update.phtml');
        }else{
            F()->Resource_module->setTitle('添加站点');
            $data['user_flag']=4;
            $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompany();
            $agent=M_Mysqli_Class('md_lixiang','UserModel')->getConditionUser($data);
            $this->smarty->assign('agent',$agent);
            $this->smarty->assign('companyData',$companyData);
            $this->smarty->assign('time',$this->setTime());
            $this->smarty->view('site/insert.phtml');
        }
    }


    
   
    /**
     * 新增站点
     */
    public function insertSite(){
    $parames=$this->parames;
        if(trim($parames['siteName'])==NULL){
            $this->msg('站点名不能为空','/actionSite','error');
            exit;
        }
        $res['site_name']=$parames['siteName'];
        if($site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr($res)){
            $this->msg('已存在此站点名', '/actionSite', 'error'); 
            exit;
        }
/*        if(trim($parames['agentName'])!=NULL){
            $agent['user_name']=$parames['agentName'];
            if($agentArr=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr($agent)){
                $data['agent_id']=$agentArr['id'];                           
            }else{
                $this->msg('代理商名不存在','/actionSite','error');
                exit;
            }
        }*/
        $data['site_name']=$parames['siteName'];
        $data['site_status']=$parames['siteStatus'];
        $data['business_start_time']=$parames['statrTime'];
        $data['business_end_time']=$parames['endTime'];
        $data['location']=$parames['siteLocation'];
        $data['longitude']=F()->Gaode_module()->addTransformCoordinate($parames['siteLocation'])['geocodes'][0]['location'];
        $data['operation_type']=$parames['operation_type'];
        $data['state']=$parames['state'];
        $data['company_id']=$parames['company_id'];
        if(M_Mysqli_Class('md_lixiang','SiteModel')->saveSite($data)){

            //=============================操作内容记录
            $tableName[0]['table_name']='md_site : 站点表';
            $data['company_id']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($parames['company_id'])[0]['name'];
            $siteStatus=[0=>'未开启',1=>'开启',2=>'关闭'];
            $operationType=[1=>'共享区',2=>'大B端',3=>'北海'];
            $state=[0=>'未勘测',1=>'需勘测',2=>'已勘测',3=>'已施工',4=>'已完工'];
            $data['site_status']=$siteStatus[$data['site_status']];
            $data['operation_type']=$operationType[$data['operation_type']];
            $data['state']=$state[$data['state']];
            $insertData[0]=$data;
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            //=============================
            $this->msg('添加成功','/siteList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->msg('添加失败','/actionSite','error');
        }
    }
    
    /**
     * 修改站点
     */
    public function updateSite(){
        $parames=$this->parames;
        if(trim($parames['siteName'])==NULL){
            $this->msg('站点名不能为空','/actionSite?id='.$this->parames['id'],'error');
            exit;
        }
        $res['site_name']=$parames['siteName'];
        if($site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr($res)){
            if($site['id']!=$parames['id']){
                $this->msg('已存在此站点名', '/actionSite?id='.$this->parames['id'], 'error');
                exit;
            }            
        }
        if(trim($parames['agentName'])!=NULL){
            $agent['user_name']=$parames['agentName'];
            if($agentArr=M_Mysqli_Class('md_lixiang','AdminModel')->getAdminByAttrOne($agent)){
                $data['agent_id']=$agentArr['id'];
            }else{
                $this->msg('代理商名不存在','/actionSite?id='.$this->parames['id'],'error');
                exit;
            } 
        }
        $data['id']=$parames['id'];      
        $data['site_name']=$parames['siteName'];
        $data['site_status']=$parames['siteStatus'];
        $data['business_start_time']=$parames['statrTime'];
        $data['business_end_time']=$parames['endTime'];
        $data['location']=$parames['siteLocation'];
        $data['company_id']=$parames['company_id'];
        $data['longitude']=F()->Gaode_module()->addTransformCoordinate($parames['siteLocation'])['geocodes'][0]['location'];
        $data['operation_type']=$parames['operation_type'];
        $data['state']=$parames['state'];
        $beforeData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$parames['id']]);
        if(M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById($data)){

            //======================================操作内容记录
            $siteStatus=[0=>'未开启',1=>'开启',2=>'关闭'];
            $operationType=[1=>'共享区',2=>'大B端',3=>'北海'];
            $state=[0=>'未勘测',1=>'需勘测',2=>'已勘测',3=>'已施工',4=>'已完工'];
            $afterData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$parames['id']]);
            $siteRes=$this->arrayNewWornData($afterData,$beforeData);
            if($siteRes){
                for($i=0; $i<count($siteRes); $i++){
                    if($siteRes[$i]['clm_name']=='site_status'){
                        $siteRes[$i]['old_string']=$siteStatus[$siteRes[$i]['old_string']];
                        $siteRes[$i]['new_string']=$siteStatus[$siteRes[$i]['new_string']];
                    }
                    if($siteRes[$i]['clm_name']=='operation_type'){
                        $siteRes[$i]['old_string']=$operationType[$siteRes[$i]['old_string']];
                        $siteRes[$i]['new_string']=$operationType[$siteRes[$i]['new_string']];
                    }
                    if($siteRes[$i]['clm_name']=='state'){
                        $siteRes[$i]['old_string']=$state[$siteRes[$i]['old_string']];
                        $siteRes[$i]['new_string']=$state[$siteRes[$i]['new_string']];
                    }
                    if($siteRes[$i]['clm_name']=='company_id'){
                        $siteRes[$i]['old_string']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($siteRes[$i]['old_string'])[0]['name'];
                        $siteRes[$i]['new_string']=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($siteRes[$i]['new_string'])[0]['name'];
                    }

                }
                $insertData[0]=$siteRes;
            }else{
                $insertData='';
            }
            $tableName[0]['table_name']='md_site : 站点表';
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
            //======================================

            $this->msg('修改成功','/actionSite?id='.$this->parames['id'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/actionSite?id='.$this->parames['id'],'error');
        }       
    }
    
    /**
     * 修改站点状态
     */
    public function actionSiteStatus(){
        $this->checkAuth();
        $beforeData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$this->parames['id']]);
        $updateCompany=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById($this->parames);
        if($updateCompany){

            //=======================================操作内容记录
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $insertData[0]=['site_name'=>$beforeData['site_name'], 'location'=>$beforeData['location'],'status'=>$status[$this->parames['status']].','.$status[$beforeData['status']]];
            $tableName[0]['table_name']='md_site : 站点表';
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //=======================================
            $this->msg('操作成功','/siteList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/siteList','error');
        }
    }
    
    /**
     * 站点下机柜列表
     */
    public function cabinetList()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('机柜列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/siteCabinetList?site_id=".$this->parames['site_id'];
        $nums=M_Mysqli_Class('md_lixiang','CabinetModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $cabinetCompanyData=M_Mysqli_Class('md_lixiang','CabinetModel')->getAllcabinetByAttr($showpage['limit'],$this->parames);
        $this->smarty->assign('cabinetCompanyData',$cabinetCompanyData);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign("parames", $this->parames);
        $this->smarty->assign('cabinetType',$this->cabinetType());
        $this->smarty->view('cabinet/list.phtml');
    }
    
    /**
     * 机柜类型
     */
    private function cabinetType(){
        return array("1"=>"12轨机柜"
            ,"2"=>"9轨机柜",
        );
    }
    
    //时间
    public function setTime(){
       return $time=[
            '-28800'=>'00:00',
            '-25200'=>'01:00',
            '-21600'=>'02:00',
            '-18000'=>'03:00',
            '-14400'=>'04:00',
            '-10800'=>'05:00',
            '-7200'=>'06:00',
            '-3600'=>'07:00',
            '0'=>'08:00',
            '3600'=>'09:00',
            '7200'=>'10:00',
            '10800'=>'11:00',
            '14400'=>'12:00',
            '18000'=>'13:00',
            '21600'=>'14:00',
            '25200'=>'15:00',
            '28800'=>'16:00',
            '32400'=>'17:00',
            '36000'=>'18:00',
            '39600'=>'19:00',
            '43200'=>'20:00',
            '46800'=>'21:00',
            '50400'=>'22:00',
            '54000'=>'23:00',
            '57600'=>'24:00'
        ];
    }
}
