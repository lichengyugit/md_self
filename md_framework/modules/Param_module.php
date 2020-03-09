<?php
class Param_module {

    public function __construct() {
        // 将CI的超级对象赋给CI，注意是引用形式
        $this->CI = & get_instance();
    
        $this->CI->log->log_debug('Common_File class be initialized');
    }
    
    /**
     * 通用 - 获取修改状态的参数
     * 
     * @access public
     * @param array $statusArr status的枚举数组 (常用 0关闭 1开启 2已删除)
     * @param string $idTag 获取id值的下标
     * @param string $statusTag 获取status值的下标
     * @param string $errorLang 提示错误的语言包
     * @return mixed
     */
    public function getParamForUpdateStatus($statusArr = array(0, 1 ,2), $idTag = 'id', $statusTag = 'status', $errorLang = 'args_illegal') {
        $CI = & get_instance();
        $id = param($idTag);
        $status = param($statusTag);
        
        // 验证参数合法性
        if (!$id || !is_int($id) || !in_array($status, $statusArr)) {
            if (IS_AJAX) {
                F()->Return_module->ajaxReturn(NULL, 'cuowu', 0);
                return FALSE;
            } else {
                $this->CI->log->log_info('cuowu');
                return show_error('cuowu');
            }
        } else {
            $this->CI->log->log_debug('获取了' . $idTag . '、' . $statusTag . '参数并验证通过');
            return array(
                $id, 
                $status 
            );
        }
    }

    /**
     * 获取一个参数
     * 
     * @param string $name 参数名称下标
     * @param string $errorLang 提示错误的语言包
     * @param boolean $strictMode 严格模式
     * @return mixed
     */
    public function getParam($name, $type = 'int', $errorLang = NULL, $strictMode = TRUE) {
        $CI = & get_instance();
        $param = param($name, $type);
        if($strictMode) {
            $result = empty($param);
        } else {
            $result = ($param === FALSE);
        }
        if ($result) {
            if(!$errorLang) {
                $errorLang = $name . 'args_illegal';
            } else {
                $errorLang = 'cuowu';
            }
            if (IS_AJAX) {
                F()->Return_module->ajaxReturn(NULL, $errorLang, 0);
                return FALSE;
            } else {
                $this->CI->log->log_info($errorLang);
                return show_error($errorLang);
            }
        }
        $this->CI->log->log_debug('获取参数$' . $name . '=' . $param);
        return $param;
    }

    /**
     * 获取分页需要的limit语句和分页栏html代码
     * 
     * @access public
     * @param int $total
     * @param int $pagenum
     * @param mixed $ajaxJsFn ajax分页的js函数，默认不是ajax分页
     * @param boolean $returnPages 是否返回生成的分页html代码
     * @return string
     */
    public function getLimitAndPagination($total, $pagenum = NULL, $ajaxJsFn = FALSE, $returnPages = FALSE) {
        $CI = & get_instance();
        if (!$pagenum) {
            $pagenum = !empty($GLOBALS['config']['default_pagenum']) ? $GLOBALS['config']['default_pagenum'] : 20;
        }
        
        $CI->load->library('Common_Pagination', array(
            'totalNum' => $total, 
            'pageNum' => $pagenum 
        ));
        $page = get($CI->common_pagination->pageTag, 'int', 1);
        $pages = $CI->common_pagination->show($ajaxJsFn);
        $totalPages = $CI->common_pagination->totalPages;
        if (!$returnPages) {
            $CI->smarty->assign('pagesTag', $total > $pagenum);
            $CI->smarty->assign('pages', $pages);
            $CI->smarty->assign('page', $page);
            $CI->smarty->assign('pagenum', $pagenum);
            $CI->smarty->assign('totalPages', $totalPages);
        }
        $this->CI->log->log_debug('加载分页类并将返回的分页HTML代码变量$pages分配到view层');
        
        $CI->load->library('Common_Sql');
        $limit = $CI->common_sql->createLimit($page, $pagenum);
        
        $data = $returnPages ? array(
            $pages, 
            $limit,
            $page
        ) : $limit;
        return $data;
    }

    /**
     * 获取上传文件数组 并记录详细日志
     * 
     * @access public
     * @return mixed - array | string
     */
    public function getUploadFiles() {
        $CI = & get_instance();
        $userinfo = (!isset($CI->userinfo) || $CI->userinfo === FALSE) ? '0:System' : ($CI->userinfo->id . ':' . $CI->userinfo->email);
        if (!empty($_FILES)) {
            $files = array();
            foreach ($_FILES as $key=>$val) {
                $this->CI->log->log_debug('User:' . $userinfo . '上传了' . ($val['size'] / 1024) . 'KB的文件:' . $val['name']);
                $files[ ] = $val;
            }
            return $files;
        }
        return '';
    }
}