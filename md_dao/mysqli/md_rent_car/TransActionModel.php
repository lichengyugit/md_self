<?php
header("content-type:text/html;charset=utf-8");
class TransActionModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_site');
        $this->log->log_debug('SurveyInfoModel  model be initialized');
    }


    /**
     * 商家入驻请求
     * @params $arr   入驻商家id
     * @return  bool
     */
    public function BusinessAudit($params,$invite){
        $this->write_db->trans_begin();
       try{
            //商家审核信息
            $register=M_Mysqli_Class('md_lixiang','RegisterModel')->getRegByAttr($params);

            $userData=[
                'user_flag'=>4,
                'user_name'=>$register['name'],
                'password'=>$register['password'],
                'nick_name'=>urlencode($register['name']),
                'mobile'=>$register['mobile'],
                'invite_code'=>$invite,
                'identification'=>1,
                'identification_time'=>time(),
                'user_type'=>0
            ];
            //绑定商家用户信息
            $user=M_Mysqli_Class('md_lixiang','UserModel')->addUser($userData);
           //如果添加失败抛出异常
           if($user < 1){
               throw new \Exception('绑定商家用户信息失败');
           }

            $userWalletData=[
                'user_id'=>$user            
            ];
            //绑定用户钱包表信息
            $userWallet=M_Mysqli_Class('md_lixiang','UserWalletModel')->addWallet($userWalletData);
           //如果添加失败抛出异常
           if($userWallet < 1){
               throw new \Exception('绑定用户钱包表信息失败');
           }

            $IdCardData=[
                'user_id'=>$user,
                'name'=>$register['name'],
                'card_number'=>$register['card_number']
            ];
            //绑定用户身份信息
            $idCard=M_Mysqli_Class('md_lixiang','IdCardModel')->addIdCard($IdCardData);
           //如果添加失败抛出异常
           if($idCard < 1 || $idCard == false){
               throw new \Exception('绑定用户身份信息失败');
           }

            //判断用户是否使用邀请码
            if(!empty($register['invite_code'])){
                $userInvite=M_Mysqli_Class('md_lixiang','UserModel')->getUserInfoByAttr(['invite_code'=>$register['invite_code']]);
                $inviteCodeData=[
                    'user_id'=>$user,
                    'user_type'=>4,
                    'invite_code'=>$register['invite_code'],
                    'attr_id'=>$userInvite['id'],
                    'attr_type'=>$userInvite['user_flag']
                ];
                $inviteCode=M_Mysqli_Class('md_lixiang','InviteCodeModel')->saveInviteCode($inviteCodeData);
               //如果添加失败抛出异常
               if($inviteCode < 1){
                   throw new \Exception('绑定邀请码信息失败');
               }

                $PosInterface=F()->Gaode_module->addTransFormCoordinate($register['location']);
                $position = explode(',',$PosInterface['geocodes'][0]['location']); 
                $MerchantData=[
                    'name'=>$register['shop_name'],
                    'mobile'=>$register['mobile'],
                    'location'=>$register['location'],
                    'attr_id'=>$user,
                    'longitude'=>$position[0],
                    'latitude'=>$position[1],
                    'salesman_id'=>$userInvite['id']
                ];
                $Merchant=M_Mysqli_Class('md_lixiang','MerchantModel')->saveMerchant($MerchantData);
               //如果添加失败抛出异常
               if($Merchant < 1){
                   throw new \Exception('绑定商家信息失败');
               }
            }else{
                $PosInterface=F()->Gaode_module->addTransFormCoordinate($register['location']);
                $position = explode(',',$PosInterface['geocodes'][0]['location']); 
                $MerchantData=[
                    'name'=>$register['shop_name'],
                    'mobile'=>$register['mobile'],
                    'location'=>$register['location'],
                    'longitude'=>$position[0],
                    'latitude'=>$position[1],
                    'attr_id'=>$user
                ];
                $Merchant=M_Mysqli_Class('md_lixiang','MerchantModel')->saveMerchant($MerchantData);
               //如果添加失败抛出异常
               if($Merchant < 1){
                   throw new \Exception('绑定商家信息失败');
               }
            }

            $registerData=[
                'id'=>$register['id'],
                'is_pass'=>1
            ];
            //如果审核通过 更改状态
            $result=M_Mysqli_Class('md_lixiang','RegisterModel')->updateRegByAttr($registerData);
            //如果添加失败抛出异常
           if($result == false){
               throw new \Exception('更改入驻信息状态失败');
           }
            $url = "http://weiapi.xianghuandian.com/allmoneyin?action=actionMoneyIn&user_id=".$user;
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $output = curl_exec($ch);
            curl_close($ch);
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
