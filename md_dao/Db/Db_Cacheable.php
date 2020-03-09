<?php
abstract class Db_Cacheable {
    protected $isUseCache = USE_CACHE;
    private $dbname;
    private $table;
    protected $tablename;
    protected $dbDriver;
    protected $currentTableName;
    protected $userId = 0;

    public function __construct($dbname, $table) {
        $this->table = $table;
        $this->dbname = $dbname;
        $this->dbDriver = Models::$db_driver;
        $this->tablename = ($this->dbname == NULL) ? $this->table : $this->dbname . '.' . $this->table;
        $this->currentTableName = $this->tablename;
        if ($this->isUseCache) {
            $this->setTableCache();
        }
        // if (isset($this->session)) {
        // $userinfo = $this->session->userdata('userinfo');
        // if ($userinfo) {
        // $this->userId = $userinfo['id'];
        // }
        // }
    }

    protected function setTableCache() {
        if (!$this->cache->get('table_' . $this->tablename)) {
            $this->cache->save('table_' . $this->tablename, json_encode(array(
                    $this->tablename => $this->tables 
            )));
        }
    }

    protected function afterQuery($sql, $binds) {
        $this->removeCacheAuto();
    }

    protected function afterUpdate($data, $wheres) {
        $this->removeCacheAuto();
    }

    protected function afterDelete($wheres) {
        $this->removeCacheAuto();
    }

    protected function afterInsert($data) {

        $this->removeCacheAuto();
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    private function getRemoveCacheTable() {
        $fatherClasses = $this->cache->mget($this->cache->keys('table_*'));
        if (count($fatherClasses) == 0) {
            return NULL;
        }
        $removeTables = array();
        foreach ($fatherClasses as $classes) {
            $classes = json_decode($classes, TRUE);
            foreach ($classes as $class=>$tables) {
                foreach ($tables as $key=>$val) {
                    if ($val == $this->tablename) {
                        $removeTables[] = $class;
                        break;
                    }
                }
            }
        }
        return $removeTables;
    }

    protected function removeCache($removeTables) {
        if ($this->isUseCache && count($removeTables) > 0) {
            foreach ($removeTables as $table) {
                $key = $table . '-';
                $this->cache->delete($key);
                $this->log->log_debug('删除缓存key:' . $key . '*');
            }
        }
    }

    private function removeCacheAuto() {
        if ($this->isUseCache) {
            $classes = $this->cache->get('table_*');
            $removeTables = $this->getRemoveCacheTable();
            $this->removeCache($removeTables);
        }
    }

    protected function setIsUseCache($isUseCache = TRUE) {
        $this->isUseCache = $isUseCache;
    }

    private function getCache($key) {
        $result = $this->cache->get($key);
        if ($result) {
            return json_decode(gzuncompress($result), TRUE);
        }
        return FALSE;
    }

    protected function setCacheKey($tables, $sql, $binds) {
    }

    private function saveCache($key, $value, $ttl) {
        return $this->cache->save($key, gzcompress(json_encode($value), 9), $ttl);
    }

    protected function needReadDb() {
        return Models::getInstance()->needReadDb($this->dbDriver);
    }

    protected function needWriteDb() {
        return Models::getInstance()->needWriteDb($this->dbDriver);
    }

    private function getStrKey($sql, $binds, $type, $isGlobal) {
        $sign = $sql . ' integle ' . $type;
        $bindStr = '';
        if ($binds && is_array($binds)) {
            foreach ($binds as $bind) {
                $bindStr .= ' ' . $bind;
            }
        } else {
            $bindStr .= ' ' . $binds;
        }
        $sign .= $bindStr;
        $key = $this->tablename . '-' . md5($sign);
        // $userinfo = $this->session->userdata('userinfo');
        // if ($userinfo && !$isGlobal) {
        // $key = $key . '-' . $userinfo['id'] . '-' . md5($sign);
        // } else {
        // $key = $key . '-0-' . md5($sign);
        // }
        return array(
                'key' => $key 
        );
    }

    public function getCacheResultArray($sql, $binds = FALSE, $time = 300, $isGlobal = FALSE) {
        if (!$this->isUseCache) {
            // $this->read_db->query($sql, $binds)->result_array();
            // echo $this->read_db->last_query();exit;
            return $this->read_db->query($sql, $binds)->result_array();
        }
        $key = $this->getStrKey($sql, $binds, 'result_array', $isGlobal);
        $result = $this->getCache($key['key']);
        if (!$result) {
            $result = $this->read_db->query($sql, $binds)->result_array();
            $this->saveCache($key['key'], $result, $time);
        } else {
            $this->log->log_trace('sql语句:' . $sql . '使用缓存  , 参数:' . json_encode($binds));
        }
        return $result;
    }

    public function getCacheRowArray($sql, $binds = FALSE, $time = 300, $isGlobal = FALSE) {
        if (!$this->isUseCache) {
            return $this->read_db->query($sql, $binds)->row_array();
        }
        $key = $this->getStrKey($sql, $binds, 'row_array', $isGlobal);
        $result = $this->getCache($key['key']);
        if (!$result) {
            $result = $this->read_db->query($sql, $binds)->row_array();
            $this->saveCache($key['key'], $result, $time);
        } else {
            $this->log->log_trace('sql语句:' . $sql . '使用缓存  , 参数:' . json_encode($binds));
        }
        return $result;
    }

    public function __get($key) {
        $CI = & get_instance();
        if ($key == 'cache') {
            if (!isset($CI->cache)) {
                if (ENVIRONMENT == 'dev') {
                    $this->load->driver('cache', array(
                            'adapter' => 'file' 
                    ));
                } else {
                    $this->load->driver('cache', array(
                            'adapter' => CACHETYPE 
                    ));
                }
            }
        } elseif ($key == 'read_db') {
            $key = $this->dbDriver . '_' . $key;
            if (!isset($CI->$key)) {
                $CI->$key = Models::getInstance()->needReadDb($this->dbDriver);
            }
            return $CI->$key;
        } elseif ($key == 'write_db') {
            $key = $this->dbDriver . '_' . $key;
            if (!isset($CI->$key)) {
                $CI->$key = Models::getInstance()->needWriteDb($this->dbDriver);
            }
            return $CI->$key;
        }
        return $CI->$key;
    }
}
