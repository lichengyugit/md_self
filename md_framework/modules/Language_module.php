<?php
class Language_module {
    private $languages = array();
    private $currentLang;
    private $CI;
    private $log;

    public function __construct() {
        $this->log = & get_log();
        $this->CI = & get_instance();
        $this->languages = include DEFAULT_LANG_PATH . 'language.php';
        $this->currentLang = 'chinese';
    }

    public function getCurrentLanguage() {
        if (!isset($this->currentLang)) {
            $langType = $this->CI->session->userdata('language');
            if ($langType) {
                $this->currentLang = $langType;
            } else {
                $this->currentLang = $this->CI->config->item('language');
            }
        }
        return $this->currentLang;
    }

    public function ajaxLanguage($langFiles) {
        $langType = $this->getCurrentLanguage();
        if (is_array($langFiles)) {
            foreach ($langFiles as $langFile) {
                $viewlangs = array_merge($viewlangs, $this->CI->lang->load($langFile, $langType, TRUE));
            }
        } else {
            $ajaxLangs = $this->CI->lang->load($langFiles, $langType, TRUE);
        }
        return $ajaxLangs;
    }

    public function supportedLanguages() {
        if (!IS_AJAX) {
            $this->log->log_debug('加载支持切换的语言');
            $this->CI->smarty->assign('classlangs', $this->languages);
        }
    }
}