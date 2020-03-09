<?php
class CI_Common_Was_Report {
    var $CI;
    var $log;
    var $owaspCn = array(
            'A1' => 'SQL注入',
            'A2' => '失效的身份认证和会话管理',
            'A3' => '跨站脚本(XSS)',
            'A4' => '不安全的直接引用对象',
            'A5' => '安全配置错误',
            'A6' => '敏感信息泄露',
            'A7' => '功能级访问控制缺失',
            'A8' => '跨站请求伪造(CSRF)',
            'A9' => '使用含有已知漏洞的组件',
            'A10' => '未验证的重定向和转发' 
    );
    var $owaspEn = array(
            'A1' => 'SQL Injection',
            'A2' => 'Broken Authentication and Session Management',
            'A3' => 'Cross-Site Scripting (XSS)',
            'A4' => 'Insecure Direct Object References',
            'A5' => 'Security Misconfiguration',
            'A6' => 'Sensitive Data Exposure',
            'A7' => 'Missing Function Level Access Control',
            'A8' => 'Cross-Site Request Forgery (CSRF)',
            'A9' => 'Using Components with Known Vulnerabilities',
            'A10' => 'Unvalidated Redirects and Forwards' 
    );

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_log();
        $this->log->log_debug('CI_Common_was_report class be initialized');
    }

    public function getWebResult($deeppath, $xmlFilename, $lang) {
        $xml = file_get_contents(XML_PATH . '/was/' . $deeppath . DS . $xmlFilename);
        $result = simplexml_load_string($xml, null, LIBXML_NOCDATA);
        $vulnList = $result->RESULTS->VULNERABILITY_LIST->VULNERABILITY;
        $infoList = $result->RESULTS->INFORMATION_GATHERED_LIST->INFORMATION_GATHERED;
        
        $glossList = $result->GLOSSARY->QID_LIST;
        
        $vulnCnInfoModel = M_Mysqli_Class('cro_scan', 'VulnsCnInfoModel');
        $webResult['url'] = $result->APPENDIX->WEBAPP->URL->__toString();
        $webResult['os'] = $result->APPENDIX->WEBAPP->OPERATING_SYSTEM->__toString();
        $webResult['start_date'] = $this->_toTimeZone(strtotime($result->APPENDIX->SCAN_LIST->SCAN->START_DATE->__toString()));
        $webResult['end_date'] = $this->_toTimeZone(strtotime($result->APPENDIX->SCAN_LIST->SCAN->END_DATE->__toString()));
        for ($i = 1; $i <= 10; $i++) {
            $webResult['owasps']['A' . $i] = 0;
        }
        for ($i = 0; $i < count($vulnList); $i++) {
            $vuln = $vulnList[$i];
            $qid = $vuln->QID->__toString();
            $vulnResult = $vulnCnInfoModel->getVnlnInfoByQid($qid);
            if (!$vulnResult) {
                break;
            }
            $tempPayLoads = array();
            for ($j = $i + 1; $j < count($vulnList); $j++) {
                $tempVuln = $vulnList[$j];
                $tempQid = $tempVuln->QID->__toString();
                if ($tempQid == $qid) {
                    if (count($tempPayLoads) > 0) {
                        $tempPayLoads = array_merge($tempPayLoads, $this->_getPayloadsArray($tempVuln->PAYLOADS));
                    } else {
                        $tempPayLoads = $this->_getPayloadsArray($tempVuln->PAYLOADS);
                    }
                    unset($vulnList[$j]);
                    $j--;
                }
            }
            $vulnResult['title'] = strip_tags($vulnResult['title']);
            if (count($tempPayLoads) > 0) {
                $vulnResult['payloads'] = array_merge($this->_getPayloadsArray($vuln->PAYLOADS), $tempPayLoads);
            } else {
                $vulnResult['payloads'] = $this->_getPayloadsArray($vuln->PAYLOADS);
            }
            
            // $temp = $vulnResult['payloads'];
            // for ($m = 0; $m < count($temp); $m++) {
            // for ($n = $m + 1; $n < count($temp) - 1; $n++) {
            // if ($temp[$n]['url'] == $temp[$m]['url'] && $temp[$n]['contents'] == $temp[$m]['contents'] && $temp[$n]['method'] == $temp[$m]['method']) {
            // unset($temp[$n]);
            // }
            // }
            // }
            // $vulnResult['payloads'] = $temp;
            
            $vulnResult['gloss'] = $this->_getGlossArray($qid, $glossList);
            $vulnResult['gloss']['owasp_cn'] = $this->_getOwasp($vulnResult['gloss']['owasp'], $lang);
            if ($vulnResult['gloss']['owasp'] != null) {
                $owasps = explode(',', $vulnResult['gloss']['owasp']);
                foreach ($owasps as $owasp) {
                    $webResult['owasps'][$owasp]++;
                }
            }
            
            // $vulnResults[] = $vulnResult;
            $webResult['vulns'][$vulnResult['severity_level']][] = $vulnResult;
        }
        $webResult['vulns_count'] = count($vulnList);
        $webResult['owasps_count'] = 0;
        $infoResults = array();
        $webResult['infos_count'] = 0;
        if (isset($infoList)) {
            foreach ($infoList as $info) {
                $qid = $info->QID;
                $infoResult = $vulnCnInfoModel->getVnlnInfoByQid($qid);
                if ($infoResult) {
                    $infoResult['title'] = strip_tags($infoResult['title']);
                    $infoResult['data'] = $this->convertToUTF8(base64_decode($info->DATA));
                    $infoResult['gloss'] = $this->_getGlossArray($qid, $glossList);
                    $infoResult['gloss']['owasp_cn'] = $this->_getOwasp($infoResult['gloss']['owasp'], $lang);
                    if ($infoResult['gloss']['owasp'] != null) {
                        $owasps = explode(',', $infoResult['gloss']['owasp']);
                        foreach ($owasps as $owasp) {
                            $webResult['owasps'][$owasp]++;
                        }
                    }
                    $infoResults[$infoResult['severity_level']][] = $infoResult;
                    $webResult['infos_count']++;
                }
            }
        }
        $webResult['infos'] = $infoResults;
        for ($i = 1; $i <= 10; $i++) {
            $webResult['owasps_count'] += $webResult['owasps']['A' . $i];
        }
        $webResult['groups'] = $this->_getGroupVulnArray($webResult['owasps'], $lang);
        return $webResult;
    }

    private function ConvertToUTF8($text) {
        $encoding = mb_detect_encoding($text, mb_detect_order(), false);
        if ($encoding == "UTF-8") {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        $out = iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8//IGNORE", $text);
        return $out;
    }

    private function _getGlossArray($qid, $glossList) {
        foreach ($glossList->QID as $gloss) {
            if ($qid == $gloss->QID->__toString()) {
                $result['qid'] = $gloss->QID;
                $result['category'] = $gloss->CATEGORY->__toString();
                $result['severity'] = $gloss->SEVERITY->__toString();
                $result['title'] = $gloss->TITLE->__toString();
                $result['group'] = $gloss->GROUP->__toString();
                $result['owasp'] = $gloss->OWASP->__toString();
                $result['wasc'] = $gloss->WASC->__toString();
                $result['cwe'] = $gloss->CWE->__toString();
                $result['cvss_base'] = $gloss->CVSS_BASE->__toString();
                $result['cvss_temporal'] = $gloss->CVSS_TEMPORAL->__toString();
                $result['description'] = $gloss->DESCRIPTION->__toString();
                $result['impact'] = $gloss->IMPACT->__toString();
                $result['solution'] = $gloss->SOLUTION->__toString();
                return $result;
            }
        }
    }

    private function _getGroupVulnArray($owasps, $lang) {
        $groups = array();
        for ($i = 1; $i <= 10; $i++) {
            if ($owasps['A' . $i] > 0) {
                if ($lang == 'cn') {
                    $group['name'] = $this->owaspCn['A' . $i];
                } else {
                    $group['name'] = $this->owaspEn['A' . $i];
                }
                $group['value'] = $owasps['A' . $i];
                $groups[] = $group;
            }
        }
        return $groups;
    }

    private function _getOwasp($owasps, $lang) {
        if ($owasps != null) {
            $owasps = explode(',', $owasps);
            $result = '';
            foreach ($owasps as $owasp) {
                if ($lang == 'cn') {
                    $result .= $owasp . ' ' . $this->owaspCn[$owasp] . ',';
                } else {
                    $result .= $owasp . ' ' . $this->owaspEn[$owasp] . ',';
                }
            }
            return rtrim($result, ',');
        }
        return null;
    }

    private function _getPayloadsArray($payloads) {
        $results = array();
        foreach ($payloads->PAYLOAD as $payload) {
            $result['method'] = $payload->REQUEST->METHOD->__toString();
            $result['url'] = $payload->REQUEST->URL->__toString();
            $result['contents'] = htmlspecialchars(base64_decode($payload->RESPONSE->CONTENTS->__toString()));
            $headers = $payload->REQUEST->HEADERS;
            foreach ($headers->HEADER as $value) {
                $result['headers'][$value->key->__toString()] = $value->value->__toString();
            }
            $results[] = $result;
        }
        return $results;
    }

    private function _toTimeZone($src, $from_tz = 'UTC', $to_tz = 'Asia/Shanghai', $fm = 'Y-m-d H:i:s') {
        $datetime = new DateTime('@' . $src, new DateTimeZone($from_tz));
        $datetime->setTimezone(new DateTimeZone($to_tz));
        return $datetime->format($fm);
    }
}