<?php
header("content-type:text/html;charset=utf-8");
class TransactionModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_site');
        $this->log->log_debug('SurveyInfoModel  model be initialized');
    }

    /**
     * 业务员添加站点
     * @params $arr   站点表数据
     * @params $data  图片表数据
     * @return  bool
     */
    public function salesmanAdd($siteData , $imageData){
        $this->write_db->trans_begin();
       try{
           //站点表添加
           $siteId = $this->insert($siteData);

           //如果添加失败抛出异常
           if(!$siteId){
               throw new \Exception('站点表添加失败');
           }
           for($i = 0 ; $i<count($imageData) ; $i++){
               $imageData[$i]['site_id'] = $siteId;
           }
           //图片表添加
           $imageRs = M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($imageData);

           //如果图片添加返回响应行小于1就抛出异常
           if($imageRs < 1){
               throw new \Exception('图片表添加失败');
           }
           //成功提交,返回true
           $this->write_db->trans_commit();
           return true;
       }catch (\Exception  $e){
           //失败回滚,返回false
           $this->write_db->trans_rollback();
           return false;

       }
    }

    /**
     * 业务员修改站点
     * @params $siteData     站点数据
     * @params $videoCoding  视频数据
     * @params $imageData    图片数据
     * @return bool
     */
    public function salesmanEdit($siteData , $videoCoding ,$insertCoding, $imageData){
        $this->write_db->trans_begin();
        try{
            //站点表添加
            $siteUpRs=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById($siteData);
            //如果修改失败抛出异常
            if($siteUpRs < 1){
                throw new \Exception('站点表修改失败');
            }
            //如果$videoCoding修改视频数据不为null就走这
            if($videoCoding!=null){
                //图片表修改
                $videoUpRs = M_Mysqli_Class('md_lixiang', 'PictureModel')->updatePic(['filename' => $videoCoding['items'][0]['key'] ],['site_id' => $siteData['id'],'type_id'=>13,'platform'=>3]);
                if($videoUpRs < 1){
                    throw new \Exception('视频修改失败');
                }
            }

            //如果$videoCoding新增视频数据不为null就走这
            if($insertCoding!=null){
                //图片表视频新增
                $videoaddRs = M_Mysqli_Class('md_lixiang', 'PictureModel')->saveImage($insertCoding);
                if($videoaddRs < 1){
                    throw new \Exception('视频添加失败');
                }
            }
//            $upPic = M_Mysqli_Class('md_lixiang', 'PictureModel')->updatePic(['status'=>2],['site_id' => $siteData['id'],'type_id' => 12,'platform'=>3]);
//           // 如果图片状态修改失败抛出异常
//            if($upPic < 1){
//                throw new \Exception('图片表修改失败');
//            }
            //该站点数据库图片
            $picInfoDatas=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['site_id'=>$siteData['id'],'platform'=>3,'type_id'=>12]);
            for($i=0;$i<count($picInfoDatas);$i++){
                $picData[$i]=$picInfoDatas[$i]['filename'];
            }

            //提交更改的图片

            for($i=0;$i<count($imageData);$i++){
                $upPicData[$i]=$imageData[$i]['filename'];
            }
            $addPic=array_diff($upPicData,$picData);
            if(!empty($addPic)){
//                for($i=0;$i<=count($addPic);$i++){
                foreach ($addPic as $k=>$v){
                    $addData[$k]=[
                        'type_id'        =>$imageData[0]['type_id'],
                        'image_title'    =>$imageData[0]['image_title'],
                        'site_id'        =>$siteData['id'],
                        'description'    =>$imageData[0]['description'],
                        'filename'       =>$v,  //文件名
                        'save_platform'  => 0,                          //0七牛云
                        'platform'       => 3,                          //3勘测项目
                        'sort'           => 100,                        //图片排序
                        'status'         => 0,
                        'create_date'    => date("Y-m-d H:i:s",time()),
                        'create_time'    => time()
                    ];
                }
//                }
                $picRetrun = M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($addData);
                if($picRetrun < 1){
                    throw new \Exception('图片表添加失败');}
                }
            $upPic=array_diff($picData,$upPicData);
            if(!empty($upPic)){
//                for($i=0;$i<count($upPic);$i++){
                foreach ($upPic as $k=>$v){
                    $upData[$k]=[
                        'type_id'        =>$imageData[0]['type_id'],
                        'site_id'        =>$siteData['id'],
                        'filename'       =>$v,  //文件名
                        'save_platform'  => 0,                          //0七牛云
                        'platform'       => 3,                          //3勘测项目
                    ];
                    $upPicRetrun = M_Mysqli_Class('md_lixiang','PictureModel')->updatePic(['status'=>2],$upData[$k]);
                }


//                }
                if($upPicRetrun < 1){
                    throw new \Exception('图片表修改失败');
                }
            }
            //添加图片数据
//            $pics = M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($imageData);
//            if($pics < 1){
//                throw new \Exception('图片表添加失败');
//            }
//            //修改站点状态
//            $siteStateRs = M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById(['state' => 0,'id' => $siteData['id']]);
//            var_dump($siteStateRs);die;
//            if($siteStateRs < 1){
//                throw new \Exception('站点状态修改失败');
//            }
            //成功提交,返回true
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception  $e){

            //失败回滚,返回false
            $this->write_db->trans_rollback();
            var_dump($e->getMessage());die;
            return false;

        }
    }

    /**
     * 勘测人员填写信息
     * @params $SurveyInfo     勘测信息
     * @params $SurveyImage    图片信息
     * @params $accountInfo    耗材信息
     * @return bool
     */
     public function insSurveyInfo($surveyInfo , $surveyImage,$accountInfo)
     {
         $this->write_db->trans_begin();
         try{
             //勘测信息表添加数据
             $infoReturnId = M_Mysqli_Class('md_survey','SurveyInfoModel')->addSurveyInfo($surveyInfo);
             if($infoReturnId < 1){
                 throw new \Exception('勘测信息表添加失败');
             }
             for($i=0;$i<count($surveyImage);$i++){
                 $surveyImage[$i]['attribute_id']=$infoReturnId;
             }
             $imagesRows = M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($surveyImage);
             if($imagesRows < 1){
                 throw new \Exception('图片表添加失败');
             }
             if($accountInfo!=null){
                 $accountRows = M_Mysqli_Class('md_lixiang','StorageAccountModel')->addBashMate($accountInfo);
                 if($accountRows < 1){
                     throw new \Exception('耗材表添加失败');
                 }
             }

             $projectState=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['survey_state'=>1,'audit'=>0],['id'=>$surveyInfo['project_id']]);
             if($projectState < 1){
                 throw new \Exception('站点状态更新失败');
             }
             $this->write_db->trans_commit();
             return true;
         }catch (\Exception  $e){
             //失败回滚,返回false
             $this->write_db->trans_rollback();
             return false;

         }
     }

     /*
      * 勘测信息修改
     * @params $SurveyInfo     勘测信息
     * @params $SurveyImage    图片信息
     * @params $accountInfo    耗材信息
     * @return bool
     * */
     public function editSurveyInfo($upSurveyInfo,$upSurveyImage,$upAccountInfo,$storageData)
     {
         $this->write_db->trans_begin();
         try{
             //勘测信息表修改
             $editInfo = M_Mysqli_Class('md_survey', 'SurveyInfoModel')->updataState($upSurveyInfo, ['project_id' => $upSurveyInfo['project_id'], 'info_state' => 1, 'state' => 1]);
             if($editInfo < 1){
                 throw new \Exception('勘测信息表修改失败');
             }

             if($upSurveyImage!=null){
                 $infoId=$upSurveyImage[0]['attribute_id'];
                 //修改成功直接往下走   修改失败报错
                 for($i=0;$i<count($upSurveyImage);$i++){
                     $pics[$i] = M_Mysqli_Class('md_lixiang', 'PictureModel')->updatePic($upSurveyImage[$i], ['attribute_id' => $infoId, 'type_id' => $upSurveyImage[$i]['type_id'],'platform'=>3]);
                     if ($pics[$i] < 1) {
                         throw new \Exception('图片表修改失败');
                     }
                 }
             }

             //耗材表修改处理
             if($upAccountInfo!=null){
                 //修改用材信息
                 $accountss = M_Mysqli_Class('md_lixiang', 'StorageAccountModel')->selectAccount(['project_id' => $upSurveyInfo['project_id'], 'status' => 0]);
                 if ($accountss) {
                     $upAcccount = M_Mysqli_Class('md_lixiang', 'StorageAccountModel')->updateAccount(['status' => 2], ['project_id' => $upSurveyInfo['project_id'], 'status' => 0]);
                     $mateId = M_Mysqli_Class('md_lixiang', 'StorageAccountModel')->addBashMate($upAccountInfo);
                 } else {
                     $upAcccount = 1;
                     $mateId = M_Mysqli_Class('md_lixiang', 'StorageAccountModel')->addBashMate($upAccountInfo);
                 }
             }else{
                 $accounts = M_Mysqli_Class('md_lixiang', 'StorageAccountModel')->selectAccount(['project_id' => $upSurveyInfo['project_id'], 'status' => 0]);
                 if ($accounts) {
                     $upAcccount = M_Mysqli_Class('md_lixiang', 'StorageAccountModel')->updateAccount(['status' => 2], ['project_id' => $upSurveyInfo['project_id'], 'status' => 0]);
                     $mateId = 1;
                 } else {
                     $upAcccount = 1;
                     $mateId = 1;
                 }
             }
             if($mateId < 1 || $upAcccount < 1 ){
                 throw new \Exception('耗材表修改失败');
             }

             $siteState = M_Mysqli_Class('md_survey', 'SurveyProjectModel')->updataState(['survey_state' => 1, 'audit' => 0], ['id' => $upSurveyInfo['project_id']]);
             if($siteState < 1 ){
                 throw new \Exception('工程表状态修改失败');
             }

             $this->write_db->trans_commit();
             return true;
         }catch (\Exception  $e){
             //失败回滚,返回false
             $this->write_db->trans_rollback();
//             var_dump($e->getMessage());die;
             return false;
         }

     }

     /*
      * 施工人员提交信息
      * */
     public function consInsSurveyInfo($infoData,$imageData,$projectId,$storageData)
     {
         $this->write_db->trans_begin();
         try{

             //勘测信息添加
             if($infoData!=null){
                 //勘测信息添加
                 $infoReturnId=M_Mysqli_Class('md_survey','SurveyInfoModel')->addSurveyInfo($infoData);
                 if($infoReturnId < 1){
                     throw new \Exception('勘测信息表添加失败');
                 }
             }

             for($i=0;$i<count($imageData);$i++){
                 $imageData[$i]['attribute_id']=$infoReturnId;
             }
             //图片添加
             $picReturnRow=M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($imageData);
             if($picReturnRow < 1){
                 throw new \Exception('图片表添加失败');
             }
                 //耗材订单表
                 $recordData=M_Mysqli_Class('md_lixiang','StorageOrderModel')->getRecordOrderRowData(['site_id'=>$storageData['id'],'order_status'=>2]);
                 for($i=0;$i<count($storageData['cabinet_data']);$i++){
                     $updata=[
                         'site_id'=>$storageData['id'],
                         'site_name'=>$storageData['site_name'],
                         'order_id'=>$recordData['id'],
                         'state'=>2,
                         'create_time'=>time(),
                     ];
                     $recordReturn=M_Mysqli_Class('md_lixiang','StorageSurveyRecordModel')->updateWheresRecord($updata,['type'=>1,'code'=>$storageData['cabinet_data'][$i]]);
                 }
                    $reOrderRreturn=M_Mysqli_Class('md_lixiang','StorageOrderModel')->updateWheresStorageOrder(['order_status'=>1],['id'=>$recordData['id']]);
                 if($recordReturn < 1){
                     throw new \Exception('耗材记录表修改失败');
                 }
                 if($reOrderRreturn < 1){
                     throw new \Exception('耗材订单表修改失败');
                 }

             //工程表状态修改
             $proState=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['survey_state'=>2,'audit'=>3],['id'=>$projectId]);
             if($proState < 1){
                 throw new \Exception('工程表状态修改失败');
             }

             $this->write_db->trans_commit();
             return true;
         }catch (\Exception $e){
             //失败回滚,返回false
             $this->write_db->trans_rollback();
//             var_dump($e->getMessage());die;
             return false;
         }
     }

     /*
      * 施工人员修改
      * */
     public function consEditSurveyInfo($imageData,$projectId,$storageData)
     {
         $this->write_db->trans_begin();
         try{
             if($imageData!=null){
                 $infoId=$imageData[0]['attribute_id'];
                 //修改成功直接往下走   修改失败报错
                 for($i = 0 ; $i < count($imageData) ; $i++){
                     $picsReturnRow[$i]=M_Mysqli_Class('md_lixiang','PictureModel')->updatePic($imageData[$i],['attribute_id'=>$infoId,'type_id'=>$imageData[$i]['type_id'],'platform'=>3]);
                     if($picsReturnRow[$i] < 1){
                         throw new \Exception('图片表修改失败');
                     }
                 }

             }

                 //查询该站点已绑定机柜
                 $storageSiteData=M_Mysqli_Class('md_lixiang','StorageSurveyRecordModel')->getRecordCabinetData(['site_id'=>$storageData['id'],'state'=>2,'type'=>1]);//
                 //查询该站点所属订单号
                 $editRecordData=M_Mysqli_Class('md_lixiang','StorageOrderModel')->getRecordOrderRowData(['site_id'=>$storageData['id'],'order_status'=>1,'order_platform'=>2,'type'=>1]);
                 //耗材订单表
                 $y=0;
                 for($i=0;$i<count($storageSiteData);$i++){
                     //该站点所属机柜
                     $sameData[$y]=$storageSiteData[$i]['code'];
                     $y++;
                 }

                 $h=0;
                 for($i=0;$i<count($storageData['cabinet_data']);$i++){
                     //提交的机柜编号
                     $addData[$h]=$storageData['cabinet_data'][$i];
                     $h++;
                 }
                 $recordReturn='';
                 $sameRecordReturn='';
                 //如果提交过来的编号数据库符合条件没有的机柜编号填充数据
                 $subCinetData=array_diff($addData,$sameData);
                 if($subCinetData){
                     $subdataAdd = [
                         'site_id' => $storageData['id'],
                         'site_name' => $storageData['site_name'],
                         'create_time' => time(),
                         'state' => 2,
                         'order_id' => $editRecordData['id'],

                     ];
//                   for($i=0;$i<count($subCinetData);$i++){
//                       var_dump($subCinetData[$i]);
//                       $sameRecordReturn = M_Mysqli_Class('md_lixiang', 'StorageSurveyRecordModel')->updateWheresRecord($subdataAdd, ['code' =>$subCinetData[$i]]);
//                   }
                     foreach ($subCinetData as $k=>$v){
                         $sameRecordReturn = M_Mysqli_Class('md_lixiang', 'StorageSurveyRecordModel')->updateWheresRecord($subdataAdd, ['type'=>1,'code' =>$v]);
                     }
                 }else{
                     $sameRecordReturn=true;
                 }


                 //如果数据库有符合条件提交更改的机柜没有就把数据恢复为派送状态
                 $databaseData=array_diff($sameData,$addData);
                 if($databaseData){
                     $updataDel = [
                         'site_id' => null,
                         'site_name' => null,
                         'create_time' => time(),
                         'state' => 1,
                     ];
//                   for($i=0;$i<count($databaseData);$i++){
//                       $recordReturn = M_Mysqli_Class('md_lixiang', 'StorageSurveyRecordModel')->updateWheresRecord($updataDel, ['code' => $databaseData[$i]]);
//                   }
                     foreach ($databaseData as $k=>$v){
                         $recordReturn = M_Mysqli_Class('md_lixiang', 'StorageSurveyRecordModel')->updateWheresRecord($updataDel, ['type'=>1,'code' => $v]);
                     }
                 }else{
                     $recordReturn=true;
                 }


                 if($recordReturn<1 || $sameRecordReturn < 1){
                     throw new \Exception('耗材订单表修改失败');
                 }


             $proState=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['survey_state'=>2,'audit'=>3],['id'=>$projectId]);
             if($proState < 1){
                 throw new \Exception('工程表状态修改失败');
             }
             $this->write_db->trans_commit();
             return true;
         }catch (\Exception $e){
             //失败回滚,返回false
             $this->write_db->trans_rollback();
             return false;
         }


     }

     /*
      * 验收人员通过
      * */
     public function checkSitePass($siteData,$siteId,$infoId,$state)
     {
         $this->write_db->trans_begin();
         try{
             $siteReturnRow = M_Mysqli_Class('md_lixiang', 'SiteModel')->updateSiteById($siteData);
             if($siteReturnRow < 1){
                 throw new \Exception('站点表修改失败');
             }

             $projectReturnRow = M_Mysqli_Class('md_survey', 'SurveyProjectModel')->updataState(['state' => $state, 'audit' => 7], ['site_id' => $siteId]);
             if($projectReturnRow < 1){
                 throw new \Exception('工程表表修改失败');
             }

             $infoReturnRow = M_Mysqli_Class('md_survey', 'SurveyInfoModel')->updataState(['state' =>$state], ['id' => $infoId]);
             if($infoReturnRow < 1){
                 throw new \Exception('勘测信息表表修改失败');
             }

//             $cabinetReturnRow = M_Mysqli_Class('md_lixiang', 'CabinetModel')->updateCabinetByAttr(['status'=>0], ['site_id' => $siteId]);
//             if($cabinetReturnRow < 1){
//                 throw new \Exception('机柜表表修改失败');
//             }
             $this->write_db->trans_commit();
             return true;
         }catch (\Exception $e){
             //失败回滚,返回false
             $this->write_db->trans_rollback();
//             var_dump($e->getMessage());die;
             return false;
         }

     }
    /*
     * 验收人员不通过
     * */
    public function checkSiteNotPass($siteData,$siteId,$infoId)
    {
        $this->write_db->trans_begin();
        try{
            $siteReturnRow = M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById($siteData);
            if($siteReturnRow < 1){
                throw new \Exception('站点表修改失败');
            }

            $projectReturnRow = M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['state'=>2,'survey_state'=>1,'audit'=>8],['site_id'=>$siteId]);
            if($projectReturnRow < 1){
                throw new \Exception('工程表表修改失败');
            }

            $infoReturnRow = M_Mysqli_Class('md_survey','SurveyInfoModel')->updataState(['state'=>2],['id'=>$infoId]);
            if($infoReturnRow < 1){
                throw new \Exception('勘测信息表表修改失败');
            }

            $this->write_db->trans_commit();
            return true;
        }catch (\Exception $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
//            var_dump($e->getMessage());die;
            return false;
        }

    }


    /*
     * 添加故障信息
     * */
     public function addMalFunctionData($malData,$picData)
     {
         $this->write_db->trans_begin();
         try{
             $maldata=[
                 'create_record_id'  =>$malData['create_record_id'],
                 'create_record_name'=>$malData['create_record_name'],
                 'malfunction_date'  =>$malData['malfunction_date'],
                 'malfunction_time'  =>$malData['malfunction_time'],
                 'cabinet_id'        =>$malData['cabinet_id'],
                 'cabinet_num'       =>$malData['cabinet_num'],
                 'location'          =>$malData['location'],
                 'malfunction_state' =>2,
                 'state'             =>2,
                 'msg'               =>$malData['msg'],
             ];
             $Malresult=M_Mysqli_Class('md_survey','MalfunctionModel')->addMalfunctionData($maldata);
             if($Malresult < 1){
                 throw new \Exception('故障表添加失败');
             }

             $pivotData=[
                 'malfunction_id'     =>$Malresult,
                 'malfunction_status'=>0,
                 'course_soure'       =>1,
                 'course_status'      =>2,
             ];
             $Pivotresult=M_Mysqli_Class('md_survey','MalfunctionPivotModel')->addMalPivot($pivotData);
             if($Pivotresult < 1){
                 throw new \Exception('pivot表添加失败');
             }


             //增加图片
             if(!empty($picData['filename'])){
                 $picData['filename']=explode(',',$picData['filename']);
                 for($i=0;$i<count($picData['filename']);$i++){
                     $picDatas[$i]['filename']=$picData['filename'][$i];
                     $picDatas[$i]['type_id']=17;
                     $picDatas[$i]['attribute_id']=$Malresult;
                     $picDatas[$i]['image_title']='故障图片';
                     $picDatas[$i]['description']='故障提交图片';
                     $picDatas[$i]['save_platform']=0;
                     $picDatas[$i]['platform']=4;
                     $picDatas[$i]['create_time']=time();
                     $picDatas[$i]['create_date']=date('Y-m-d',time());
                 }
                 $picResult=M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($picDatas);
                 if($picResult < 1){
                     throw new \Exception('图片表添加失败');
                 }

             }

             //添加故障原因
             for($i=0;$i<count($malData['failure_cause']);$i++){
                 $recordData[$i]['pivot_id']=$Pivotresult;
                 $recordData[$i]['failure_cause']=$malData['failure_cause'][$i];
                 $recordData[$i]['type']=1;
                 $recordData[$i]['create_time']=time();
                 $recordData[$i]['create_date']=date('Y-m-d H:i:s',time());
             }
             $Recordresult=M_Mysqli_Class('md_survey','MalfunctionRecordModel')->bashSaveRecord($recordData);
             if($Recordresult < 1){
                 throw new \Exception('故障原因表添加失败');
             }

             $this->write_db->trans_commit();
             return true;
         }catch (\Exception $e){

             //失败回滚,返回false
             $this->write_db->trans_rollback();
             var_dump($e->getMessage());die;
             return false;
         }
     }

     /*
      * 重新分配故障点
      * */
     public function anewDistributionMal($data)
     {
         $this->write_db->trans_begin();
         try{
             $Malresult=M_Mysqli_Class('md_survey','MalfunctionModel')->editMalData(['state'=>2],['id'=>$data['mal_id']]);
             if($Malresult < 1){
                 throw new \Exception('故障表修改失败');
             }

             $pivotData=[
                 'malfunction_id'     =>$data['mal_id'],
                 'malfunction_status' =>0,
                 'course_soure'       =>2,
                 'course_status'      =>2,
             ];
             $Pivotresult=M_Mysqli_Class('md_survey','MalfunctionPivotModel')->addMalPivot($pivotData);
             if($Pivotresult < 1){
                 throw new \Exception('历程表添加失败');
             }


             //添加故障原因
             for($i=0;$i<count($data['failure_cause']);$i++){
                 $recordData[$i]['pivot_id']     =$Pivotresult;
                 $recordData[$i]['failure_cause']=$data['failure_cause'][$i];
                 $recordData[$i]['type']         =1;
                 $recordData[$i]['create_time']  =time();
                 $recordData[$i]['create_date']  =date('Y-m-d H:i:s',time());
             }

             $Recordresult=M_Mysqli_Class('md_survey','MalfunctionRecordModel')->bashSaveRecord($recordData);
             if($Recordresult < 1){
                 throw new \Exception('故障原因表添加失败');
             }

             $this->write_db->trans_commit();
             return true;
         }catch (\Exception $e){
//             var_dump($e->getMessage());die;
             //失败回滚,返回false
             $this->write_db->trans_rollback();
             return false;
         }
     }


    /*
    * 修改故障信息
    * */
    public function editMalFunctionData($malData,$picData,$recordDatas,$moreRecordData)
    {
        $this->write_db->trans_begin();
        try{
            $malid=$malData['mal_id'];unset($malData['mal_id']);
            $maldata=[
                'cabinet_num'      =>$malData['cabinet_num'],
                'cabinet_id'       =>$malData['cabinet_id'],
                'location'         =>$malData['location'],
                'malfunction_date' =>$malData['malfunction_date'],
                'malfunction_time' =>$malData['malfunction_time'],
                'msg'              =>$malData['msg'],
            ];
            $Malresult=M_Mysqli_Class('md_survey','MalfunctionModel')->editMalData($maldata,['id'=>$malid]);
            if($Malresult < 1){
                throw new \Exception('故障表修改失败');
            }


            if(!empty($picData)){
                $malPics=M_Mysqli_Class('md_lixiang','PictureModel')->getImageInfo(['type_id'=>17,'attribute_id'=>$malid,'save_platform'=>0,'platform'=>4]);
                $y=0;
                $mysqlData=[];
                $addPicData=[];
                for($i=0;$i<count($malPics);$i++){

                    $mysqlData[$y]=$malPics[$i]['filename'];
                    $y++;
                }
                $picData=explode(',',$picData);
                $h=0;
                for($i=0;$i<count($picData);$i++){

                    $addPicData[$h]=$picData[$i];
                    $h++;
                }
                $databaseData=array_diff($addPicData,$mysqlData);
                if($databaseData){
//                    for($i=0;$i<count($databaseData);$i++){
                    foreach ($databaseData as $k=>$v){
                        $picDatas[$k]['filename']=$v;
                        $picDatas[$k]['type_id']=17;
                        $picDatas[$k]['attribute_id']=$malid;
                        $picDatas[$k]['image_title']='故障图片';
                        $picDatas[$k]['description']='故障提交图片';
                        $picDatas[$k]['save_platform']=0;
                        $picDatas[$k]['platform']=4;
                        $picDatas[$k]['create_time']=time();
                        $picDatas[$k]['create_date']=date('Y-m-d H:i:s',time());
                    }

//                    }
                    $picResult=M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($picDatas);
                }else{
                    $picResult=true;
                }
                $msqylData=array_diff($mysqlData,$addPicData);
                if($msqylData){
                    foreach ($msqylData as $k=>$v) {
                        $editBaseData = M_Mysqli_Class('md_lixiang', 'PictureModel')->updatePic(['status' => 2], ['type_id'=>17,'attribute_id'=>$malid,'filename' => $v]);
                    }
                }else{
                    $editBaseData=true;
                }

                if($picResult < 1 || $editBaseData < 1){
                    throw new \Exception('图片表添加失败');
                }

                if($picResult < 1){
                    throw new \Exception('图片表添加失败');
                }
            }


                if(!empty($moreRecordData)){
                    for($i=0;$i<count($moreRecordData);$i++){
                        $upRecordData[$i] = M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->delRecordData(['pivot_id'=>$malData['pivot_id'],'type'=>1,'failure_cause'=>$moreRecordData[$i]]);
                    }
                    if($upRecordData < 1){
                        throw new \Exception('故障原因表修改失败');
                    }

                }
                 if(!empty($recordDatas)){
                     //添加故障原因
                     for($i=0;$i<count($recordDatas['failure_cause']);$i++){
                         $recordData[$i]['failure_cause'] =$recordDatas['failure_cause'][$i];
                         $recordData[$i]['pivot_id']      =$malData['pivot_id'];
                         $recordData[$i]['type']       =1;
                         $recordData[$i]['create_time']   =time();
                         $recordData[$i]['create_date']   =date('Y-m-d H:i:s',time());
                     }
                     $Recordresult=M_Mysqli_Class('md_survey','MalfunctionRecordModel')->bashSaveRecord($recordData);
                     if($Recordresult < 1){
                         throw new \Exception('故障原因表添加失败');
                     }
                 }

            $this->write_db->trans_commit();
            return true;

        }catch (\Exception $e){
            //失败回滚,返回false
//                        var_dump($e->getMessage());die;
            $this->write_db->trans_rollback();
            return false;
        }
    }

    /*
     * 故障检测
     * */
    public function malDetectionData($Data)
    {
        $this->write_db->trans_begin();
        try{
            //1为需现场维修
            if($Data['redio_type']==1){
                if(!empty($Data['data']['dbMoreRecordData'])){
                    for($i=0;$i<count($Data['data']['dbMoreRecordData']);$i++){
                        $upRecordData[$i] = M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->delRecordData(['pivot_id'=>$Data['pivot_id'],'type'=>1,'failure_cause'=>$Data['data']['dbMoreRecordData'][$i]]);
                    }
                    if($upRecordData < 1){
                        throw new \Exception('故障原因表删除失败');
                    }

                }
                if(!empty($Data['data']['recordData'])){
                    //添加故障原因
                    for($i=0;$i<count($Data['data']['recordData']['failure_cause']);$i++){
                        $recordData[$i]['failure_cause']   =$Data['data']['recordData']['failure_cause'][$i];
                        $recordData[$i]['pivot_id']        =$Data['pivot_id'];
                        $recordData[$i]['type']            =1;
                        $recordData[$i]['create_time']     =time();
                        $recordData[$i]['create_date']     =date('Y-m-d H:i:s',time());
                    }
                    $Recordresult=M_Mysqli_Class('md_survey','MalfunctionRecordModel')->bashSaveRecord($recordData);
                    if($Recordresult < 1){
                        throw new \Exception('故障原因表添加失败');
                    }
                }
                $pivotUpData=[
                    'malfunction_status'=>1
                ];

                $malUpData=[
                    'fault_level'            =>$Data['fault_level'],
                ];

            }else{


                $malUpData=[
                    'state'            =>$Data['state'],
                    'malfunction_state'=>$Data['malfunction_state'],
                    'fault_level'      =>$Data['fault_level'],
                ];

                $pivotUpData=[
                    'course_status'     =>$Data['course_status'],
                    'results_described' =>$Data['results_described'],
                    'malfunction_status'=>$Data['malfunction_status'],
                    'servicing_time'    =>time(),
                    'servicing_date'    =>date('Y-m-d H:i:s',time()),
                ];
                if(!empty($Data['failure_cause'])){
                    //添加故障原因
                    for($i=0;$i<count($Data['failure_cause']);$i++){
                        $recordData[$i]['failure_cause'] =$Data['failure_cause'][$i];
                        $recordData[$i]['pivot_id']      =$Data['pivot_id'];
                        $recordData[$i]['type']       =2;
                        $recordData[$i]['create_time']   =time();
                        $recordData[$i]['create_date']   =date('Y-m-d H:i:s',time());
                    }
                    $Recordresult=M_Mysqli_Class('md_survey','MalfunctionRecordModel')->bashSaveRecord($recordData);
                    if($Recordresult < 1){
                        throw new \Exception('故障原因表添加失败');
                    }
                }

            }

            $malResult=M_Mysqli_Class('md_survey','MalfunctionModel')->editMalData($malUpData,['id'=>$Data['mal_id']]);
            if($malResult < 1){
                throw new \Exception('故障表修改失败');
            }

            $pivotResult=M_Mysqli_Class('md_survey','MalfunctionPivotModel')->editMalPivotData($pivotUpData,['id'=>$Data['pivot_id']]);
            if($pivotResult < 1){
                throw new \Exception('历程表修改失败');
            }


            $this->write_db->trans_commit();
            return true;
        }catch (\Exception $e){
//            var_dump($e->getMessage());die;
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;
        }
    }

    /*
     * 故障任务分配人员
     * */
    public function malAllotTeatUser($malUserData,$storageData)
    {
        $this->write_db->trans_begin();
        try{
            $malData=[
                'admin_id'       =>$malUserData['admin_id'],
                'admin_user_name'=>$malUserData['admin_user_name'],
                'admin_id'=>$malUserData['admin_id'],
                'malfunction_status'=>3,
                'servicing_date'=>$malUserData['servicing_date'],
                'servicing_time'=>strtotime($malUserData['servicing_date']),
            ];
            $Malresult=M_Mysqli_Class('md_survey','MalfunctionModel')->editMalData($malData,['id'=>$malUserData['id']]);
            if($Malresult < 1){
                throw new \Exception('故障表修改失败');
            }
            if(!empty($storageData)){
                $MalRecordresult=M_Mysqli_Class('md_survey','StorageMalfunctionRecordModel')->bashSaveImage($storageData);
                if($MalRecordresult < 1){
                    throw new \Exception('故障耗材记录表添加失败');
                }
            }

            $this->write_db->trans_commit();
            return true;
        }catch (\Exception $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;
        }
    }

    /*
     * 仓库耗材出库
     * */
    public function warehouseStorageData($storageDatas)
    {

        $this->write_db->trans_begin();
        try{
            $malId=$storageDatas['malfunction_id'];
            $storageData=$storageDatas['storagedata'];
            $Malresult=M_Mysqli_Class('md_survey','MalfunctionModel')->editMalData(['malfunction_status'=>4],['id'=>$malId]);
            if($Malresult < 1){
                throw new \Exception('故障表修改失败');
            }

            $MalRecordresult=M_Mysqli_Class('md_survey','StorageMalfunctionRecordModel')->editRecordData(['status'=>2],['malfunction_id'=>$malId]);
            if($MalRecordresult < 1){
                throw new \Exception('故障表记录修改状态失败');
            }
            for($i=0;$i<count($storageData);$i++){
                $storageDataAttr[$i] = M_Mysqli_Class('md_lixiang', 'StorageMeterialModel')->getMeterNumByInfo(['code'=>$storageData[$i]['code']]);
                $storageData[$i]['type']          =$storageDataAttr[$i]['type'];
                $storageData[$i]['coding']        =$storageDataAttr[$i]['code'];
                $storageData[$i]['malfunction_id']=$malId;
                $storageData[$i]['company']       =$storageDataAttr[$i]['company'];
                $storageData[$i]['num']           =$storageData[$i]['out'];
                $storageData[$i]['create_time']   =time();
                $storageData[$i]['create_date']   =date('Y-m-d H:i:s',time());
                unset($storageData[$i]['out']);
                unset($storageData[$i]['code']);
            }
            $MalRecordresultAdd=M_Mysqli_Class('md_survey','StorageMalfunctionRecordModel')->bashSaveImage($storageData);
            if($MalRecordresultAdd < 1){
                throw new \Exception('故障耗材记录表添加失败');
            }
            $this->write_db->trans_commit();
            return true;
        }catch (\Exception $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;
        }
    }

    /*
     * 流程列表删除
     * */
    public function delMalfunctionData($data)
    {
        $this->write_db->trans_begin();
        try{

            $malStatus = M_Mysqli_Class('md_survey', 'MalfunctionModel')->editMalData(['status'=>$data['status']], ['id'=>$data['id']]);
            if($malStatus < 1){
                throw new \Exception('故障表修改失败');
            }

            $pivotDatas = M_Mysqli_Class('md_survey', 'MalfunctionPivotModel')->getMalfunctionsByAttr(['status'=>0,'malfunction_id'=>$data['id']]);
            for($i=0;$i<count($pivotDatas);$i++){
                $recordStatus[$i] = M_Mysqli_Class('md_survey', 'MalfunctionRecordModel')->editRecordData(['status'=>$data['status']], ['pivot_id'=>$pivotDatas[$i]['id']]);
                if($recordStatus[$i] < 1){
                    throw new \Exception('故障原因表修改失败');
                }
            }

            $pivotStatus = M_Mysqli_Class('md_survey', 'MalfunctionPivotModel')->editMalPivotData(['status'=>$data['status']], ['malfunction_id'=>$data['id']]);
            if($pivotStatus < 1){
                throw new \Exception('历程表修改失败');
            }

            $this->write_db->trans_commit();
            return true;
        }catch (\Exception $e){
            //失败回滚,返回false
            $this->write_db->trans_rollback();
            return false;
        }
    }
}
