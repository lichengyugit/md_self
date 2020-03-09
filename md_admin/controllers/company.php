<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class company extends MY_Controller
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
     * 集团列表
     */
    public function companyList()
    {   $this->checkAuth();
        //$this->msg('添加成功','companyList','ok');exit;
        F()->Resource_module->setTitle('集团列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url = "/companyList";
        $nums=M_Mysqli_Class('md_lixiang','CompanyModel')->getNumByAttr($this->parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
        $companyList=M_Mysqli_Class('md_lixiang','CompanyModel')->getAllCompanyByAttr($showpage['limit'],$this->parames);
        $this->smarty->assign('companyList',$companyList);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('company/list.phtml');
    }
    
    /**
     * 新增与修改集团页面
     */
    public function actionCompany(){
        $this->checkAuth();
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(array_key_exists('id', $this->parames)){
            F()->Resource_module->setTitle('修改集团');
            $companyInfo=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr($this->parames);
            $this->smarty->assign('companyInfo',$companyInfo);
            $this->smarty->view('company/update.phtml');
        }else{
            F()->Resource_module->setTitle('添加集团');
            $this->smarty->view('company/insert.phtml');
        }
    }
    
    /**
     * 新增集团
     */
    public function saveCompany(){
        $save=M_Mysqli_Class('md_lixiang','CompanyModel')->saveCompany($this->parames);
        if($save>0){
//            $data=[
//                'op01'=>date('Y-m-d H:i:s',time()),
//                'em_info'=>[[
//                    'op02'=>$this->parames['name'],
//                    'op03'=>$this->parames['contacts'],
//                    'op04'=>$this->parames['mobile'],
//                    'op06'=>$save
//                ]]
//
//            ];
////            $url="http://47.100.19.81/TESTDogWood_api/wechat/wechat/eab3ce16";
//            $url=ML_URL."eab3ce16";
//            $outPut=$this->apiAndData($url, $data);
//            get_log()->log_api('<接口测试> #### 接口名：saveCompany 作用：调用魔力同步企业通知接口参数：'.json_encode($data));
//            get_log()->log_api('<接口测试> #### 接口名：saveCompany 作用：调用魔力同步企业通知接口后获取返回值：'.json_encode($outPut));
            $tableName[0]['table_name']='md_company : 集团表';
            $insertData[0]=$this->parames;
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1,'type'=>'add'],$insertData,$tableName);
            $this->msg('添加成功','/companyList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
            $this->msg('添加失败','/companyList','error');
        }
    }
    
    /**
     * 修改集团
     */
    public function updateCompany(){
        $beforeData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($this->parames['id']);
        $update=M_Mysqli_Class('md_lixiang','CompanyModel')->updateCompanyById($this->parames);
        if($update){
//            $data=[
//                'op01'=>date('Y-m-d H:i:s',time()),
//                'em_info'=>[[
//                    'op02'=>$this->parames['name'],
//                    'op03'=>$this->parames['contacts'],
//                    'op04'=>$this->parames['mobile'],
//                    'op06'=>$this->parames['id']
//                ]]
//
//            ];
////            $url="http://47.100.19.81/TESTDogWood_api/wechat/wechat/eab3ce16";
//            $url=ML_URL."eab3ce16";
//            $outPut=$this->apiAndData($url, $data);
//            get_log()->log_api('<接口测试> #### 接口名：updateCompany 作用：调用魔力修改企业通知接口参数：'.json_encode($data));
//            get_log()->log_api('<接口测试> #### 接口名：updateCompany 作用：调用魔力修改企业通知接口后获取返回值：'.json_encode($outPut));
            //==================================操作内容记录
            $afterData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($this->parames['id']);
            $companyRes=$this->arrayNewWornData($afterData[0],$beforeData[0]);
            if($companyRes){
                $insertData[0]=$companyRes;
            }else{
                $insertData='';
            }
            $tableName[0]['table_name']='md_company : 集团表';

            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1,'type'=>'edit'],$insertData,$tableName);
            //==================================

            $this->msg('修改成功','/actionCompany?id='.$this->parames['id'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/actionCompany?id='.$this->parames['id'],'error');
        }
    }
    
    /**
     * 修改集团状态
     */
    public function actionCompanyStatus(){
        $this->checkAuth();

        $beforeCompayData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyWhereIn($this->parames['id']);
        $updateCompany=M_Mysqli_Class('md_lixiang','CompanyModel')->updateCompanyById($this->parames);
        if($updateCompany){
            $configUpdateData=array('status'=>$this->parames['status']);
            $configWhere=array('company_id'=>$this->parames['id']);
            $updateCompanyConfig=M_Mysqli_Class('md_lixiang','CompanyConfigModel')->updateCompanyConfigByAttr($configUpdateData,$configWhere);

            //====================操作内容记录
            $status=[0=>'启用',1=>'禁用',2=>'删除'];
            $tableName[0]['table_name']='md_company : 集团表';
            $insertData[0]=['name'=>$beforeCompayData[0]['name'],'contacts'=>$beforeCompayData[0]['contacts'],'mobile'=>$beforeCompayData[0]['mobile'],'status'=>$status[$this->parames['status']].','.$status[$beforeCompayData[0]['status']]];
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1,'type'=>'del'],$insertData,$tableName);
            //====================
            $this->msg('操作成功','/companyList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('操作失败','/companyList','error');
        }
    }
    
    /**
     * 查看所属集团用户
     */
    public function companyUser()
    {
        $this->checkAuth();
        $parames=$this->parames;
        $compay_name=isset($parames['company_name'])?$parames['company_name'].'-':'';
        F()->Resource_module->setTitle($compay_name.'集团所属用户列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames['user_flag']=1;
        $parames['where']=" AND attr_id=".$parames['id'];
        $companyUserNum=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser('',$parames);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "companyUser?".$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($companyUserNum));
        $limit="limit ".$showpage['limit'];
        $companyUserData=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser($limit,$parames);
//        var_dump($companyUserData);
        $parames['input_data']=isset($parames['input_data'])?$parames['input_data']:'';
        $parames['id_card']=isset($parames['id_card'])?$parames['id_card']:'';
        $parames['create_time']=isset($parames['create_time'])?$parames['create_time']:'';
        $parames['is_deposit']=isset($parames['is_deposit'])?$parames['is_deposit']:'';
        $this->smarty->assign('companyUserData',$companyUserData);
        $this->smarty->assign('parames',$this->parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('company/companyUserList.phtml');
    }


    public function actionIndex($arr){
        for($i=0 ; $i<count($arr) ; $i++){
          $arr[$i]['nick_name']=urldecode($arr[$i]['nick_name']);
        }
        return $arr;
    }

    /*
     * 集团所属站点列表
     * */
    public function affCompanySite()
    {
        $this->checkAuth();

        $parames=$this->parames;
        F()->Resource_module->setTitle($this->parames['company_name'].'集团所属站点');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $uri=$this->makeSearchUrl($this->parames);
        $url = "affCompanySite?".$uri;
        $companySiteNum=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteData('',$parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($companySiteNum));
        $limit=" LIMIT ".$showpage['limit'];
        $companySiteDatas=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteData($limit,$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign("parames", $parames);
        $this->smarty->assign("companySiteDatas", $companySiteDatas);
        $this->smarty->view('company/companysitelist.phtml');

    }

    /*
      * 站点所属用户列表
      * 
      */
    public function siteAffiliatedUser()
    {
        $parames=$this->parames;
        $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$parames['site_id']]);
        F()->Resource_module->setTitle($siteData['site_name'].'-站点所属用户');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
//        $cabinetData=M_Mysqli_Class('md_lixiang','CabinetModel')->getCabinetInfoByAttr(['site_id'=>$parames['site_id']]);
//        if(!empty($cabinetData)){
//            $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr(['id'=>$cabinetData['company_id']]);
            $uri=$this->makeSearchUrl($this->parames);
            $url = "siteAffiliatedUser?".$uri;
            $parames['where']=" AND site_id=".$parames['site_id'];
            $siteAffiliatedUserNum=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser('',$parames);
//            var_dump($siteAffiliatedUserNum[0]['attr_id']);die;
            $showpage= $this->page($url,$this->commonDefine['pagesize'],count($siteAffiliatedUserNum));
            $limit=" LIMIT ".$showpage['limit'];
            $siteAffiliatedUserData=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser($limit,$parames);
            if(!empty($siteAffiliatedUserData)){
                $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyInfoByAttr(['id'=>$siteAffiliatedUserData[0]['attr_id']]);
                $parames['company_name']=$companyData['name'];
                $parames['company_id']=$companyData['id'];
                $parames['input_data']=isset($parames['input_data'])?$parames['input_data']:'';
                $parames['create_time']=isset($parames['create_time'])?$parames['create_time']:'';
                $parames['id_card']=isset($parames['id_card'])?$parames['id_card']:'';
                $parames['is_deposit']=isset($parames['is_deposit'])?$parames['is_deposit']:'';
                $this->smarty->assign("pages", $showpage['show']);
                $this->smarty->assign("parames", $parames);
                $this->smarty->assign("siteAffiliatedUserData", $siteAffiliatedUserData);
                $this->smarty->view('company/siteaffiliatedaserlist.phtml');
            }else{
                $this->msg('无用户,请点击返回','#','erro');
            }

//        }else{
//            $this->msg('该站点无机柜或无用户,请点击返回','#','erro');
//        }

    }


    /*
     * 搜索集团
     * */
    public function compnaySearch()
    {
        $companyData=M_Mysqli_Class('md_lixiang','CompanyModel')->getCompanyAllDatas($this->parames);
        if($companyData){
            $this->setOutPut($companyData);
        }else{
            $companyData=[];
            $this->setOutPut($companyData);
        }
    }


}
