<?php
class CI_DB_timing extends CI_DB_query_builder {
    public $log;

    public function query($sql, $binds = FALSE, $return_object = TRUE) {
        $startTime = microtime(true);
        $query = parent::query($sql, $binds, $return_object);
        $endTime = microtime(true);
        $exectime = round(($endTime - $startTime) * 1000);
        $this->writeSqlLog($exectime);
        return $query;
    }

    private function writeSqlLog($exectime) {
        $this->log = & get_log();
        $CI = &get_instance();
        $className = get_class($CI);
        $uriString = '';
        foreach ($CI->uri->rsegments as $rsegment) {
            $uriString .= $rsegment . '/';
        }
        
        if ($exectime > $CI->config->item('sql_timeout_log')) {
            $this->log->log_warn('<警告> sql超时 controller方法体:' . $uriString . ',query:' . parent::last_query() . '，SQL执行时长为：' . $exectime . '毫秒');
        } else {
            $this->log->log_sql('<基准测试> #### sql query 基准测试 controller方法体 : ' . $uriString . ', query : ' . parent::last_query() . '，SQL执行时长为:' . $exectime . '毫秒');
        }
    }
}