<?php
header("content-type:text/html;charset=utf-8");
class TransModel extends DB_Model
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
    public function salesmanEdit($siteData , $videoCoding , $imageData){
        $this->write_db->trans_begin();
        try{
            //站点表添加
            $siteUpRs=M_Mysqli_Class('md_lixiang','SiteModel')->updateSiteById($siteData);
            //如果修改失败抛出异常
            if($siteUpRs < 1){
                throw new \Exception('站点表修改失败');
            }
            //如果$videoCoding视频数据不为null就走这
            if($videoCoding!=null){
                 //$videoCoding['items'][0]['key']
                $videoUpRs = M_Mysqli_Class('md_lixiang', 'PictureModel')->updatePic(['filename' => $videoCoding['items'][0]['key'] ],['site_id' => $siteData['id'],'type_id'=>13]);
                if($videoUpRs < 1){
                    throw new \Exception('视频修改失败');
                }
            }
            $upPic = M_Mysqli_Class('md_lixiang', 'PictureModel')->updatePic(['status'=>2],['site_id' => $siteData['id'],'type_id' => 12]);
            //如果图片状态修改失败抛出异常
            if($upPic < 1){
                throw new \Exception('图片表修改失败');
            }
            //添加图片数据
            $pics = M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($imageData);
            if($pics < 1){
                throw new \Exception('图片表添加失败');
            }
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
            return false;
        }
    }

    /**
     * 勘测人员填写信息
     * @params $siteData     站点数据
     * @params $videoCoding  视频数据
     * @params $imageData    图片数据
     * @return bool
     */
     public function insSurveyInfo($memSurveyInfo , $memSurveyImage,$accountInfo)
     {
         $this->write_db->trans_begin();
         try{
             $siteInfo=M_Mysqli_Class('md_lixiang','SiteModel')->getSiteInfoByAttr(['id'=>$memSurveyInfo['id']]);
             unset($memSurveyInfo['id']);
             $memSurveyInfo['location'] = $siteInfo['longitude'];
             $InfoReturnId = M_Mysqli_Class('md_survey','SurveyInfoModel')->addSurveyInfo($memSurveyInfo);
//             var_dump($addInfoId);die;
             if($InfoReturnId < 1){
                 throw new \Exception('勘测信息表失败');
             }
             for($i = 0;$i<count($memSurveyImage);$i++){
                 $memSurveyImage[$i]['image_title']=$siteInfo['site_name'];
                 $memSurveyImage[$i]['attribute_id']=$InfoReturnId;
             }
             $imagesRows = M_Mysqli_Class('md_lixiang','PictureModel')->bashSaveImage($memSurveyImage);
             if($imagesRows < 1){
                 throw new \Exception('图片表失败');
             }
             if($accountInfo!=null){
                 $accountRows = M_Mysqli_Class('md_lixiang','StorageAccountModel')->addBashMate($accountInfo);
//             var_dump($accountRows);die;
                 if($accountRows < 1){
                     throw new \Exception('耗材表失败');
                 }
             }

             $projectState=M_Mysqli_Class('md_survey','SurveyProjectModel')->updataState(['survey_state'=>1,'audit'=>0],['id'=>$accountInfo['project_id']]);
             if($projectState < 1){
                 throw new \Exception('站点状态失败');
             }
             $this->write_db->trans_commit();
             return true;
         }catch (\Exception  $e){

             //失败回滚,返回false
             $this->write_db->trans_rollback();
             var_dump($e->getMessage());die;
             return false;

         }
     }

     

}
