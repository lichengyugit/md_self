<?php
class UserWalletModel extends DB_Model {
    protected $tables = array(
            'user' => 'md_lixiang.md_user'
    );

    public function __construct() {
        parent::__construct('md_xiuche', 'md_user_wallet');
        $this->log->log_debug('UserWalletModel  model be initialized');
    }
    
    
    
    /**
     * 获取单条用户钱包信息
     */
    public function getWalletByUserId($user_id){
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id = ?";
        return $this->getCacheRowArray($sql,array($user_id));
    }

    /**
     * 修改钱包
     */
    // public function updateWalletByAttr($data,$where){
    //     $update=$this->update($data, $wheres);
    //     return $update;
    // }

    
    /**
     * 添加用户钱包
     */
    public function addWallet($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }


    /**
     *  钱包余额变动
     */
    public function updateWalletBalance($parames,$user_id)
    {
        $wallet = $this->getWalletByUserId($user_id);       
        if ($wallet<1) {
            $data['user_id'] = $user_id;
            $data['create_time'] = time();
            $data['create_date'] = date('Y-m-d H:i:s');
            $insert=$this->insert($data);
            return $insert;
        }else{
            $set ="";
            $parames['update_time'] = time();
            $parames['update_date'] = "'".date("Y-m-d H:i:s")."'";
            foreach($parames as $k=>$v){
                $set.= $k." = ".$v.", ";
            }
            $set = trim($set, ", ");
            $sql = "UPDATE ".$this->tablename." SET ".$set." WHERE user_id= ".$user_id;
            $this->write_db->query($sql);
             return $this->write_db->affected_rows();
        }
    }

    /**
     * @param $parames
     * @param $user_id
     * @param $actionBalance
     * @return mixed
     */
    public function actionWalletBalance($parames, $user_id, $actionBalance){

            $wallet = $this->getWalletByUserId($user_id);
            if ($wallet<1) {
                if(array_key_exists('balance', $parames)){
                    $data['balance'] = $parames['balance'];
                }
                if (array_key_exists('giving_balance', $parames)) {
                    $data['giving_balance'] = $parames['giving_balance'];
                }
                if(array_key_exists('total_balance', $parames)){
                    $data['total_balance'] = $parames['total_balance'];
                }
                $data['user_id'] = $user_id;
                $data['create_time'] = time();
                $data['create_date'] = date('Y-m-d H:i:s');
                $insert=$this->insert($data);
                return $insert;
            }else{
                $set ="";
                if(array_key_exists('balance', $parames)){
                    $parames['balance'] = 'balance '.$actionBalance.$parames['balance'];
                }
                if (array_key_exists('giving_balance', $parames)) {
                    $parames['giving_balance'] = 'giving_balance '.$actionBalance.$parames['giving_balance'];
                }
                if(array_key_exists('total_balance', $parames)){
                    $parames['total_balance'] = 'total_balance '.$actionBalance.$parames['total_balance'];
                }
                $parames['update_time'] = time();
                $parames['update_date'] = "'".date("Y-m-d H:i:s")."'";
                foreach($parames as $k=>$v){
                    $set.= $k." = ".$v.", ";
                }
                $set = trim($set, ", ");
                $sql = "UPDATE ".$this->tablename." SET ".$set." WHERE user_id= ".$user_id;
                $this->read_db->query($sql);
                 return $this->read_db->affected_rows();
            }
        }
        
   /**
    * 用户钱包更改(不更改id)
    */     
     public function actionUserWalletByAttr($data){
         $wheres=array('user_id'=>$data['id']);
         unset($data['id']);
         $data['update_time']=time();
         $data['update_date']=date("Y-m-d H:i:s",time());
         $update=$this->update($data, $wheres);
         if($update){
             return $update;
         }
         else{
             return false;
         }
     } 

     public function addUserWalletLog($data)
    {
        $data['create_time'] = time();
        $data['create_date'] = date('Y-m-d H:i:s');
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * 用户钱包更改(更改id)
     */
    public function UpdateUserWalletId($data){
        $wheres=array('user_id'=>$data['user_id']);
        $data['user_id']=$data['UuserId'];
        unset($data['UuserId']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    /*
     * 获取用户钱包
     * */
    public function getUserWalletData($limit='',$parames='')
    {
        $where='';
        if(isset($parames['user_flag'])) $where.=" AND U.user_flag=".$parames['user_flag'];
        empty($parames['identification']) || $where .=" AND U.identification=".$parames['identification'];
        if(!empty($parames['select'])) {
                 if (preg_match("/^1[345678]{1}\d{9}$/", $parames['select'])) {                   //验证是否手机号
                     $where .= " AND U.mobile=" . trim($parames['select']);
                 } elseif (preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $parames['select'])) {     //验证是否身份证
                     $where .= " AND U.card_number=" . '"' . trim($parames['select']) . '"';
                 } else {
                     if($parames['user_flag']==0){
                         $where .= " AND U.name LIKE " . "'%" . trim($parames['select']) . "%'";
                     }elseif($parames['user_flag']==3){
                         $where .= " AND U.user_name LIKE " . "'%" . trim($parames['select']) . "%'";
                     }

                 }
        }
        if(!empty($parames['create_time'])){
            $str=preg_split('/\s-\s/',$parames['create_time']);
            $strTime=date('Y-m-d H:i:s',strtotime($str[0]));
            $endTime=date('Y-m-d H:i:s',strtotime($str[1]));
            $where.=" AND WU.create_date>=".'"' . $strTime . '"'." AND WU.create_date<".'"' . $endTime . '"' ;
        }
        $sql="SELECT WU.*,U.name,U.user_name,U.mobile,U.card_number FROM ".$this->tablename." AS WU LEFT JOIN ".$this->tables['user']." AS U ON WU.user_id=U.id WHERE U.status=0 ".$where." ORDER BY WU.balance DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }
}
?>
