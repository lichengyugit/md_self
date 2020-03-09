<?php
class MemberCardModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_member_card');
        $this->log->log_debug('MemberCardModel  model be initialized');
    }

    /*
     * 查询用户会员等级
     * @parame   user_id 用户id
     */
    public function  getlevel()
    {
        $sql = " SELECT * FROM ".$this->tablename;
        return $this->getCacheRowArray($sql);
    }

}
