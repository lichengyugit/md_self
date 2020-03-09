<?php
class UserModel extends DB_Model {
   protected $tables = array(
         'company'=>'md_lixiang.md_company'
    );

    public function __construct() {
        parent::__construct('md_lixiang','md_user');
        $this->log->log_debug('UserModel  model be initialized');
        $this->load->library('session');
    }

    /**
     * 根据属性获取单用户信息
     */
    public function getUserInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * [getSearchUser 搜索用户]
     * @param  [type] $status [description]
     * @param  [type] $search   [description]
     * @return [type]         [description]
     */
    public function getSearchUser($condition,$search,$page=1,$pageSize=10){
        $where="";
        foreach ($condition as $k=>$v){
            $where.=" AND `".$k."` = ".$v;
        }
        if($search){
            $where.=" AND (`username` like '%".$search."%' OR `name` like '%".$search."%')";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1 = 1".$where." limit ?,?";
        return $this->getCacheResultArray($sql,array(
                ($page - 1) * $pageSize,
                $pageSize
        ));
    }

    /**
     * [getConditionUser 根据条件获取用户群体]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function getConditionUser($condition){
        $where="";
        foreach ($condition as $k=>$v){
            $where.=" AND `".$k."` = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1 = 1".$where;
        return $this->getCacheResultArray($sql);
    }

    /**
     * [getConditionXg 根据条件获取修哥 并分页]
     * @param  [type] $condition [description]
     * @param  [type] $page      [description]
     * @param  [type] $pageSize  [description]
     * @return [type]            [description]
     */
    public function getConditionXg($condition,$page=1,$pageSize=10){
        $where="";
        foreach ($condition as $k=>$v){
            $where.=" AND `".$k."` = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1 = 1".$where." limit ?,?";
        return $this->getCacheResultArray($sql,array(
                ($page - 1) * $pageSize,
                $pageSize
        ));
    }

    /**
     * [countAgentXg 获取商家下的修哥数量]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function countAgentXg($agent_id){
        $sql = " SELECT count(`id`) AS c FROM ".$this->tablename." WHERE `parent_id` = ? AND `role_flag` = ? AND `status` = ?";
        return $this->getCacheRowArray($sql,array(
                $agent_id,
                3,
                1
        ))['c'];
    }

    /**
     * [getUsersInfoByAttr 获取多用户信息]
     * @param  [type] $parames [description]
     * @return [type]          [description]
     */
    public function getUsersInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            if(is_array($v)){
                $v=implode(",", $v);
                $where.= " AND ".$k." in (".$v.")";
            }else{
               $where.= " AND ".$k." = '".$v."'";
            }
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 验证用户名密码
     */
    public function checkUserByNameAndPass($username,$passWord){
        $sql = " SELECT COUNT(1) as c FROM ".$this->tablename." WHERE username = ? AND passwd = ?";
        return $this->getCacheRowArray($sql,array(
                $username,
                $passWord
        ))['c'];
    }
    
    /**
     * 验证手机号用户是否存在
     */
    public function checkUserMobile($mobile,$user_flag=0){
    $sql = " SELECT id,COUNT(1) as c FROM ".$this->tablename." WHERE mobile=? AND user_flag =? ";
       $row = $this->getCacheRowArray($sql,array(
                $mobile,
                $user_flag
        ));

        if(!$row['c']){
            unset($row['id']);
        }
        return $row;
    }

    /**
     * [countXgNumbers 计算每个店铺下修哥人数]
     * @param  [type] $username  [description]
     * @param  [type] $role_flag [description]
     * @return [type]            [description]
     */
    // public function countXgNumbers($shop_id){
    //     $sql = " SELECT COUNT(1) as c FROM ".$this->tablename." WHERE parent_id=? AND status =? AND role_flag =? ";
    //     return $this->getCacheRowArray($sql,array(
    //             $shop_id,
    //             1,
    //             3
    //     ))['c'];
    // }
    
    /**
     * 根据类型获得所有用户
     */
    public function getAllUser($page, $pageSize,$role_flag){
        $sql = " SELECT * FROM ".$this->tablename." AS p WHERE p.status <2 AND p.role_flag=? ORDER BY id ASC LIMIT ?,?";
        return $this->getCacheResultArray($sql,array(
                $role_flag,
                ($page - 1) * $pageSize,
                $pageSize
        ));
    }
    

    
    /**
     * 添加用户
     */
    public function addUser($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d,H:i:s',$data['create_time']);
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    
    /*
     * 批量增加用户
     */
    public function addUserBatch($data){
        return $this->insertBatch($data);
    }


    /**
     * 根据ID修改用户信息(不更改id号)
     */
    public function updateUser($data){
        //$data['update_time']=time();
        //$data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres=array('id'=>$data['id']);
        unset($data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    /**
     * 根据ID获取单个用户是否属于集团和集团id
     */
    public function getUserCompany($id){
        $sql=" SELECT user_type,attr_id FROM ".$this->tablename." WHERE id=? ";
        $row=$this->getCacheRowArray($sql,$id);
        return $row;
    }
    
    /**
     * 修改用户信息
     */
    public function updateUserByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update >0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 获取所有用户
     */
    public function getAllUsers(){
        $sql= " SELECT * FROM ".$this->tablename." WHERE status <> 2 ";
        $row=$this->getCacheResultArray($sql);
        return $row;
    }
    
    /**
     * 根据条件获取用户数量
     */
    public function getUserByAttr($parames){
        $where="";
        if(array_key_exists('or_user_flag',$parames)){
            $data=$parames['or_user_flag'];
            unset($parames['or_user_flag']);
            foreach ($parames as $k=>$v){
                if($k=='user_flag'){
                    $where.='AND (user_flag='.$v.' or user_flag='.$data.')';
                }else{
                    $where.=" AND ".$k." = '".$v."'";     
                }
            }
            $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
            return $this->getCacheRowArray($sql,$where)['c'];
        }else{
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = '".$v."'";
            }
            $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
            return $this->getCacheRowArray($sql,$where)['c'];
        }
    }
    
    /**
     * 根据条件获得所有用户列表
     */
    public function getAllUserByAttr($limit,$parames){
        $where="";
        if(array_key_exists('or_user_flag',$parames)){
            $data=$parames['or_user_flag'];
            unset($parames['or_user_flag']);
            foreach ($parames as $k=>$v){
                if($k=='user_flag'){
                    $where.='AND (U.user_flag='.$v.' or U.user_flag='.$data.')';
                }else{
                    $where.=" AND U.".$k." = '".$v."'";
                }
            }
            //$sql = " SELECT U.*,C.name AS company_name FROM ".$this->tablename." AS U LEFT JOIN ".$this->tables['company']." AS C ON U.attr_id=C.id WHERE U.status < 2 ".$where." ORDER BY U.id DESC "." LIMIT ".$limit;
            $sql = " SELECT *  FROM ".$this->tablename."  WHERE `status` < 2 ".$where." ORDER BY U.id DESC "." LIMIT ".$limit;
            return $this->getCacheResultArray($sql,$where);
        }else{
            foreach ($parames as $k=>$v){
                $where.=" AND ".$k." = ".$v;
            }
            $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC "." LIMIT ".$limit;
            return $this->getCacheResultArray($sql,$where);
        }
    }

    /**
     * 根据条件获取北海用户数量
     */
    public function getBeihaiUserByAttr($parames){
        $where="";
        if(isset($parames['user_flag'])){
            unset($parames['user_flag']);
            $type=1;
        }
        foreach ($parames as $k=>$v){
            $where.=" AND `".$k."` = ".$v;
        }
        if(isset($type)){
            $where.=" AND `user_flag` in(0,1)";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
    
    /**
     * 根据条件获得所有北海用户列表
     */
    public function getAllBeihaiUserByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND `".$k."` = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 连表获取用户名、身份证号码和钱包余额
     */
    public function getUserNameAndIdNumber($userId){
        $sql="select u.*,i.card_number,w.balance from ".$this->tablename." as u left join md_idcard as i on u.id=i.user_id left join md_user_wallet as w on u.id=w.user_id where u.id= ? ";
        $row=$this->getCacheRowArray($sql,array($userId));
        return $row;
    }

    /*
     * 判断对应身份账号是否存在
     */
    public function judgeUser($userFlag,$mobile){
        $sql='SELECT * FROM '.$this->tablename.' WHERE user_flag='.$userFlag.' AND mobile='.$mobile;
        $result=$this->getCacheResultArray($sql);
        if($result){
            return false;
        }else{
            return true;
        }

    }

    /**
     * index页面获取列表数据
     */
    public function indexgetAllUserByAttr($limit='',$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['user']='用户列表';
        return $arr;
    }
    

    /**
     * 根据ID修改用户信息(更改id号)
     */
    public function updateUserId($data){
        //$data['update_time']=time();
        //$data['update_date']=date('Y-m-d,H:i:s',$data['update_time']);
        $wheres=array('id'=>$data['id']);
        $data['id']=$data['uId'];
        unset($data['uId']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }


    /**
     * 连表模糊查询用户数据
     */
    public function tableQuery($data,$LIMIT=''){
        $like=$data['select'];
        unset($data['select']);
        if(!empty($data['time'])){
            $str=preg_split('/\s-\s/',$data['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $str=' AND U.create_time>'.$strTime.' AND U.create_time<'.$endTime;
        }else{
            $str='';
        }
        if(array_key_exists('is_deposit',$data)){
            $str.=' AND U.is_deposit='.$data['is_deposit'];
        }
        if(array_key_exists('is_vip',$data)){
            $str.=' AND U.is_vip='.$data['is_vip'];
        }
        if(array_key_exists('id_card',$data)){
            $str.=' AND U.id_card='.$data['id_card'];
        }
        if(array_key_exists('user_flag',$data)){
            $str.=' AND U.user_flag='.$data['user_flag'];
        }
        if(array_key_exists('compay_id',$data)){
            $str.=' AND U.user_type='.$data['compay_id'];
        }
        if(array_key_exists('attr_id',$data)){
            $str.=' AND U.attr_id='.$data['attr_id'];
        }
        if(array_key_exists('merchant_user_id',$data)){
            $str.=' AND U.merchant_user_id='.$data['merchant_user_id'];
        }
        $sql="SELECT U.*,C.name AS company_name FROM md_user AS U LEFT JOIN ".$this->tables['company']." AS C ON U.attr_id=C.id WHERE (U.user_flag=0 or U.user_flag=1) AND U.status < 2 AND  U.identification=1 ".$str." AND CONCAT(IFNULL(U.name,'"."'),IFNULL(U.mobile,''),IFNULL(U.card_number,'')) LIKE '%".trim($like)."%'"." ORDER BY U.id DESC ".$LIMIT;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 手机后台 检索用户数据
     */
    public function phoneQuery($data,$LIMIT=''){
        $sql="SELECT id,nick_name,name,card_number,mobile,user_type,is_vip,id_card,identification,status,create_time,card_type FROM md_user WHERE (user_flag=0 or user_flag=1) AND status < 2 AND  identification=1  AND CONCAT(IFNULL(name,'"."'),IFNULL(mobile,''),IFNULL(card_number,'')) LIKE '%".$data."%'"." ORDER BY id DESC ".$LIMIT;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 根据搜索条件获取用户数据数量
     */
    public function getSearchCountBatteryByAttr($data){
        $like=$data['select'];
        unset($data['select']);
        if(!empty($data['time'])){
            $str=preg_split('/\s-\s/',$data['time']);
            $strTime=strtotime($str[0]);
            $endTime=strtotime($str[1]);
            $str=' AND create_time>'.$strTime.' AND create_time<'.$endTime;
        }else{
            $str='';
        }
        if(array_key_exists('is_deposit',$data)){
            $str.=' AND is_deposit='.$data['is_deposit'];
        }
        if(array_key_exists('is_vip',$data)){
            $str.=' AND is_vip='.$data['is_vip'];
        }
        if(array_key_exists('id_card',$data)){
            $str.=' AND id_card='.$data['id_card'];
        }
        if(array_key_exists('user_flag',$data)){
            $str.=' AND user_flag='.$data['user_flag'];
        }
        if(array_key_exists('compay_id',$data)){
            $str.=' AND user_type='.$data['compay_id'];
        }
        if(array_key_exists('attr_id',$data)){
            $str.=' AND attr_id='.$data['attr_id'];
        }
        if(array_key_exists('merchant_user_id',$data)){
            $str.=' AND merchant_user_id='.$data['merchant_user_id'];
        }
        $sql="SELECT count(1) as c FROM md_user WHERE (user_flag=0 or user_flag=1) AND status < 2 AND identification=1 ".$str." AND CONCAT(IFNULL(name,'"."'),IFNULL(mobile,''),IFNULL(card_number,'')) LIKE '%".trim($like)."%'";
        return $this->getCacheRowArray($sql)['c'];
    }
       

    /**
     * 检查用户是否存在
     */
    public function inspectUser($data){
        $result="SELECT count(1) as c from ".$this->tablename."  WHERE status < 2 AND mobile='".$data['mobile']."' AND card_number='".$data['card_number']."'"." AND attr_id='".$data['attr_id']."'";
        return $this->getCacheRowArray($result)['c'];
    }


    /**
     *  检索工程队用户
     */
    public function selectInsider($data,$limit=''){
        $sql="SELECT id,user_name,name,card_number,mobile,user_type,is_vip,id_card,identification,status,create_time,card_type FROM md_user WHERE user_flag=7 AND status < 2  AND CONCAT(IFNULL(name,'"."'),IFNULL(mobile,''),IFNULL(card_number,''),IFNULL(user_name,'')) LIKE '%".$data."%'"." ORDER BY id DESC ".$limit;
        return $this->getCacheResultArray($sql);
    }








     public function getTeamUser($limit='',$parames)
     {
         $where='';
         if(isset($parames['user_flag'])){
             $where.=' AND user_flag='.$parames['user_flag'];
         }
         if(isset($parames['identification'])){
             $where.=' AND identification='.$parames['identification'];
         }
         if(isset($parames['id_card']) && $parames['id_card']!=''){
             $where.=' AND id_card='.$parames['id_card'];
         }
         if(isset($parames['is_deposit']) && $parames['is_deposit']!=''){
             $where.=' AND is_deposit='.$parames['is_deposit'];
         }
         $where.=$parames['where'];
         if(!empty($parames['input_data'])){
             if(preg_match("/^1[345678]{1}\d{9}$/",trim($parames['input_data']))){

                 $where .= " AND mobile =". trim($parames['input_data']) ;
             }elseif(preg_match("/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/",trim($parames['input_data']))){
                 $where .=" AND card_number ="."'". trim($parames['input_data']) ."'";;
             }else{
                 $where .= " AND user_name LIKE "."'%". trim($parames['input_data']) ."%'" ;
             }

         }
         if(!empty($parames['create_time'])){
             $str=preg_split('/\s-\s/',$parames['create_time']);
             $strTime=strtotime($str[0]);
             $endTime=strtotime($str[1]);
             $where .=' AND create_time>='.$strTime.' AND create_time<='.$endTime;
         }

         $sql = "SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." ORDER BY id DESC ".$limit;
        return  $this->getCacheResultArray($sql);
     }
































}
?>






