<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
class malfunctionsystem extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->parames = $this->getParames();//调用http流方法
        unset($this->parames['currentPage']);
        $this->commonDefine = $this->commonDefine();
        $this->smarty->assign('baseUrl', $this->commonDefine['baseUrl']);
        $this->smarty->assign('resourceUrl', $this->commonDefine['resourceUrl']);
        $this->smarty->assign("function", "malfunctionsystem");
    }


    /**
     * 添加故障信息
     */
    public function addDreakdownData()
    {

        $parames = $this->parames;
        if (IS_GET) {
            $this->checkAuth();
            F()->Resource_module->setTitle('添加故障信息');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('malfunctionsystem/add.phtml');
        } else {
            $parames['malfunction_time'] = strtotime($parames['malfunction_date']);
            $parames['create_record_id'] = $this->session->userdata['user_id'];
            $parames['create_record_name'] = $this->session->userdata['userName'];
            $picData['filename'] = isset($parames['filename'])?$parames['filename']:'';
            unset($parames['filename']);
            $result = M_Mysqli_Class('md_survey', 'TransactionModel')->addMalFunctionData($parames, $picData);
            if ($result > 0) {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 1]);
                $this->msg('添加成功', '/getmalfunctionList', 'ok');
            } else {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 2]);
                $this->msg('添加失败', '/addDreakdownData', 'error');
            }
        }


    }

    /**
     * 二次派修故障信息
     */
    public function resSubmitDreakdownData()
    {
        $this->checkAuth();
        $parames = $this->parames;
        if (IS_GET) {
            F()->Resource_module->setTitle('重新提交故障信息');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
//            $malfunctiomAttrData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionInfo(['id'=>$parames['id']]);
            $malfunctiomAttrData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataInfo(['mal_id'=>$parames['id']]);
            $picData = M_Mysqli_Class('md_lixiang', 'PictureModel')->getImageInfo(['type_id' => 17, 'attribute_id' => $parames['id'], 'save_platform' => 0, 'platform' => 4]);
            if(!empty($picData)){
                for ($i = 0; $i < count($picData); $i++) {
                    $pics[$i]['filename'] = IMG_URL . '/' . $picData[$i]['filename'];

                }
            }else{
                $pics='';
            }


            $this->smarty->assign("malfunctiomAttrData", $malfunctiomAttrData);
            $this->smarty->assign("picData", $pics);
            $this->smarty->view('malfunctionsystem/res_add.phtml');
        } else {
            $result = M_Mysqli_Class('md_survey', 'TransactionModel')->anewDistributionMal($this->parames);
            if ($result > 0) {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 1]);
                $this->msg('添加成功', '/getmalfunctionList', 'ok');
            } else {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 2]);
                $this->msg('添加失败', '/resSubmitDreakdownData?id='.$parames['id'], 'error');
            }
        }


    }


    //获取机柜信息
    public function getCabinetData()
    {
        $cabinet = M_Mysqli_Class('md_lixiang', 'CabinetModel')->getCabinetInfoByAttr(['cabinet_number' => $this->parames['cabinet_num']]);
        $data = [
            'id' => $cabinet['id'],
            'location' => $cabinet['location']
        ];
        $this->setOutPut($data);
        die;
    }

    /*
     * 获取站点信息
     * */
    public function siteJoinCabinetData()
    {
        $parames['site_status']=1;
        $siteData=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteData('',$this->parames);
        $this->setOutPut($siteData);die;
    }

    /*
     * 获取机柜信息
     * */
    public function getSiteJoinCabinetData()
    {
        $cabinetData=M_Mysqli_Class('md_lixiang','CabinetModel')->getAllBoxByAttr(['site_id'=>$this->parames['site_id']]);
        $this->setOutPut($cabinetData);die;
    }

    /*
     * 获取机柜故障图片信息
     * */
    public function getMalfunctionPic()
    {
        $pics = '';
        $picData = M_Mysqli_Class('md_lixiang', 'PictureModel')->getImageInfo($this->parames);
        for ($i = 0; $i < count($picData); $i++) {
            $pics[$i]['filename'] =!empty($picData[$i]['filename'])?IMG_URL . '/' . $picData[$i]['filename']:'';
        }
        $this->setOutPut($pics);
        die;
    }

    //故障维修列表
    public function getmalfunctionList()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('维修系统管理');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $teamData = M_Mysqli_Class('md_survey', 'RepairTeamModel')->getTeamInfoAll(['status' => 0]);
        $this->smarty->assign("teamData", $teamData);
        $this->smarty->view('malfunctionsystem/list.phtml');

    }

    /*
     * 根据机柜信息匹配站点信息
     * */
    public function matchingSiteData($data)
    {
        for($i=0;$i<count($data);$i++){
            $cabinetData[$i] = M_Mysqli_Class('md_lixiang', 'CabinetModel')->getCabinetInfoByAttr(['id'=>$data[$i]['cabinet_id']]);
            $siteData[$i] = M_Mysqli_Class('md_lixiang', 'SiteModel')->getSiteInfoByAttr(['id'=>$cabinetData[$i]['site_id']]);
            $data[$i]['site_name']=$siteData[$i]['site_name'];
        }
        return $data;die;
    }

    /*
     * ajax获取故障待检测列表
     * */
    public function ajaxGetUndMalfunctionData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '0';
        $parames['state']              = 2;
        $parames['course_status']      = 2;
        $parames['malfunction_state']  = 2;
        $parames['record_type']        = 1;
        $url = "ajaxGetUndMalfunctionData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        if($malfunctiomData){
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        for ($i = 0; $i < count($malfunctiomData); $i++) {
            $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
            $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
        }
        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * ajax获取故障已检测列表
     * */
    public function ajaxGetTheGeneratedData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '1';
        $parames['state']              = 2;
        $parames['course_status']      = 2;
        $parames['malfunction_state']  = 2;
        $parames['record_type']        = 1;
        $url = "ajaxGetTheGeneratedData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {

                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * ajax获取故障已派修队伍列表
     * */
    public function ajaxGetTheTroopsData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '2';
        $parames['state']              = 2;
        $parames['course_status']      = 2;
        $parames['malfunction_state']  = 2;
        $parames['record_type']        = 1;
        $url = "ajaxGetTheTroopsData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {

                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
         * ajax获取故障已派修人员列表
         * */
    public function ajaxGetTheUserData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '3';
        $parames['state']              = 2;
        $parames['course_status']      = 2;
        $parames['malfunction_state'] = 2;
        $parames['record_type']        = 1;
        $url = "ajaxGetTheUserData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {

                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * 获取未完成列表
     * */
    public function ajaxGetUnfinishedMalData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '5';
        $parames['state']              = 1;
        $parames['course_status']      = 1;
        $parames['malfunction_state']  = 2;
        $parames['record_type']        = 1;
        $url = "ajaxGetUnfinishedMalData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {

                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
    * 获取远程已完成列表
    * */
    public function ajaxGetendMalData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '7';
        $parames['state']              = 1;
        $parames['course_status']      = 1;
        $parames['malfunction_state']  = 1;
        $parames['record_type']        = 1;
        $url = "ajaxGetendMalData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {

                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
   * 获取现场已完成列表
   * */
    public function ajaxGetsceneEndMalData()
    {
        $parames = $this->parames;
        $parames['malfunction_status'] = '6';
        $parames['state'] = 1;
        $parames['course_status']      = 1;
        $parames['malfunction_state']  = 1;
        $parames['record_type']        = 1;
        $url = "ajaxGetsceneEndMalData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {

                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    //添加维修队
    public function addRepairTeam()
    {
        $this->checkAuth();
        if (IS_POST) {
            $parames = $this->parames;
            $parames['team_type'] = 1;
            $data = M_Mysqli_Class('md_survey', 'RepairTeamModel')->addRepairTeam($parames);
            if ($data > 0) {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 1]);
                $this->msg('添加成功', '/repairTeamList', 'ok');                                             //记得路由修改成维修队管理
            } else {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 2]);
                $this->msg('添加失败', '/addRepairTeam', 'error');
            }
        } else {
            F()->Resource_module->setTitle('添加维修队');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('malfunctionsystem/team_add.phtml');
        }
    }

    /*
     * 添加维修人员
     * */
    public function addMalfunctionUser()
    {
        $this->checkAuth();
        $parames = $this->parames;
        if (IS_POST) {
            $adminData = M_Mysqli_Class('md_lixiang', 'AdminModel')->getUserInfoByAttr(['user_flag' => 5, 'mobile' => $parames['mobile'], 'attr_type' => $parames['team_id']]);
            if (count($adminData) > 1) {
                $this->msg('该维修人员已存在', '/addMalfunctionUser', 'error');
            }
            $data = [
                'user_name' => $parames['user_name'],
                'user_flag' => 5,
                'password' => md5(md5($parames['password'])),
                'attr_type' => $parames['team_id'],
                'salt' => $this->getRandomString(5),
                'mobile' => $parames['mobile'],
                'create_ip' => $this->getClientIP(),
            ];
            $adminResult = M_Mysqli_Class('md_lixiang', 'AdminModel')->saveAdmin($data);
            if ($adminResult > 0) {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 1]);
                $this->msg('添加成功', '/malUserList', 'ok');                             //记得该工作人员列表
            } else {
                $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 2]);
                $this->msg('添加失败', '/addMalfunctionUser', 'error');
            }
        } else {
            F()->Resource_module->setTitle('添加维修人员');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->view('malfunctionsystem/add_team_user.phtml');
        }
    }

    /*
     * 修改故障信息
     * */
    public function editMalfunctionData()
    {
        $parames = $this->parames;
        $this->checkAuth();
        if (IS_GET) {
            if (empty($this->parames['type'])) {
                F()->Resource_module->setTitle('修改故障信息');
                F()->Resource_module->setJsAndCss(array(
                    'home_page'
                ), array(
                    'main'
                ));
                $this->smarty->assign('pivot_id', $this->parames['pivot_id']);
                $this->smarty->view('malfunctionsystem/update.phtml');
            } else {
                $malfunctionAttr = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataInfo(['pivot_id' => $this->parames['pivot_id'],'record_type'=>1]);
                $pics = '';
                $pickey = '';
                $picData = M_Mysqli_Class('md_lixiang', 'PictureModel')->getImageInfo(['type_id' => 17, 'attribute_id' => $malfunctionAttr['id'], 'save_platform' => 0, 'platform' => 4]);
                for ($i = 0; $i < count($picData); $i++) {
                    $pickey[$i]['filename'] = $picData[$i]['filename'];
                    $pics[$i]['filename'] = IMG_URL . '/' . $picData[$i]['filename'];

                }
                $malfunctionAttr['attr_failure'] = explode(',', isset($malfunctionAttr['attr_failure']) ? $malfunctionAttr['attr_failure'] : '');
                $malfunctionAttr['failure_cause'] = explode(',', isset($malfunctionAttr['failure_cause']) ? $malfunctionAttr['failure_cause'] : '');
                $data['malfunctionAttr'] = $malfunctionAttr;
                $data['pics'] = $pics;
                $data['pic_key'] = $pickey;
                $this->setOutPut($data);
                die;
            }

        } elseif (IS_POST) {
            $parames['malfunction_time'] = strtotime($parames['malfunction_date']);
            $picDatas = isset($parames['filename'])?$parames['filename']:'';
            unset($parames['filename']);
            $data=$this->dataFiltration($parames);
            //$dbMoreRecordData  需删除的数据       $recordData需添加的数据
            $malfunctionAttr = M_Mysqli_Class('md_survey', 'TransactionModel')->editMalFunctionData($parames, $picDatas,isset($data['recordData'])?$data['recordData']:'',isset($data['dbMoreRecordData'])?$data['dbMoreRecordData']:'');
            if ($malfunctionAttr) {
                $this->writeBackstageLog(['operation_type' => 3, 'operation_state' => 1]);
                $this->msg('修改成功', '/getmalfunctionList', 'ok');
            } else {
                $this->writeBackstageLog(['operation_type' => 3, 'operation_state' => 2]);
                $this->msg('修改失败', '/editMalfunctionData?pivot_id=' . $parames['pivot_id'], 'error');
            }
        }
    }

    /*
     * 检测故障
     * */
    public function detectionMal()
    {
        $parames = $this->parames;
        $this->checkAuth();
        if (IS_GET) {
            if (empty($this->parames['type'])) {
                F()->Resource_module->setTitle('故障检测');
                F()->Resource_module->setJsAndCss(array(
                    'home_page'
                ), array(
                    'main'
                ));
                $this->smarty->assign('pivot_id', $this->parames['pivot_id']);
                $this->smarty->view('malfunctionsystem/detection.phtml');
            } else {
                $malfunctionAttr = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataInfo(['pivot_id' => $this->parames['pivot_id'],'record_type'=>1]);
                $pics = '';
                $pickey = '';
                $picData = M_Mysqli_Class('md_lixiang', 'PictureModel')->getImageInfo(['type_id' => 17, 'attribute_id' =>$malfunctionAttr['id'], 'save_platform' => 0, 'platform' => 4]);
                for ($i = 0; $i < count($picData); $i++) {
                    $pickey[$i]['filename'] = $picData[$i]['filename'];
                    $pics[$i]['filename'] = IMG_URL . '/' . $picData[$i]['filename'];

                }
//                $malfunctionAttr['attr_failure'] = explode(',', isset($malfunctionAttr['attr_failure']) ? $malfunctionAttr['attr_failure'] : '');
                $malfunctionAttr['failure_cause'] = explode(',', isset($malfunctionAttr['failure_cause']) ? $malfunctionAttr['failure_cause'] : '');
                $data['malfunctionAttr'] = $malfunctionAttr;
                $data['pics'] = $pics;
                $data['pic_key'] = $pickey;
                $this->setOutPut($data);
                die;
            }
        } else {

            //如果为1 需现场维修  2远程已排查
            if ($parames['redio_type'] == 1) {
                $parames['data']=$this->dataFiltration($parames);
                $parames['malfunction_status'] = 1;
            } else {
                $parames['state'] = 1;
                $parames['malfunction_status'] = 7;
                $parames['malfunction_state']  = 1;
                $parames['message_source']     = 1;
                $parames['course_status']      = 1;
                $parames['type']               = 2;
                $parames['servicing_date'] = date('Y-m-d H:i:s',time());
                $parames['servicing_time'] = time();
            }
            $Malresult = M_Mysqli_Class('md_survey', 'TransactionModel')->malDetectionData($parames);
//            $Malresult = M_Mysqli_Class('md_survey', 'MalfunctionModel')->editMalData($parames, ['id' => $malid]);
            if ($Malresult) {
                $this->writeBackstageLog(['operation_type' => 3, 'operation_state' => 1]);
                $this->msg('修改成功', '/getmalfunctionList', 'ok');
            } else {
                $this->writeBackstageLog(['operation_type' => 3, 'operation_state' => 2]);
                $this->msg('修改失败', '/detectionMal?pivot_id=' . $parames['pivot_id'], 'error');
            }
        }


    }


    /*
     * 任务派修队伍
     * */
    public function malAllotTeam()
    {
        $this->checkAuth();
        $data = [
            'team_name' => $this->parames['team_name'],
            'team_id' => $this->parames['team_Id'],
            'malfunction_status' => 2,
        ];
        $updateStatus = M_Mysqli_Class('md_survey', 'MalfunctionPivotModel')->editMalPivotData($data, ['id' => $this->parames['pivot_id']]);
        if ($updateStatus > 0) {
            $this->writeBackstageLog(['operation_type' => 3, 'operation_state' => 1]);
            $this->msg('分配成功', '/getmalfunctionList', 'ok');
        } else {
            $this->writeBackstageLog(['operation_type' => 3, 'operation_state' => 2]);
            $this->msg('分配失败,请联系管理员', '/getmalfunctionList', 'error');
        }
    }

    /*
     * 任务分配给维修人员
     * */
    public function malAllotTeatUser()
    {
        $parames = $this->parames;
        $this->checkAuth();

        $malData = [
            'admin_id' => $parames['admin_id'],
            'admin_user_name' => $parames['admin_user_name'],
            'malfunction_status' => 3,
            'servicing_date' => $parames['servicing_date'],
            'servicing_time' => strtotime($parames['servicing_date']),
        ];
        $Malresult = M_Mysqli_Class('md_survey', 'MalfunctionPivotModel')->editMalPivotData($malData, ['id' => $parames['pivot_id']]);
        if ($Malresult) {
            $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 1]);
            echo 1;
            die;
        } else {
            $this->writeBackstageLog(['operation_type' => 4, 'operation_state' => 2]);
            echo 2;
            die;
        }
    }

    /*
     * 获取队伍信息
     * */
    public function getRepairTeamData()
    {
        $repairData = M_Mysqli_Class('md_survey', 'RepairTeamModel')->getTeamInfoAll(['status' => 0, 'type' => 1, 'team_name' => $this->parames['team_name']]);
        $this->setOutPut($repairData);
        die;
    }

    /*
     * 获取维修人员信息
     * */
    public function getTeamUserData()
    {
        $this->parames['status'] = 0;
        $this->parames['user_flag'] = 5;
        $adminData = M_Mysqli_Class('md_lixiang', 'AdminModel')->getAdminInfoData($this->parames);
        $this->setOutPut($adminData);
        die;
    }

    /*
     * 获取耗材信息
     * */
    public function getStorageData()
    {
        $storageData = M_Mysqli_Class('md_survey', 'StorageMalfunctionRecordModel')->getMalfunctionsRecordByAttr(['pivot_id' => $this->parames['pivot_id'], 'status' => 0]);
        $this->setOutPut($storageData);
        die;
    }

    /*
     * 维修出库管理耗材管理
     * */
    public function malComeStorage()
    {
        $this->checkAuth();
        F()->Resource_module->setTitle('维修耗材出库管理');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->smarty->view('malfunctionsystem/mal_come_storage.phtml');
    }

    /*
     * 耗材出库中列表
     * */
    public function ajaxGetStorageOutboundData()
    {
        $parames = $this->parames;
        $url = "ajaxGetStorageOutboundData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', ['malfunction_status' => $parames['malfunction_status'], 'state' => 2]);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * 耗材已出库列表
     * */
    public function ajaxGetStorageComeData()
    {
        $parames = $this->parames;
        $url = "ajaxGetStorageComeData";
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', ['malfunction_status' => $parames['malfunction_status'], 'state' => 2]);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * 仓库出库列表搜索
     * */
    public function storageDataSearch()
    {
        $parames = $this->parames;
        $agentBlancenSearchNum = M_Mysqli_Class('md_lixiang', 'UserWalletModel')->getUserWalletData('', $parames);
        $url = "storageDataSearch";
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], count($agentBlancenSearchNum));
        $limit = " LIMIT " . $showpage['limit'];
        $agentBlancenSearchData = M_Mysqli_Class('md_lixiang', 'UserWalletModel')->getUserWalletData($limit, $parames);
        $arr['arr'] = $agentBlancenSearchData;
        $arr['one'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * 耗材出库详情页
     * */
    public function storageComeInfoData()
    {
        $parames = $this->parames;
        if (IS_GET) {
            if (empty($parames['type'])) {
                F()->Resource_module->setTitle('耗材出库详情页');
                F()->Resource_module->setJsAndCss(array(
                    'home_page'
                ), array(
                    'main'
                ));
                $this->smarty->assign('malfunction_id', $parames['malfunction_id']);
                $this->smarty->view('malfunctionsystem/agree_details_info.phtml');
            } else {
                $storageData = M_Mysqli_Class('md_survey', 'StorageMalfunctionRecordModel')->getMalfunctionsRecordByAttr(['malfunction_id' => $parames['malfunction_id'], 'status' => 0, 'state' => 1]);
                $this->setOutPut($storageData);
                die;
            }

        }
    }

    /*
     * 仓库耗材出库
     * */
    public function warehouseStorageData()
    {
        $parames = $this->parames;
        if ($parames['storagedata']) {
            $updateStatus = M_Mysqli_Class('md_survey', 'TransactionModel')->warehouseStorageData($parames);
            if ($updateStatus) {
                echo 1;
                die;
            } else {
                echo 2;
                die;
            }
        }
    }

    /*
     * 故障流程列表搜索
     * */
    public function ajaxMalsearchData()
    {
        $parames = $this->parames;
        if($parames['malfunction_status'] < 6){
            $parames['state'] = 2;
        }else{
            $parames['state'] = 1;
        }
        $url = "ajaxMalsearchData";
        $parames['record_type']=1;
        $malfunctiomNum = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataCount('', $parames);
        $showpage = $this->newpage($url, $this->commonDefine['pagesize'], $malfunctiomNum);
        $limit = " LIMIT " . $showpage['limit'];
        $malfunctiomData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionDataAll($limit, $parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        if($malfunctiomData){
            for ($i = 0; $i < count($malfunctiomData); $i++) {
                $malfunctiomData[$i]['message_source'] = $messageSource[$malfunctiomData[$i]['message_source']];
                $malfunctiomData[$i]['status'] = $status[$malfunctiomData[$i]['status']];
            }
            $malfunctiomData=$this->matchingSiteData($malfunctiomData);
        }

        $arr['arr'] = $malfunctiomData;
        $arr['page'] = $showpage['show'];
        $this->setOutPut($arr);
        die;
    }

    /*
     * 流程列表删除故障信息
     * */
    public function delMalData()
    {
        $this->checkAuth();
//        $updateStatus = M_Mysqli_Class('md_survey', 'MalfunctionModel')->editMalData(['status'=>$this->parames['status']], ['id'=>$this->parames['id']]);
        $updateStatus = M_Mysqli_Class('md_survey', 'TransactionModel')->delMalfunctionData($this->parames);
        if($updateStatus){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('删除成功','/getmalfunctionList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('删除失败','/getmalfunctionList','error');
        }
    }

    /*
     * 维修列表删除
     * */
    public function delMalListData()
    {
        $this->checkAuth();
//        $updateStatus = M_Mysqli_Class('md_survey', 'MalfunctionModel')->editMalData(['status'=>$this->parames['status']], ['id'=>$this->parames['id']]);
        $updateStatus = M_Mysqli_Class('md_survey', 'TransactionModel')->delMalfunctionData($this->parames);
        if($updateStatus > 0){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('删除成功','/getMalfuncationList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('删除失败','/getMalfuncationList','error');
        }
    }

    /*
     * 故障维修总列表+检索
     * */
    public function getMalfuncationList()
    {
        $parames=$this->parames;
        $this->checkAuth();
        F()->Resource_module->setTitle('维修列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames['record_type']=1;
        $teamData=M_Mysqli_Class('md_survey','RepairTeamModel')->getTeamData(['team_type'=>1]);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "getMalfuncationList?".$uri;
        $malFunctionNums=M_Mysqli_Class('md_survey','MalfunctionModel')->getMalfunctionDataCount('',$parames);
        $showpage= $this->page($url,$this->commonDefine['pagesize'],$malFunctionNums);
        $limit=" LIMIT ".$showpage['limit'];
        $malFunctionData=M_Mysqli_Class('md_survey','MalfunctionModel')->getMalfunctionDataAll($limit,$parames);
        $messageSource = [0 => '手机端提交', 1 => '后台提交', 2 => '第三方接口'];
        $status = [0 => '正常', 1 => '禁用', 2 => '逻辑删除'];
        $malfunctionStatus = [0 => '待检测', 1 => '已检测', 2 => '已分配队伍',3=>'已分配人员',5=>'未完成',6=>'现场完成',7=>'远程完成'];
        if($malFunctionData){
            for ($i = 0; $i < count($malFunctionData); $i++) {
                $malFunctionData[$i]['message_source'] = $messageSource[$malFunctionData[$i]['message_source']];
                $malFunctionData[$i]['status']         = $status[$malFunctionData[$i]['status']];
                $malFunctionData[$i]['malfunction_status'] = $malfunctionStatus[$malFunctionData[$i]['malfunction_status']];
            }
            $malFunctionData=$this->matchingSiteData($malFunctionData);
        }


        $parames['malfunction_status']=isset($parames['malfunction_status'])?$parames['malfunction_status']:'';
        $parames['team_id']           =isset($parames['team_id'])?$parames['team_id']:'';
        $parames['state']             =isset($parames['state'])?$parames['state']:'';
        $this->smarty->assign("malFunctionData", $malFunctionData);
        $this->smarty->assign("parames", $parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->assign("teamData", $teamData);
        $this->smarty->view('malfunctionsystem/list_all.phtml');
    }

    /*
     * 维修人员列表
     * */
    public  function malUserList()
    {
        $this->checkAuth();
        $parames=$this->parames;
        F()->Resource_module->setTitle('维修人员列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames['user_flag']='5';
        $teamDatas=M_Mysqli_Class('md_survey','RepairTeamModel')->getTeamData([1=>1]);
        $malNums=M_Mysqli_Class('md_lixiang','AdminModel')->getBackData('',$parames);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "malUserList?".$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($malNums));
        $limit=' LIMIT '.$showpage['limit'];
        $malDatas=M_Mysqli_Class('md_lixiang','AdminModel')->getBackData($limit,$parames);
        $this->smarty->assign('teamDatas',$teamDatas);
        $this->smarty->assign('malDatas',$malDatas);
        $this->smarty->assign('parames',$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('malfunctionsystem/back_user_list.phtml');
    }

    /*
     * 维修人员删除及禁用
     * */
    public function malUserStatusEdit()
    {
        $this->checkAuth();
        $updateStatus = M_Mysqli_Class('md_lixiang', 'AdminModel')->updateAdminByAttr(['status'=>$this->parames['status'],'id'=>$this->parames['id']]);
        if($updateStatus > 0){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('修改成功','/malUserList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/malUserList','error');
        }
    }

    /*
     * 修改维修人员信息
     * */
    public function malUserEditData()
    {
        if(IS_GET){
            $this->checkAuth();
            F()->Resource_module->setTitle('修改维修人员信息');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $userData = M_Mysqli_Class('md_lixiang', 'AdminModel')->getUserInfoByAttr(['id'=>$this->parames['id']]);
            $this->smarty->assign('userData',$userData);
            $this->smarty->view('malfunctionsystem/mal_user_update.phtml');
        }else{
            $userData = M_Mysqli_Class('md_lixiang', 'AdminModel')->getUserInfoByAttr(['mobile'=>$this->parames['mobile'],'user_flag'=>5]);
            if(!empty($userData) && $userData['id']!=$this->parames['id']){
                $this->msg('账户已存在','/malUserEditData?id='.$this->parames['id'],'error');
            }
            $data=[
                'user_name'=>$this->parames['user_name'],
                'mobile'   =>$this->parames['mobile'],
                'password' =>md5(md5($this->parames['password'])),
                'id'=>$this->parames['id']
            ];
            $userStatus = M_Mysqli_Class('md_lixiang', 'AdminModel')->updateAdminByAttr($data);
            if($userStatus > 0){
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
                $this->msg('修改成功','/malUserList','ok');
            }else{
                $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
                $this->msg('修改失败','/malUserEditData','error');
            }

        }
    }

    /*
     * 维修队列表
     * */
    public function repairTeamList()
    {
//        $this->checkAuth();
        F()->Resource_module->setTitle('维修队列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $this->parames['status']='2';
        $teamNums = M_Mysqli_Class('md_survey', 'RepairTeamModel')->getTeamBackList('',$this->parames);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "repairTeamList?".$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($teamNums));
        $limit=' LIMIT '.$showpage['limit'];
        $teamData = M_Mysqli_Class('md_survey', 'RepairTeamModel')->getTeamBackList($limit,$this->parames);
        $this->smarty->assign('teamData',$teamData);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('malfunctionsystem/team_list.phtml');
    }

    /*
     * 维修队删除及修改状态
     * */
    public function editRepairTeamStatus()
    {
        $this->checkAuth();
        $updateStatus = M_Mysqli_Class('md_survey', 'RepairTeamModel')->updateTeamData(['status'=>$this->parames['status']],['id'=>$this->parames['id']]);
        if($updateStatus > 0){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('修改成功','/repairTeamList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/repairTeamList','error');
        }
    }

    /*
     * 修改分配维修队
     * */
    public function editAllotTeam()
    {
          $this->checkAuth();
          $data=[
              'team_id'=>$this->parames['team_Id'],
              'team_name'=>$this->parames['team_name'],
          ];
        $updateStatus=M_Mysqli_Class('md_survey','MalfunctionPivotModel')->editMalPivotData($data,['id'=>$this->parames['pivot_id']]);
        if($updateStatus > 0){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            $this->msg('修改成功','/getmalfunctionList','ok');
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            $this->msg('修改失败','/getmalfunctionList','error');
        }
    }

    /*
     * 修改分配维修员
     * */
    public function editAllotUser()
    {
        $this->checkAuth();
        $data=[
            'admin_id'=>$this->parames['admin_id'],
            'admin_user_name'=>$this->parames['admin_user_name'],
            'servicing_date'=>$this->parames['servicing_date'],
            'servicing_time'=>strtotime($this->parames['servicing_date']),
        ];

        $updateStatus=M_Mysqli_Class('md_survey','MalfunctionPivotModel')->editMalPivotData($data,['id'=>$this->parames['pivot_id']]);
        if($updateStatus > 0){
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
            echo 1;die;
        }else{
            $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
            echo 2;die;
        }
    }


    /*
     * 故障原因数据过滤
     * */
    public function dataFiltration($parames)
    {

        //查询出所属故障点故障原因
        $dbData = M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->getMalfunctionsRecordRequiredAttr(['status' => 0,'type'=>1,'pivot_id'=>$parames['pivot_id']]);

        //取出值与提交过来的值对比
        for($i=0;$i<count($dbData);$i++){
            $dbdata[$i]=$dbData[$i]['failure_cause'];
        }
        //提交过来的故障原因比数据库多出的就添加
        $databaseRecordDatas=array_diff($parames['failure_cause'],$dbdata);
        foreach ($databaseRecordDatas as $k=>$v){
            $databaseRecordData['failure_cause'][]=$v;
        }

        //数据库数据比对提交过来的故障原因  多出的删除
        $dbMoreRecordData=array_diff($dbdata,$parames['failure_cause']);
        $y=0;
        foreach ($dbMoreRecordData as $v){
            $dbMoreRecordDataAttr[$y]=$v;
            $y++;
        }
        $data=[
            'dbMoreRecordData'=>isset($dbMoreRecordDataAttr)?$dbMoreRecordDataAttr:'',
            'recordData'=>isset($databaseRecordData)?$databaseRecordData:'',
        ];
        return $data;die;
    }

    /*
     * 查看维修历程
     * */
    public function maintenanceProcess()
    {
        if(IS_GET){
            F()->Resource_module->setTitle('维修系统管理');
            F()->Resource_module->setJsAndCss(array(
                'home_page'
            ), array(
                'main'
            ));
            $this->smarty->assign('mal_id',$this->parames['id']);
            $this->smarty->view('malfunctionsystem/process_list.phtml');
        }else{
            $malData = M_Mysqli_Class('md_survey', 'MalfunctionModel')->getMalfunctionByAttr(['status' => 0,'id'=>$_GET['mal_id']]);
            $picDatas = M_Mysqli_Class('md_lixiang', 'PictureModel')->getImageInfo(['type_id' => 17,'attribute_id'=>$_GET['mal_id'],'save_platform'=>0,'platform'=>4]);
            $pivotData = M_Mysqli_Class('md_survey', 'MalfunctionPivotModel')->getPivotByAttrOrderBy(['status' => 0,'malfunction_id'=>$_GET['mal_id']]);

            for($i=0;$i<count($pivotData);$i++){
                $pivotData[$i]['recordData'] = M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->getGroupingRecord(['status' => 0,'pivot_id'=>$pivotData[$i]['id'],'type'=>1]);
                $pivotData[$i]['recordData2'] = M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->getGroupingRecord(['status' => 0,'pivot_id'=>$pivotData[$i]['id'],'type'=>2]);
                $pivotData[$i]['storgRecordData'] = M_Mysqli_Class('md_survey', 'StorageMalfunctionRecordModel')->getMalfunctionsRecordByAttr(['status' => 0,'pivot_id'=>$pivotData[$i]['id']]);
//                for($k=0;$k<count($pivotData[$i]['recordData']);$k++){
//                    $failureCause[$i][$k]=$pivotData[$i]['recordData'][$k]['failure_cause'];
//                    $attrFailure[$i][$k] =$pivotData[$i]['recordData'][$k]['attr_failure'];
////                    $pivotData[$i]['recordData']['attr_failure'][$k]=implode(' ',$attrFailure[$i][$k]);
//                  for ($j=0;$j<count($failureCause[$i][$k]);$j++){
//                      $failureCauseAttr[$k]= $failureCause[$i][$k][$j];
//                      $pivotData[$i]['recordData']['aaa']=implode(',',$failureCauseAttr);
//                  }
//                }
            }
            if(!empty($picDatas)){
                for($i=0;$i<count($picDatas);$i++){
                    $picData[$i]=IMG_URL.'/'.$picDatas[$i]['filename'];
                }
            }
            $malfunctionStatus=[0=>'待检测',1=>'已检测',2=>'已分配队伍',3=>'已分配维修人员',5=>'未完成',6=>'现场已完成',7=>'远程完成'];
            for($i=0;$i<count($pivotData);$i++){
                $pivotData[$i]['malfunction_status']=$malfunctionStatus[$pivotData[$i]['malfunction_status']];
            }

            $data=[
                'mal_data'=>$malData,
                'pic_data'=>isset($picData)?$picData:'',
                'pivot_data'=>$pivotData,
            ];
            $this->setOutPut($data);die;
        }

    }

    /*
     * 获取维修后故障原因与描述
     * */
    public function getAfterMalRecord()
    {
        $recordData= M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->getGroupingRecord(['status' => 0,'pivot_id'=>$this->parames['pivot_id'],'type'=>2]);
        if(empty($recordData)){
            $recordData=1;
        }
        $this->setOutPut($recordData);die;
    }

    /*
     * 查看队伍所属成员
     * */
    public function affiliatedTeamUser()
    {
        $parames=$this->parames;
        $teamName=isset($parames['team_name'])?$parames['team_name'].'-':'';
        F()->Resource_module->setTitle($teamName.'所属维修人员列表');
        F()->Resource_module->setJsAndCss(array(
            'home_page'
        ), array(
            'main'
        ));
        $parames['user_flag']='5';
        $malNums=M_Mysqli_Class('md_lixiang','AdminModel')->getBackData('',$parames);
        $uri=$this->makeSearchUrl($this->parames);
        $url = "affiliatedTeamUser?".$uri;
        $showpage= $this->page($url,$this->commonDefine['pagesize'],count($malNums));
        $limit=' LIMIT '.$showpage['limit'];
        $malDatas=M_Mysqli_Class('md_lixiang','AdminModel')->getBackData($limit,$parames);
        $this->smarty->assign('malDatas',$malDatas);
        $this->smarty->assign('parames',$parames);
        $this->smarty->assign("pages", $showpage['show']);
        $this->smarty->view('malfunctionsystem/affiliated_team_user.phtml');
    }



    /*
     * 维修队分配维修员表单
     * */
    public function teamAffilitedUser()
    {
       $this->checkAuth();
       $parames=$this->parames;
       if(IS_GET){
           F()->Resource_module->setTitle('分配维修员表单');
           F()->Resource_module->setJsAndCss(array(
               'home_page'
           ), array(
               'main'
           ));
           $this->smarty->assign('team_id',$parames['team_id']);
           $this->smarty->view('malfunctionsystem/allocation_team_user.phtml');
       }elseif (IS_POST){
           $adminStatus=M_Mysqli_Class('md_lixiang','AdminModel')->updateAdminByAttr(['id'=>$parames['admin_id'],'attr_type'=>$parames['attr_type'],'user_flag'=>5]);
           if($adminStatus > 0){
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>1]);
               $this->msg('分配成功','/repairTeamList?team_id='.$parames['attr_type'],'ok');
           }else{
               $this->writeBackstageLog(['operation_type'=>3,'operation_state'=>2]);
               $this->msg('分配失败','/teamAffilitedUser?team_id='.$parames['attr_type'],'error');
           }
       }else{
           $data=isset($parames['user_name'])?$parames['user_name']:'';
           $adminData=M_Mysqli_Class('md_lixiang','AdminModel')->selectInsider($data);
           if($adminData){
               $this->setOutPut($adminData);exit;
           }else{
               echo '';exit;
           }
       }
    }
}


