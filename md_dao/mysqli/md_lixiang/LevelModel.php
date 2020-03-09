<?php
class LevelModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_level');
        $this->log->log_debug('LevelModel  model be initialized');
    }

    /*
     * 查询用户会员等级
     * @parame   user_id 用户id
     */
    public function  getlevel($parame)
    {
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id = ? ";
        return $this->getCacheRowArray($sql,array($parame));
    }

}
