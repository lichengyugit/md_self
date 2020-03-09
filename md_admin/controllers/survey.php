<?php
if (!defined('ROOTPATH')) {
	$url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
	header('Location: ' . $url, TRUE, 302);
	exit();
}

class survey extends MY_Controller
{
	
	public function __construct()
	{
		parent::__construct();
		$this->parames = $this->getParames();//调用http流方法
		unset($this->parames['currentPage']);
		$this->commonDefine=$this->commonDefine();
		$this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
		$this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
		$this->smarty->assign("function", "survey");
	}

	//B端站点任务发放页面
	public function blocSiteTask(){
		$this->checkAuth();
		$parames=$this->parames;
		if(array_key_exists('site_name',$parames)){
			//生成一个B端站点的信息
			//md_site 表里添加一个大B端站点信息
			//md_survey_project 表里添加一个中间数据 绑定站点id  队伍id  生成一个耗材出库的状态 为大B端数据
			//md_storage_order 表生成一个订单 标注需要出库电池 机柜数量
			$siteData=[
				'site_name'=>$parames['site_name'],//站点名称
				'location'=>$parames['location'],//站点地址
				'site_principal'=>$parames['site_principal'],//站点负责人名称
				'mobile'=>$parames['mobile'],//站点负责人电话
				'open_time'=>strtotime($parames['open_time']),//需要转换成时间戳
				'longitude'=>$parames['location'],//根据地质转换成经纬度
				'set_site_number'=>$parames['cabinet_num'],//设站数量
				'set_battery_number'=>empty($parames['set_battery_number'])?0:$parames['set_battery_number'],//设站电池数量
				'operation_type'=>2,//1共享区 2大B端 3北海
				'state'=>2,//0未勘测1需勘测2已勘测3已施工4已完工5站点发放未通过
				'company_id'=>$parames['company_id']//集团id
			];
			$site=M_Mysqli_Class('md_lixiang','SiteModel')->saveSite($siteData);
			$proData=[
				'state'=>2,
				'survey_state'=>1,
				'audit'=>9,
				'team_id'=>$parames['team_id'],
				'site_id'=>$site//绑定站点id
			];
			$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->insertState($proData);
			$orderData=[
				'order_sn'=>$this->createStorageAllotNum(),//生成一个订单号
				'order_platform'=>2,//1.后台电池调拨 2.后台勘测出库 3.电池调拨手机端 4.勘测调拨手机端
				'creator_id'=>$_SESSION['user_id'],//订单创建人id
				'type'=>1,//订单发货类型：1.勘测系统出库 2.电池调拨系统
				'attr_type'=>3,//一：勘测系统出库 3-大B端出库 4-共享区出库  二：电池调拨系统 1-对人员调拨  2-对站点调拨
				'order_status'=>0,//0进行中 1已完成
				'site_id'=>$site,//站点id
				'site_name'=>$parames['site_name'],//站点名称 冗余字段
				'creator_name'=>$_SESSION['userName']
			];
			$order=M_Mysqli_Class('md_lixiang','StorageOrderModel')->addOrder($orderData);
			if($site && $project && $order){
				$this->msg('操作成功','/blocSiteTask','ok');
			}else{
				$this->msg('操作失败','/blocSiteTask','error');
			}
			/*如果出库*/
			//md_storage_suvey_record 表里绑定订单表这些电池编号 机柜编号数据  修改订单状态
			//修改耗材出库的小状态 变成待施工状态
			//电池调拨系统 生成一个任务
		}else{
			F()->Resource_module->setTitle('大B端站点任务发放');
			F()->Resource_module->setJsAndCss(array(
				'home_page'
			), array(
				'main'
			));    
			$arr=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam([]);
			$this->smarty->assign('arr',$arr);
			$this->smarty->view('survey/bloc_site_task.phtml');
		}
	}



	//查询站点列表
	public function surveyList()
   	{
   		$this->checkAuth();
		F()->Resource_module->setTitle('勘测站点列表');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));    
		$this->smarty->view('survey/list.phtml');
   	}



   //站点勘测详情
	public function surveyDetails(){
		F()->Resource_module->setTitle('勘测站点详情');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));    
		//接收站点表id
		$parames=$this->parames;
		//根据站点表id查询工程表site_id对应id
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo($parames);
		if(empty($projectId)){
			$this->msg('该站点未发放','/surveyList','error');exit;
		}else{
			$rule=M_Mysqli_Class('md_lixiang','StorageRuleModel')->selectRule([]);
			if($projectId['state']==1){
				$this->msg('该站点没有勘测返回数据','/surveyList','error');exit;
			}else{
				//根据工程表id找到对应任务表project_id对应数据
				$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId['id'],'info_state'=>1]);
				//根据任务表id查找图片表图片
				$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id'],'platform'=>3]);
				$url='';
				foreach ($img as $k => $v) {
					$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
				}
				$this->smarty->assign('img',$img);
				$this->smarty->assign('rule',$rule);
				$this->smarty->assign('arr',$arr[0]);
				$this->smarty->view('survey/details.phtml');
				//根据站点数据详情  找到工程表对应id 以及任务表对应数据
			}
		}
   }

   //详情耗材Ajax处理
   public function surveyStorageDetails(){
   		$parames=$this->parames;
   		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$parames['id']]);
   		$account=M_Mysqli_Class('md_lixiang','StorageAccountModel')->updateAccount(['status'=>2],['project_id'=>$projectId['id'],'state'=>1,'status'=>0]);
   		$rule=M_Mysqli_Class('md_lixiang','StorageRuleModel')->selectRule([]);
   		$arr=$parames['data'];
   		foreach ($arr as $key => $value) {
   			foreach ($rule as $k => $v) {
   				if($value['code'] == $v['coding']){
   					$arr[$key]['company']=$v['company'];
   					$arr[$key]['type']=$v['type'];
   					$arr[$key]['specifications']=$v['specifications'];
   				}
   			}
   			$arr[$key]['state']=1;
   			$arr[$key]['project_id']=$projectId['id'];
   			unset($arr[$key]['id']);
   		}

   		$switchs=true;
   		foreach ($arr as $key => $value) {
   			$true=M_Mysqli_Class('md_lixiang','StorageAccountModel')->addMeterial($value);
   			if(!$true){
   				$switchs=false;
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
   				echo json_encode('耗材'.$value['name'].'添加失败');exit;
   			}
   		}
   		if($switchs){
            $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
	   		echo json_encode('添加成功');
   		}
   		
/*   		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$parames['id']]);
   		$data['id']=$parames['id'];
   		$num=0;
   		for($i=0 ; $i<count($parames['data']) ; $i++){
   			$parames['data'][$i]['project_id']=$projectId['id'];
   			$parames['data'][$i]['state']=2;
   		}
   		foreach ($parames['data'] as $key => $value) {
   			unset($value['id']);
   			unset($value['code']);
   			$request=M_Mysqli_Class('md_lixiang','StorageAccountModel')->addMeterial($value);
   			if(!empty($request)){
   				 $num+=1;
   			}
   		}
   		if(count($parames['data']) == $num){
   			echo json_encode('添加成功');
   		}else{
   			return false;
   		}*/
   }


   //站点施工详情
   public function surveyAfterDetails()
   {
   		F()->Resource_module->setTitle('施工站点详情');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		//接收站点表id
		$parames=$this->parames;
		//根据站点表id查询工程表site_id对应id
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo($parames);
/*		if(empty($projectId)){
			$this->msg('该站点未发放','/surveyList','error');exit;
		}else{
			if($projectId['state']==3 || $projectId['state']==4){*/
				//根据工程表id找到对应任务表project_id对应数据
				$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId['id'],'info_state'=>2]);
				//根据任务表id查找图片表图片
				$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id']]);
				$url='';
				foreach ($img as $k => $v) {
					$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
				}
				$this->smarty->assign('img',$img);
				$this->smarty->assign('arr',$arr[0]);
				$this->smarty->view('survey/agree_afterdetails.phtml');
/*			}else{
				$this->msg('该站点没有施工返回数据','/surveyList','error');exit;
			}*/
/*		}*/
   }

   	//站点详情
   	public function surveyTreaty(){
   		F()->Resource_module->setTitle('站点详情');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		$parames=$this->parames;
		$url='/surveyTreaty';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getNumByAttr($this->parames);
		$HeYuetable=$this->getMemcache('B213');
		$PingGutable=$this->getMemcache('B212');
		if($HeYuetable){
			$Hstr='B213-'.date('Ymd').$HeYuetable;
		}else{
			$Hstr='B213-'.date('Ymd').'001';
		}
		if($PingGutable){
			$Pstr='B212-'.date('Ymd').$PingGutable;
		}else{
			$Pstr='B212-'.date('Ymd').'001';
		}
		$showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
		$arr=M_Mysqli_Class('md_lixiang','SiteModel')->getAllSiteByAttr($showpage['limit'],$parames);
		$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>12]);
		$video=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>13]);
		$user=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$arr[0]['user_id']]);
		$arr[0]['user_name']=$user['user_name'];
		if($video){
			$arr[0]['video_src']=IMG_URL.'/'.$video[0]['filename'];
		}else{
			$arr[0]['video_src']='';
		}
		foreach ($img as $k => $v) {
			$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
		}
		$this->smarty->assign('HeYuetable',$Hstr);
		$this->smarty->assign('PingGutable',$Pstr);
		$this->smarty->assign('img',$img);
		$this->smarty->assign('arr',$arr[0]);
		$this->smarty->view('survey/treaty.phtml');
   	}

   	//耗材出库单
   	public function surveyBillDetails(){
			F()->Resource_module->setTitle('勘测详情');
			F()->Resource_module->setJsAndCss(array(
				'home_page'
			), array(
				'main'
			));
			$parames=$this->parames;
			$site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr($parames);
			//查询工程表对应id
			$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getAgreeDetails(['site_id'=>$parames['id']]);
			$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId[0]['id'],'info_state'=>1]);
			$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id']]);
			$src=IMG_URL;
			$video=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>13]);
			$video[0]['src']=$src.'/'.$video[0]['filename'];
			$aroundimg=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>12]);
			foreach ($aroundimg as $k => $v) {
				$aroundimg[$k]['src']=IMG_URL.'/'.$aroundimg[$k]['filename'];
			}
			foreach ($img as $k => $v) {
				$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
			}
			//11  14  15  16
			$beforeone=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>11]);
			$beforeone[0]['src']=$src.'/'.$beforeone[0]['filename'];
			$beforetwo=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>14]);
			$beforetwo[0]['src']=$src.'/'.$beforetwo[0]['filename'];
			$beforetre=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>15]);
			$beforetre[0]['src']=$src.'/'.$beforetre[0]['filename'];
			$beforefor=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>16]);
			$beforefor[0]['src']=$src.'/'.$beforefor[0]['filename'];
			$this->smarty->assign('beforeone',$beforeone[0]);
			$this->smarty->assign('beforetwo',$beforetwo[0]);
			$this->smarty->assign('beforetre',$beforetre[0]);
			$this->smarty->assign('beforefor',$beforefor[0]);
			$this->smarty->assign('around',$aroundimg);
			$this->smarty->assign('video',$video[0]);
			$this->smarty->assign('site',$site);
			$this->smarty->assign('img',$img);
			$this->smarty->assign('arr',$arr[0]);
			$this->smarty->view('survey/bill.phtml');
/*   	F()->Resource_module->setTitle('出库单');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		//接收站点表id
		$parames=$this->parames;
		//根据站点表id查询工程表site_id对应id
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo($parames);
		$account=M_Mysqli_Class('md_lixiang','StorageAccountModel')->selectAccount(['project_id'=>$projectId['id'],'state'=>1]);
		$site=M_Mysqli_Class('md_lixiang','SiteModel')->getAllSiteByAttr(15,$parames);
/*		if(empty($account)){
			$this->msg('该站点没有出库单数据','/surveyList','error');exit;
		}else{
			$this->smarty->assign('site',$site[0]);
			$this->smarty->assign('arr',$account);
			$this->smarty->view('survey/bill.phtml');
		/*}*/
   	}



   	//耗材出库单接口
   	public function surveyBillDetailsInterface(){
   		//接收站点表id
		$parames=$this->parames;
		//根据站点表id查询工程表site_id对应id
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo($parames);
		//流水表数据
		$account=M_Mysqli_Class('md_lixiang','StorageAccountModel')->selectAccount(['project_id'=>$projectId['id'],'state'=>1]);
		//规范表数据
		$mafanle=M_Mysqli_Class('md_lixiang','StorageRuleModel')->selectRule([]);
		for($i=0 ; $i<count($account) ; $i++){
			for($o=0 ; $o<count($mafanle) ; $o++){
				if($account[$i]['specifications'] == $mafanle[$o]['specifications'] && $account[$i]['name'] == $mafanle[$o]['name']){
					$account[$i]['specifications']=$mafanle[$o]['coding'];
				}
			}
		}
		$arr=[];
		foreach ($account as $key => $value) {
			$arr+=[
				$value['specifications']=>$value['out']
			];
		}
		$this->setOutPut($arr);
   	}

	//修改站点配置状态
 	public function actionSurveyStatus(){
       $updateCompany=M_Mysqli_Class('md_lixiang','SiteModel')->updateSurvey($this->parames);
       if($updateCompany){
           $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
           $this->msg('操作成功','/surveyList','ok');
       }else{
           $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
           $this->msg('操作失败','/surveyList','error');
       }
  	}







   //发放审核
   public function surveyAgreeGrant(){
   		F()->Resource_module->setTitle('勘测站点列表');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		$parames=$this->parames;
		$action=$_SERVER['REQUEST_METHOD'];
		$HeYuetable=$this->getMemcache('B213');
		$PingGutable=$this->getMemcache('B212');
/*		if($HeYuetable){
			$Hstr='B213-'.date('Ymd').$HeYuetable;
		}else{
			$Hstr='B213-'.date('Ymd').'001';
		}
		if($PingGutable){
			$Pstr='B212-'.date('Ymd').$PingGutable;
		}else{
			$Pstr='B212-'.date('Ymd').'001';
		}*/
		if($HeYuetable){
			$Hstr='B213-';
		}else{
			$Hstr='B213-';
		}
		if($PingGutable){
			$Pstr='B212-';
		}else{
			$Pstr='B212-';
		}

		if($action=='GET'){
			$url='/surveyAgreeGrant';
			$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getNumByAttr($this->parames);
			$showpage= $this->page($url,$this->commonDefine['pagesize'],$nums);
			$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>12]);
			$video=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>13]);
			$arr=M_Mysqli_Class('md_lixiang','SiteModel')->getAllSiteByAttr($showpage['limit'],$parames);
			$user=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$arr[0]['user_id']]);
			$arr[0]['user_name']=$user['user_name'];
			if($video){
				$arr[0]['video_src']=IMG_URL.'/'.$video[0]['filename'];
			}else{
				$arr[0]['video_src']='';
			}
			foreach ($img as $k => $v) {
				$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
			}
			$TeamOpt=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam([]);
			if(empty($arr)){
				$this->msg('该站点没有签呈返回数据','/surveyList','error');exit;
			}else{
				$this->smarty->assign('HeYuetable',$Hstr);
				$this->smarty->assign('PingGutable',$Pstr);
				$this->smarty->assign('img',$img);
				$this->smarty->assign('arr',$arr[0]);
				$this->smarty->assign('option',$TeamOpt);
				$this->smarty->assign("pages", $showpage['show']);
				$this->smarty->view('survey/agree_grant.phtml');
			}
		}else{

			if($site=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById($parames)){
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
				$this->msg('操作成功','/surveyList','ok');
			}else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
				$this->msg('操作失败','/surveyList','error');
			}
		}
   }



   //发放审核不通过
   public function surveyGrantPass(){
   		$parames=$this->parames;
   		$data=[
   			'state'=>5
   		];
        $project=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteByAttr($data,$parames);
        if($project){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
        	$this->msg('审核完成','/surveyList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
        	$this->msg('站点表更新失败','/surveyList','error');
        }
   }

   //点击发放
   public function surveyGrant()
   {
		$parames=$this->parames;
		$arr=json_decode($parames['jsonData'],true);
        $this->form_validation->set_data($arr);
        $this->form_validation->set_rules('set_site_number','设站数量必须是整数数字大于零并且不能为空','integer|required|greater_than[0]');
        $this->form_validation->run();
        if($this->form_validation->run()===FALSE){
            $this->msg($this->form_validation->validation_error(), '/surveyList', 'error');
        }
		$arr['state']=1;
		unset($arr['null']);
		//修改工程表状态
		$state=1;
		$data=array(
        'site_id'=>$parames['id'],
        'status'=>0,
        'state'=>1,
        'survey_state'=>0,
        'team_id'=>$parames['select'],
        'create_date'=>date("Y-m-d H:i:s",time()),
        'create_time'=>time()
      	);
		if($arr['location']==null || !isset($arr['location']) || $arr['longitude']==null || !isset($arr['longitude'])){
			$locan=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$parames['id']]);
			$arr['location']=$locan['location'];
	      	$arr['longitude']=$locan['longitude'];
		}
      	//id 				站点表id
      	//selectValue		工程队id
		//修改站点表状态
		$site=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteByAttr($arr,['id'=>$parames['id']]);
		//发布一条工程记录 到工程表
		$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->insertState($data);
		//查询站点表id 对应 工程表 id
		//$proteamId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$parames['id']]);
		//$team=M_Mysqli_Class('md_survey','SurveyTeamModel')->updateSurveyTeam(['id'=>$parames['select'],'team_project'=>$proteamId['id']]);
		if($site && $project){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
			echo json_encode('发放成功');
			//$this->msg('发放成功','/surveyList','ok');
		}else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
			return false;
			//$this->msg('发放失败','/surveyList','error');
		}
		//发放任务  需要指定工程队  绑定工程队ID到工程表  添加状态(state)为需勘测 小状态(survey_state)为未勘测 status为0 
   }






   //勘测完成 点击审核
   public function surveyAgreeDetails()
   {
		$action=$_SERVER['REQUEST_METHOD'];
		if($action=='GET')
		{
			F()->Resource_module->setTitle('已勘测审核');
			F()->Resource_module->setJsAndCss(array(
				'home_page'
			), array(
				'main'
			));
			$parames=$this->parames;
			$site=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr($parames);
			//查询工程表对应id
			$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getAgreeDetails(['site_id'=>$parames['id'],'survey_state'=>1]);

			$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId[0]['id'],'state'=>1]);
			if(empty($arr)){
				$this->msg('审核数据有误','/surveyList','error');exit;
			}else{
				$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id']]);
				$src=IMG_URL;
				$video=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>13]);
				$video[0]['src']=$src.'/'.$video[0]['filename'];
				$aroundimg=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$parames['id'],'type_id'=>12]);
				foreach ($aroundimg as $k => $v) {
					$aroundimg[$k]['src']=IMG_URL.'/'.$aroundimg[$k]['filename'];
				}
				foreach ($img as $k => $v) {
					$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
				}
				$this->smarty->assign('around',$aroundimg);
				$this->smarty->assign('video',$video[0]);
				$this->smarty->assign('site',$site);
				$this->smarty->assign('img',$img);
				$this->smarty->assign('arr',$arr[0]);
				$this->smarty->view('survey/agree_details.phtml');
				//大状态（state）改为已勘测
			}
		}else{
			$parames=$this->parames;
            $this->form_validation->set_data($parames);
            $this->form_validation->set_rules('amount','机柜数量必须是整数数字大于零并且不能为空','integer|required|greater_than[0]');
            $this->form_validation->run();
            if($this->form_validation->run()===FALSE){
                $this->msg($this->form_validation->validation_error(), '/surveyList', 'error');
            }
            $parames['create_date']=date("Y-m-d H:i:s",time());
            $parames['create_time']=time();
			$parames['state']=2;
			$state=2;
			//更新任务表状态和数据
			$info=M_Mysqli_Class('md_survey','SurveyInfoModel')->updataState($parames,['project_id'=>$parames['project_id'],'state'=>1,'info_state'=>1]);
			if($info){
				//更新工程表状态
				$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['state'=>$state,'survey_state'=>1,'audit'=>1],['id'=>$parames['project_id'],'survey_state'=>1]);
					if($project){
						//根据工程id查找站点id
						$projectsiteid=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSiteId(['id'=>$parames['project_id']]);
						//更新站点表状态
						$site=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteByAttr(['state'=>$state],['id'=>$projectsiteid['site_id']]);
						if($site){
							if($info && $project && $site){
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
								$this->msg('发放成功','/surveyList','ok');
							}else{
                                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
								$this->msg('发放失败','/surveyList','error');exit;
							}
						}else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
							$this->msg('站点表更新失败','/surveyList','error');exit;
						}
					}else{
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
						$this->msg('工程表更新失败','/surveyList','error');exit;
					}
			}else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
				 $this->msg('任务表更新失败','/surveyList','error');exit;
			}
		}
   }

   //勘测审核不通过
   public function surveyAgreeOut(){
   		$parames=$this->parames;
   		$data=[
   			'survey_state'=>0,
   			'audit'=>2
   		];
        $project=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState($data,['id'=>$parames['project_id']]);
        if($project){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
        	$this->msg('审核完成','/surveyList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
        	$this->msg('工程表更新失败','/surveyList','error');
        }
   }



   //施工完成 点击审核
   public function surveyAgreesDetails()
   {
		F()->Resource_module->setTitle('已施工审核');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		$action=$_SERVER['REQUEST_METHOD'];
		if($action == 'GET')
		{
			$parames=$this->parames;
			//查询工程表对应id
			$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getAgreeDetails(['site_id'=>$parames['id'],'survey_state'=>2]);
			$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId[0]['id'],'state'=>2,'info_state'=>2]);
			if(empty($arr)){
				$this->msg('审核数据有误','/surveyList','error');exit;
			}else{
				$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id']]);
				$url='';
				foreach ($img as $k => $v) {
					$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
				}
				$this->smarty->assign('img',$img);
				$this->smarty->assign('arr',$arr[0]);
				$this->smarty->view('survey/agree_cons_details.phtml');
				//大状态（state）改为已勘测
			}
		}else{
			$parames=$this->parames;
			$this->form_validation->set_data($parames);
            $this->form_validation->set_rules('amount','机柜数量必须是整数数字大于零并且不能为空','integer|required|greater_than[0]');
            $this->form_validation->run();
            if($this->form_validation->run()===FALSE){
                $this->msg($this->form_validation->validation_error(), '/surveyList', 'error');
            }
            $ProResult=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSiteId(['id'=>$parames['project_id']]);
            $siteResult=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$ProResult['site_id']]);
            // $upCabinet=[
            // 	'cabinet_name'=>$siteResult['site_name'],
            // 	'location'=>$siteResult['location'],
            // 	'longitude'=>explode(',',$siteResult['longitude'])[0],
            // 	'latitude'=>explode(',',$siteResult['longitude'])[1],
            // 	'status'=>0
            // ];
            //更新机柜信息
            //$cabinet=M_Mysqli_Class('md_lixiang','CabinetModel')->updateCabinetByAttr($upCabinet,['site_id'=>$siteResult['id']]);
            $parames['create_date']=date("Y-m-d H:i:s",time());
            $parames['create_time']=time();
			$parames['state']=3;
			$state=3;
			$info=M_Mysqli_Class('md_survey','SurveyInfoModel')->updataState($parames,['project_id'=>$parames['project_id'],'state'=>2,'info_state'=>2,]);
			if($info){
				$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['state'=>$state,'audit'=>6],['id'=>$parames['project_id'],'survey_state'=>2]);
				if($project){
					$projectsiteid=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSiteId(['id'=>$parames['project_id']]);
					$site=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteByAttr(['state'=>$state],['id'=>$projectsiteid['site_id']]);
					if($site){
						if($info && $project && $site){
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
						$this->msg('发放成功','/surveyList','ok');
						}else{
                            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
							$this->msg('发放失败','/surveyList','error');exit;
						}
					}else{
                        $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
						$this->msg('站点表更新失败','/surveyList','error');exit;
					}
				}else{
                    $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
					$this->msg('工程表更新失败','/surveyList','error');exit;
				}
			}else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
				$this->msg('任务表更新失败','/surveyList','error');exit;
			}
		}
	//如果通过 将工程表状态state改为已施工
   }


   /*
    *	大B端施工完成 点击审核
    */
   public function surveyBlocDetails(){
   	$parames=$this->parames;
   	$action=$_SERVER['REQUEST_METHOD'];
   	//进入审核页面 
   	//页面有 站点发放的信息（在站点表里  和耗材记录表里）  施工后的图片信息（图片表绑定的站点id）
   	//审核是否通过 走post流程
   	//详情需要让人员在手机端去分配
   	if($action == 'GET'){
   		//返回页面
   		F()->Resource_module->setTitle('已施工审核');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		//查询工程表对应id
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$parames['id'],'survey_state'=>2]);
		$site_name=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$parames['id']]);
		$site_name['open_time']=date('Y-m-d H:i:s',$site_name['open_time']);
		$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId['id'],'state'=>2,'info_state'=>2]);
		if(empty($arr)){
			$this->msg('审核数据有误','/surveyList','error');exit;
		}else{
			$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id'],'save_platform'=>0,'platform'=>3]);
			$url='';
			foreach ($img as $k => $v) {
				$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
			}
			$team=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam(['id'=>$projectId['team_id']],' LIMIT 1');
			$this->smarty->assign('img',$img);
			$this->smarty->assign('team',$team[0]);
			$this->smarty->assign('arr',$arr[0]);
			$this->smarty->assign('project',$projectId);
			$this->smarty->assign('site_name',$site_name);
			$this->smarty->view('survey/bloc_cons_details.phtml');
			//大状态（state）改为已勘测
		}
   	}else{
   		//修改工程状态
   		$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['state'=>3,'audit'=>6],['id'=>$parames['project_id'],'survey_state'=>2]);
   		//修改站点信息
   		$site=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteByAttr(['state'=>3],['id'=>$parames['site_id']]);
   		if( $project && $site ){
   			$this->msg('发放成功','/surveyList','ok');
   		}else{
   			$this->msg('任务表更新失败','/surveyList','error');exit;
   		}
	}
   }


   //大B端任务详情页面
   public function surveyTaskDetailsPage(){
   		$parames=$this->parames;
   		F()->Resource_module->setTitle('任务详情');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		$site_name=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$parames['id']]);
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$parames['id']]);
		$site_name['open_time']=date('Y-m-d H:i:s',$site_name['open_time']);
		$team=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam(['id'=>$projectId['team_id']],' LIMIT 1');
		$this->smarty->assign('team',$team[0]);
		$this->smarty->assign('project',$projectId);
		$this->smarty->assign('site_name',$site_name);
		$this->smarty->view('survey/bloc_task_page.phtml');
   }
   //大B端施工详情页面
   public function surveyConsDetailsPage(){
   		$parames=$this->parames;
   		F()->Resource_module->setTitle('施工详情');
		F()->Resource_module->setJsAndCss(array(
			'home_page'
		), array(
			'main'
		));
		//查询工程表对应id
		$projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo(['id'=>$parames['id']]);
		$site_name=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteAllotInfoByAttr(['id'=>$parames['id']]);
		$site_name['open_time']=date('Y-m-d H:i:s',$site_name['open_time']);
		$arr=M_Mysqli_Class('md_survey','SurveyInfoModel')->getSurveyDetails(['project_id'=>$projectId['id'],'state'=>2,'info_state'=>2]);
		$img=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['attribute_id'=>$arr[0]['id'],'save_platform'=>0,'platform'=>3]);
		$url='';
		foreach ($img as $k => $v) {
			$img[$k]['src']=IMG_URL.'/'.$img[$k]['filename'];
		}
		$team=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam(['id'=>$projectId['team_id']],' LIMIT 1');
		$this->smarty->assign('img',$img);
		$this->smarty->assign('team',$team[0]);
		$this->smarty->assign('arr',$arr[0]);
		$this->smarty->assign('project',$projectId);
		$this->smarty->assign('site_name',$site_name);
		$this->smarty->view('survey/bloc_cons_page.phtml');
   }


   //施工审核不通过
   public function surveyAgreePass(){
   		$parames=$this->parames;
   		$data=[
   			'survey_state'=>1,
   			'audit'=>5
   		];
        $project=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState($data,['id'=>$parames['project_id']]);
        if($project){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
        	$this->msg('审核完成','/surveyList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
        	$this->msg('工程表更新失败','/surveyList','error');exit;
        }
   }

   //添加工程队
   public function surveyAddTeam(){
   	$this->checkAuth();
		$action=$_SERVER['REQUEST_METHOD'];
		if( $action == 'POST' ){
			$parames=$this->parames;
			$data=M_Mysqli_Class('md_survey','SurveyTeamModel')->addSurveyTeam($parames);
			if(isset($data)){
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
				$this->msg('添加成功','/surveyAddTeam','ok');
			}else{
                $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
				$this->msg('添加失败','/surveyAddTeam','error');
			}
		}else{
			F()->Resource_module->setTitle('添加工程队');
			F()->Resource_module->setJsAndCss(array(
				'home_page'
			), array(
				'main'
			));
			$this->smarty->view('survey/team_add.phtml');
		}
   }

   //添加工作人员
   public function surveyAddUser(){
   	$this->checkAuth();
   		$action=$_SERVER['REQUEST_METHOD'];
   		if( $action == 'POST'){
   			$parames=$this->parames;
   			$user_flag=$parames['user_flag'];
   			$user=M_Mysqli_Class('md_lixiang','UserModel')->judgeUser($user_flag,$parames['mobile']);
   			if($user){
   				   	$parames['password']=md5($parames['password']);
		   			$parames['user_type']=2;
		   			$parames['create_date']=date("Y-m-d H:i:s",time());
		            $parames['create_time']=time();
		   			$data=M_Mysqli_Class('md_lixiang','UserModel')->addUser($parames);
		   			if(isset($data)){
                        $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>1]);
						$this->msg('添加成功','/surveyAddUser','ok');
					}else{
                        $this->writeBackstageLog(['operation_type'=>4,'operation_state'=>2]);
						$this->msg('添加失败','/surveyAddUser','error');
					}
		   		}else{
		   			$this->msg('账号已存在,请更换账号','/surveyAddUser','error');exit;
		   		}
		}else{
			F()->Resource_module->setTitle('添加工作人员');
			F()->Resource_module->setJsAndCss(array(
				'home_page'
			), array(
				'main'
			));
			$arr=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam([]);
			$this->smarty->assign('arr',$arr);
			$this->smarty->view('survey/user_add.phtml'); 
		}
   }

   /*//添加工作人员
   public function surveyAddUser(){
   		$action=$_SERVER['REQUEST_METHOD'];
   		if( $action == 'POST'){
   			$parames=$this->parames;
   			echo '<pre>';
   			print_r($parames);die;
   			$data=M_Mysqli_Class('md_survey','SurveyUserModel')->addSurveyUser($parames);
   			if(isset($data)){
				$this->msg('添加成功','/surveyAddUser','ok');
			}else{
				$this->msg('添加失败','/surveyAddUser','error');
			}
   		}else{
   			F()->Resource_module->setTitle('添加工作人员');
			F()->Resource_module->setJsAndCss(array(
				'home_page'
			), array(
				'main'
			));
			$arr=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam([]);
			$this->smarty->assign('arr',$arr);
			$this->smarty->view('survey/user_add.phtml');
   		}
   }
	*/


    /**
     * 返回勘测耗材流水申请数据接口
     */
    public function actionPost()
    {
      //业务员添加需勘测站点
      $parames=$this->parames;
      $projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo($parames);
      $arr=M_Mysqli_Class('md_lixiang','StorageAccountModel')->selectAccount(['project_id'=>$projectId['id'],'state'=>1]);
      $mafanle=M_Mysqli_Class('md_lixiang','StorageRuleModel')->selectRule([]);
      $count=count($arr);
      $rulecont=count($mafanle);
      for($i=0 ; $i<$count ; $i++){
      	for($o=0 ; $o<$rulecont ; $o++){
      		if( $arr[$i]['name'] == $mafanle[$o]['name'] && $arr[$i]['specifications'] == $mafanle[$o]['specifications'] ){
      			$arr[$i]['code'] = $mafanle[$o]['coding'];
      		}
      	}
      }
      if(!empty($arr)){
	      	$num = count($arr);
	        $card=[];
	        for ($i=0; $i < $num; $i++) {
	            $keyArray = array("id","name","specifications","company","code","out");
	            $card[$i] = $this->setArray($keyArray, $arr[$i]);
	        }
	    }else{
	    	$this->setOutPut($card);
	    	//return false;
	    }
      $this->setOutPut($card);
    }

    /**  
     * 返回施工耗材流水申请数据接口
     */
    public function actionPosts()
    {
      //业务员添加需勘测站点
      $parames=$this->parames;
      $projectId=M_Mysqli_Class('md_survey','SurveyProjectModel')->getSurveyInfo($parames);
      $arr=M_Mysqli_Class('md_lixiang','StorageAccountModel')->selectAccount(['project_id'=>$projectId['id'],'state'=>2]);
      $mafanle=M_Mysqli_Class('md_lixiang','StorageRuleModel')->selectRule([]);
      $count=count($arr);
      $rulecont=count($mafanle);
      for($i=0 ; $i<$count ; $i++){
      	for($o=0 ; $o<$rulecont ; $o++){
      		if( $arr[$i]['name'] == $mafanle[$o]['name'] && $arr[$i]['specifications'] == $mafanle[$o]['specifications'] ){
      			$arr[$i]['code'] = $mafanle[$o]['coding'];
      		}
      	}
      }
      if(!empty($arr)){
	      	$num = count($arr);
	        $card=[];
	        for ($i=0; $i < $num; $i++) {
	            $keyArray = array("id","name","specifications","company","code","out");
	            $card[$i] = $this->setArray($keyArray, $arr[$i]);
	        }
	    }else{
	    	$this->setOutPut($card);
	    	//return false;
	    }
      $this->setOutPut($card);
    }


    /*
     * 图片无刷新上传
     */
    public function filepicture(){
    	$parames=$this->parames;
    	$url='http://pfcfx0a3u.bkt.clouddn.com';
    	$file=F()->Qiniu_module->uploadPic($_FILES['file']['tmp_name'],$_FILES['file']['name']);
    	$imgname=$file['key'];
    	$siteid=$_POST['id'];
    	$typeid=$_POST['typeid'];
    	$arr=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$siteid]);
    	if(!empty($arr)){
    		$array=$this->in_arrays($typeid,$arr);
    		if($array){
    			$data=[
    				'filename'=>$imgname,
    			];
    			$arr=M_Mysqli_Class('md_lixiang','PictureModel')->updatePic($data,['site_id'=>$siteid,'type_id'=>$typeid]);
    			if($arr){
					$this->setOutPut('成功');
				}else{
					return false;
				}
    		}else{
    			$data=[
    				'url'=>$url,
    				'filename'=>$imgname,
    				'type_id'=>$typeid,
    				'description'=>'站点附带照片',
    				'save_platform'=>0,
    				'site_id'=>$siteid,
    				'platform'=>3
    			];
    			$arr=M_Mysqli_Class('md_lixiang','PictureModel')->saveImage($data);
    			if($arr){
					$this->setOutPut('成功');
				}else{
					return false;
				}
    		}
    	}else{
	    	$data=[
				'url'=>$url,
				'filename'=>$imgname,
				'type_id'=>$typeid,
				'description'=>'站点附带照片',
				'save_platform'=>0,
				'site_id'=>$siteid,
				'platform'=>3
			];
			$arr=M_Mysqli_Class('md_lixiang','PictureModel')->saveImage($data);
			if($arr){
				$this->setOutPut('成功');
			}else{
				return false;
			}
    	}
    }


    /*
     * 处理二维数组in_array 站点对应数组是否存在该类型id 在返回true 不在返回false
     * @ typeid     类型id
     * @ arr 		站点对应数组
     */
	function in_arrays($typeid,$arr){
	   $exist = false;
	   foreach($arr as $value){
	     if(in_array($typeid,$value)){
	        $exist = true;
	        break;    //循环判断字符串是否存在于一位数组，存在则跳出  返回结果
	     }
	   }
	   return $exist;
	}


    /*
     * 工程队列表
     * */
    public function teamList()
    {
//        $aa=[
//            'user_id'=>6,
//            'time'=>21312312
//        ];
////        $aa=[
////            [
////            'user_id'=>2,
////            'time'=>2
////            ],
////            [
////            'user_id'=>3,
////            'time'=>2
////            ]
////            ];
////        $userId=[];
//        $info=$this->getMemObj()->get('info');
////        $info=$this->getMemObj()->delete('info');
//        $userId=[];
//        if(is_array($info)){
//            for($i=0;$i<count($info);$i++){
//                if ( $info[$i]['time'] < 2131231 && $info[$i]['time'] > 2131231){
//                    $userId[]=isset($info[$i]['user_id'])?$info[$i]['user_id']:'';
//                }else{
//                    echo 111;
//                }
//            }
//            if(!in_array($aa['user_id'],$userId,true)){
//                $info[]=$aa;
//                $this->getMemObj()->set('info',$info,0,0);
//            }
//        }else{
//            echo 222;die;
//            $a=$this->getMemObj()->set('info',$aa,0,0);
//        }
//
//
//        echo '<pre />';
//        var_dump($this->getMemObj()->get('info'));die;
        $this->checkAuth();
        F()->Resource_module->setTitle('工程队列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $url     = "/teamList";
        $teamNums=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam([1=>1]);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($teamNums));
        $limit=' LIMIT '.$showpage['limit'];
        $teamDatas=M_Mysqli_Class('md_survey','SurveyTeamModel')->selectSurveyTeam([1=>1],$limit);
        $this->smarty->assign('teamDatas',$teamDatas);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('survey/team_list.phtml');
    }


    /*
     * 修改工程队状态
     * */
    public function editTeamstatus()
    {
        $parames=$this->parames;
        $this->checkAuth();
        $where=[
            'id'=>$parames['id'],
            'status'=>$parames['status']
        ];
        $teamStatus=M_Mysqli_Class('md_survey','SurveyTeamModel')->updateSurveyTeam($where);
        if($teamStatus){
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1]);
            $this->msg('修改成功','/teamList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('修改失败','/teamList','error');
        }
    }

    /*
     * 查看工程队所属成员
     * */
    public function teamAffiliatedList()
    {
        $parames=$this->parames;
        F()->Resource_module->setTitle('工程队所属成员');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(!empty($parames['input_data'])){
            $wheres['input_data']=$parames['input_data'];
        }
        if(!empty($parames['user_flag'])){
            $wheres['user_flag']=$parames['user_flag'];
        }
        $wheres['where']=" AND user_type=2 AND attr_id=".$parames['team_id'];
        $teamNums=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser('',$wheres);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "teamAffiliatedList?".$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($teamNums));
        $limit=' LIMIT '.$showpage['limit'];
        $teamUserDatas=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser($limit,$wheres);
        $parames['input_data']=isset($parames['input_data'])?$parames['input_data']:'';
        $parames['user_flag']=isset($parames['user_flag'])?$parames['user_flag']:'';
        $this->smarty->assign('teamUserDatas',$teamUserDatas);
        $this->smarty->assign('parames',$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('survey/team_affiliated_list.phtml');
    }

    /*
     * 修改工程队所属成员状态
     * */
    public function editTeamUserState()
    {
        $parames=$this->parames;
        $this->checkAuth();
        $teamUserStatus=M_Mysqli_Class('md_lixiang','UserModel')->updateUser(['id'=>$parames['id'],'status'=>$parames['status']]);
        if($teamUserStatus){
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1]);
            $this->msg('修改成功','/teamAffiliatedList?team_id='.$parames['team_id'],'ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('修改失败','/teamAffiliatedList?team_id='.$parames['team_id'],'error');
        }
    }

    /*
     * 工作人员列表
     * */
    public  function workerList()
    {
        $this->checkAuth();
        $parames=$this->parames;
        F()->Resource_module->setTitle('工作人员列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        if(!empty($parames['input_data'])){
            $wheres['input_data']=trim($parames['input_data']);
        }
        if(!empty($parames['user_flag'])){
            $wheres['user_flag']=$parames['user_flag'];
        }
        $wheres['where']=" AND user_type=2 ";
        $workerNums=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser('',$wheres);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "workerList?".$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($workerNums));
        $limit=' LIMIT '.$showpage['limit'];
        $workerDatas=M_Mysqli_Class('md_lixiang','UserModel')->getTeamUser($limit,$wheres);
        $userFlag=[3=>'业务员',6=>'勘测人员',7=>'施工人员',8=>'验收人员'];
        for($i=0;$i<count($workerDatas);$i++){
            $workerDatas[$i]['user_flag']=$userFlag[$workerDatas[$i]['user_flag']];
        }
        $this->smarty->assign('workerDatas',$workerDatas);
        $this->smarty->assign('parames',$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('survey/worker_list.phtml');
    }

    /*
     * 修改工作人员状态
     * */
    public function editWorkerState()
    {
        $this->checkAuth();
        $parames=$this->parames;
        $teamUserStatus=M_Mysqli_Class('md_lixiang','UserModel')->updateUser(['id'=>$parames['id'],'status'=>$parames['status']]);
        if($teamUserStatus){
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>1]);
            $this->msg('修改成功','/workerList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>$this->parames['status'],'operation_state'=>2]);
            $this->msg('修改失败','/workerList','error');
        }
    }

    /*
     * 修改工作人员信息
     * */
    public function editWorkerData()
    {
        $this->checkAuth();
        $parames=$this->parames;
        if(IS_GET){
            F()->Resource_module->setTitle('修改工作人员信息');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $workerUserAttr=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['id'=>$parames['id']]);
            $this->smarty->assign('workerUserAttr',$workerUserAttr);
            $this->smarty->view('survey/worker_update.phtml');
        }else{
            $workerDataStatus=M_Mysqli_Class('md_lixiang','UserModel')->updateUser($parames);
         if($workerDataStatus){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('修改成功','/workerList','ok');
         }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/editWorkerData?id='.$parames['id'],'error');
         }
        }
    }


	//共享区勘测站点列表
	public function shareList()
   	{
   		$parames=$this->parames;

   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(site_name,'"."'),IFNULL(location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(site_name,'"."'),IFNULL(location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$url='shareList';
		$data['operation_type']=1;
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getNumLikeByAttr($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arr=M_Mysqli_Class('md_lixiang','SiteModel')->getAllSiteLikeByAttr($showpage['limit'],$data,$like);
		$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->getProjectAll([]);
		$arr=$this->stateCode($arr,$project);
		$title=['站点名称','地址','站点状态','使用状态','联系方式','创建时间','详情'];
		$field=['id','site_name','location','state_code','status','mobile','create_date',''];
		$rule=[
			'state_code'=>[
				'0'=>'未发放',
				'5'=>'发放审核未通过',
				'10'=>'待勘测',
				'110'=>'已勘测待审核',
				'102'=>'勘测审核不通过',
				'219'=>'出库中',
				'211'=>'待施工',
				'223'=>'已施工待审核',
				'215'=>'施工审核不通过',
				'326'=>'待验收',
				'327'=>'验收审核通过',
				'218'=>'验收不通过',
				'427'=>'验收审核通过'
			],
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arr,$field,$rule);
		foreach ($data as $k => $v) {
			if( $v[3]['state_code'] == '' || $v[3]['state_code'] == '未发放' || $v[3]['state_code'] == '发放审核未通过' || $v[3]['state_code'] == '待勘测' ){
				$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="javascript:;" onclick="tipts('.reset($v)['id'].')">删除</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyTreaty?id='.reset($v)['id'].'">站点详情</a>';
			}elseif(  $v[3]['state_code'] == '已勘测待审核' || $v[3]['state_code'] == '勘测审核不通过' || $v[3]['state_code'] == '待施工' || $v[3]['state_code'] == '出库中'  ){
				$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="javascript:;" onclick="tipts('.reset($v)['id'].')">删除</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyTreaty?id='.reset($v)['id'].'">站点详情</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyBillDetails?id='.reset($v)['id'].'" class="change" id="'.reset($v)['id'].'">勘测详情</a>';
			}elseif( $v[3]['state_code'] == '已施工待审核' || $v[3]['state_code'] == '施工审核不通过' || $v[3]['state_code'] == '待验收'  || $v[3] == '验收审核通过'  || $v[3] == '验收不通过'  || $v[3] == '验收审核通过' ){
				$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="javascript:;" onclick="tipts('.reset($v)['id'].')">删除</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyTreaty?id='.reset($v)['id'].'">站点详情</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyBillDetails?id='.reset($v)['id'].'" class="change" id="'.reset($v)['id'].'">勘测详情</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyAfterDetails?id='.reset($v)['id'].'" class="change" id="'.reset($v)['id'].'">施工详情</a>  ';
			}else{
				$data[$k][key(end($v))][key(end($v))]='<span>出错</span>';
			}
			unset($data[$k][0]);
		}
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   	}

   	//大B端勘测站点列表
   	public function blocList(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(site_name,'"."'),IFNULL(location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(site_name,'"."'),IFNULL(location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$url='blocList';
		$data['operation_type']=2;
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getNumLikeByAttr($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arr=M_Mysqli_Class('md_lixiang','SiteModel')->getAllSiteLikeByAttr($showpage['limit'],$data,$like);
		$project=M_Mysqli_Class('md_survey','SurveyProjectModel')->getProjectAll([]);
		$arr=$this->stateCode($arr,$project);
		$title=['站点名称','地址','站点状态','使用状态','联系方式','创建时间','详情'];
		$field=['id','site_name','location','state_code','status','mobile','create_date',''];
		$rule=[
			'state_code'=>[
				'0'=>'未发放',
				'5'=>'发放审核未通过',
				'10'=>'待勘测',
				'110'=>'已勘测待审核',
				'102'=>'勘测审核不通过',
				'219'=>'出库中',
				'211'=>'待施工',
				'223'=>'已施工待审核',
				'215'=>'施工审核不通过',
				'326'=>'待验收',
				'327'=>'验收审核通过',
				'218'=>'验收不通过',
				'427'=>'验收审核通过'
			],
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arr,$field,$rule);
		foreach ($data as $k => $v) {
			if( $v[3]['state_code'] == '' || $v[3]['state_code'] == '未发放' || $v[3]['state_code'] == '发放审核未通过' || $v[3]['state_code'] == '待勘测' || $v[3]['state_code'] == '出库中' || $v[3]['state_code'] == '待施工' ){
				$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="javascript:;" onclick="tipts('.reset($v)['id'].')">删除</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyTaskDetailsPage?id='.reset($v)['id'].'" class="change" id="'.reset($v)['id'].'">任务详情</a>';
			}elseif( $v[3]['state_code'] == '已施工待审核' || $v[3]['state_code'] == '施工审核不通过' || $v[3]['state_code'] == '待验收'  || $v[3]['state_code'] == '验收审核通过'  || $v[3]['state_code'] == '验收不通过'  || $v[3]['state_code'] == '验收审核通过' ){
				$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="javascript:;" onclick="tipts('.reset($v)['id'].')">删除</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyTaskDetailsPage?id='.reset($v)['id'].'" class="change" id="'.reset($v)['id'].'">任务详情</a><a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyConsDetailsPage?id='.reset($v)['id'].'" class="change" id="'.reset($v)['id'].'">施工详情</a>';
			}else{
				$data[$k][key(end($v))][key(end($v))]='<span>出错</span>';
			}
			unset($data[$k][0]);
		}
		//问题是 需要数组类型 传入的类型是字符串reset() expects parameter 1 to be array, string given
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   	}


	//发放站点列表
	public function surveyGrantList(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>1,
			'st.state'=>0
		);
		$url='surveyGrantList';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式','操作'];
		$field=['id','site_name','location','status','mobile',''];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		foreach ($data as $k => $v) {
			$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyAgreeGrant?id='.reset($v)['id'].'">审核</a>';
			unset($data[$k][0]);
		}
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }


   //待勘测列表
   public function waitSurveyList(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
   		$data=array(
			'st.operation_type'=>1,
			'pr.state'=>1,
			'pr.survey_state'=>0
		);
		$url='waitSurveyList';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式','操作'];
		$field=['site_name','location','status','mobile',''];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }

	//勘测审核列表
	public function surveyAgree()
	{
		$parames=$this->parames;
		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>1,
			'pr.state'=>1,
			'pr.survey_state'=>1
		);
		$url='surveyAgree';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式','操作'];
		$field=['id','site_name','location','status','mobile',''];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		foreach ($data as $k => $v) {
			$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyAgreeDetails?id='.reset($v)['id'].'">审核</a>';
			unset($data[$k][0]);
		}
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
	}



	//出库中
	public function outbound(){
		$parames=$this->parames;
		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>1,
			'pr.state'=>2,
			'pr.survey_state'=>1,
			'pr.audit'=>1
		);
		$url='outbound';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式','操作'];
		$field=['id','site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		foreach ($data as $k => $v) {
			$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveybatcab?siteid='.reset($v)['id'].'">耗材出库</a>';
			unset($data[$k][0]);
		}
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
	}



   //待审核列表
   public function waitCons(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
   		$data=array(
			'st.operation_type'=>1,
			'pr.state'=>2,
			'pr.survey_state'=>1,
			'pr.audit'=>1
		);
		$url='waitCons';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }


   //施工审核列表
	public function surveyAgreeConstruction()
	{
		$parames=$this->parames;
		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>1,
			'pr.state'=>2,
			'pr.survey_state'=>2
		);
		$url='surveyAgreeConstruction';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式','操作'];
		$field=['id','site_name','location','status','mobile',''];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		foreach ($data as $k => $v) {
			$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyAgreesDetails?id='.reset($v)['id'].'">审核</a>';
			unset($data[$k][0]);
		}
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
	}

   //已施工待验收
   public function ConsGain(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
   	   	$data=array(
			'st.operation_type'=>1,
			'pr.state'=>3,
			'pr.survey_state'=>2,
			'pr.audit'=>6
		);
		$url='ConsGain';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }


   //验收已完成
   public function complete(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
   	   	$data=array(
			'st.operation_type'=>1,
			'pr.state'=>4,
			'pr.survey_state'=>2,
			'pr.audit'=>7
		);
		$url='complete';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }


   //大B端耗材出库接口
   public function blocOutbound(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
   		$data=array(
			'st.operation_type'=>2,
			'pr.state'=>2,
			'pr.survey_state'=>1,
			'pr.audit'=>9
		);
		$url='blocOutbound';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }

   //大B端待施工
   public function blocWaitCons(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>2,
			'pr.state'=>2,
			'pr.survey_state'=>1,
			'pr.audit = 1 or pr.audit '=>5
		);
		$url='blocWaitCons';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }
   
   //大B端已施工待审核
   public function blocSurveyAgreeConstruction(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>2,
			'pr.state'=>2,
			'pr.survey_state'=>2,
			'pr.audit'=>3
		);
		$url='surveyAgreeConstruction';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式','操作'];
		$field=['id','site_name','location','status','mobile',''];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		foreach ($data as $k => $v) {
			$data[$k][key(end($v))][key(end($v))]='<a class="btn btn-default btn-xs" href="http://admin.gzmod.com.cn/surveyBlocDetails?id='.reset($v)['id'].'">审核</a>';
			unset($data[$k][0]);
		}
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }

   //大B端待验收
   public function blocConsGain(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>2,
			'pr.state'=>3,
			'pr.survey_state'=>2,
			'pr.audit'=>6
		);
		$url='ConsGain';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }
   //大B端验收已完成
   public function blocComplete(){
   		$parames=$this->parames;
   		$like='';
   		if(array_key_exists('data',$parames)){
   			if(!empty($parames['data'][1])){
   			$str=preg_split('/\s-\s/',$parames['data'][1]);
	        $strTime=strtotime($str[0]);
	        $endTime=strtotime($str[1]);
	        $str=' AND st.create_time>'.$strTime.' AND st.create_time<'.$endTime;
	        $like=$str." AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}else{
	   			$like=" AND CONCAT(IFNULL(st.site_name,'"."'),IFNULL(st.location,'')) LIKE '%".$parames['data'][0]."%'";
	   		}
   		}
		$data=array(
			'st.operation_type'=>2,
			'pr.state'=>4,
			'pr.survey_state'=>2,
			'pr.audit'=>7
		);
		$url='complete';
		$nums=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteListNum($data,$like);
		$showpage= $this->newpage($url,$this->commonDefine['pagesize'],$nums);
		$arrs=M_Mysqli_Class('md_lixiang','SiteModel')->getSurveySiteList($showpage['limit'],$data,$like);
		$action='';
		$title=['站点名称','地址','站点状态','联系方式'];
		$field=['site_name','location','status','mobile'];
		$rule=[
			'status'=>[
				'0'=>'正常',
				'1'=>'禁用'
			]
		];
		$data=$this->disposeData($arrs,$field,$rule);
		$html=$this->createTable($title,$data);
		$arr['arr']=$html;
		$arr['one']=$showpage['show'];
		return $this->setOutPut($arr);die;
   }




   	/*
   	 *	处理表格数据
   	 */
   	private function disposeData($data,$field,$rule){
   		$resultData=[];
   		$i=0;
   		foreach ($data as $k => $v) {
   			foreach ($field as $key => $value) {
   				if(empty($value) || !array_key_exists($value,$v) ){
   					$resultData[$k][]=[
   						$key=>$value
   					];
   					continue;
   				}
   				if(array_key_exists($value,$rule)){
   					$resultData[$k][]=[
   						$value=>$rule[$value][$v[$value]]
   					];
   				}else{
   					$resultData[$k][]=[
   						$value=>$v[$value]
   					];
   				}
   			}
   		}
		return $resultData;
   	}


    /*
     *	表格生成器
     */
    private function createTable($title,$data){
    	$i=0;
    	$html='<table class="table table-striped table-bordered table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                    ';
    	foreach ($title as $k => $v) {
    		$html.='<th>'.$v.'</th>';
    	}
    	$html.='    </tr>
                    </thead>
                    <tbody>';
        foreach ($data as $k => $v) {
        	$html.='<tr class="odd gradeX" >';
        	foreach ($v as $key => $value) {
        		$html.='<td>'.reset($value).'</td>';

        	}
            $html.='</tr>';
        }
        $html.='	</tbody>
                </table>';
        return $html;
    }



   	//获取状态码
   	public function stateCode($arr,$project){
		foreach($arr as $k => $v){
			if($arr[$k]['state']!=5){
				foreach($project as $key => $val){
					if($arr[$k]['id'] == $project[$key]['site_id'] && !empty($project[$key]['state']) ){
						$arr[$k]+=[
							'state_code'=>$project[$key]['state'].$project[$key]['survey_state'].$project[$key]['audit']
						];
					}else{
						$arr[$k]+=[
							'state_code'=>0
						];
					}
				}
			}else{
				$arr[$k]+=[
					'state_code'=>$arr[$k]['state']
				];
			}
		}
		return $arr;
   	}















}


