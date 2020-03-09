<?php
header("content-type:text/html;charset=utf-8");
class UserFixStatusModel extends DB_Model
{
    protected $tables = array(

    );

    public function __construct()
    {
        parent::__construct('md_xiuche', 'md_user_fix_status');
        $this->log->log_debug('md_user_fix_status  model be initialized');
    }


    /**
     * 根据条件查找修哥对应状态信息
     * @params $parames  查找修哥状态条件
     * @return  data
     */
    public function selectFixStatusData($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }


    /**
     * 根据条件修改修哥状态
     * @param $data     修哥id或者其他唯一识别参数
     * @param $where    需要修改的修哥参数
     * @return bool     返回类型
     */
    public function alterFixWorkerStatus($data,$where){
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 添加用户
     */
    public function addFixUser($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d,H:i:s',$data['create_time']);
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }














}
