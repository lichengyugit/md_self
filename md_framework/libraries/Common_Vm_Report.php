<?php
class CI_Common_Vm_Report {
    var $CI;
    var $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_log();
        $this->log->log_debug('CI_Common_Vm_Report class be initialized');
    }

    public function getIpsResult($deeppath, $xmlFilename, $lang) {
        $xml = file_get_contents(XML_PATH . '/vm/' . $deeppath . DS . $xmlFilename);
        $result = simplexml_load_string($xml, null, LIBXML_NOCDATA);
        $ips = array();
        if (isset($result->IP)) {
            $ips = $result->IP;
        }
        
        $ipsResult = array();
        $vulnCnInfoModel = M_Mysqli_Class('cro_scan', 'VulnsCnInfoModel');
        $ipsResult['total_vulns_count'] = 0;
        $ipsResult['total_potential_vulns_count'] = 0;
        $ipsResult['total_infos_count'] = 0;
        $ipsResult['open_port_count'] = 0;
        $ipsResult['vulns_port_count'] = 0;
        $ipsResult['vulns_port'] = array();
        $ipsResult['open_port'] = array();
        $ipsResult['ips'] = array();
        
        for ($i = 1; $i <= 5; $i++) {
            $ipsResult['total_ip_vulns'][$i]['total'] = 0;
            $ipsResult['total_ip_potential_vulns'][$i]['total'] = 0;
            $ipsResult['total_ip_infos'][$i]['total'] = 0;
        }
        foreach ($ips as $ip) {
            $ipResult = array();
            $ipResult['vulns_count'] = 0;
            $ipResult['potential_vulns_count'] = 0;
            $ipResult['infos_count'] = 0;
            
            $ipResult['os'] = $ip->OS->__toString();
            $ipResult['ip'] = $ip->attributes()->value->__toString();
            $ipResult['servers'] = 0;
            
            $ipResult['vulns'] = $this->_getVulns($ip->VULNS->CAT, $lang);
            $ipResult['potential_vulns'] = $this->_getPotentials($ip->PRACTICES->CAT, $lang);
            $ipResult['infos'] = $this->_getInfos($ip->INFOS->CAT, $lang);
            
            foreach ($ipResult['vulns'] as $key=>$vulns) {
                foreach ($vulns as $vuln) {
                    if ($vuln['port']) {
                        if (isset($ipResult['ip_vulns_port'][$vuln['port']][$vuln['server_name']])) {
                            $ipResult['ip_vulns_port'][$vuln['port']][$vuln['server_name']]++;
                        } else {
                            $ipResult['ip_vulns_port'][$vuln['port']][$vuln['server_name']] = 1;
                        }
                        $ipResult['ip_open_port'][$vuln['port']][$vuln['server_name']] = $vuln['server_name'];
                        $ipsResult['open_port'][$vuln['port']][$vuln['server_name']]['ip'][] = $ipResult['ip'];
                        $ipsResult['vulns_port'][$vuln['port']][$vuln['server_name']]['ip'][] = $ipResult['ip'];
                    }
                }
            }
            foreach ($ipResult['potential_vulns'] as $key=>$vulns) {
                foreach ($vulns as $vuln) {
                    if ($vuln['port']) {
                        $ipResult['ip_open_port'][$vuln['port']][$vuln['server_name']] = $vuln['server_name'];
                        $ipsResult['open_port'][$vuln['port']][$vuln['server_name']]['ip'][] = $ipResult['ip'];
                        $ipsResult['vulns_port'][$vuln['port']][$vuln['server_name']]['ip'][] = $ipResult['ip'];
                    }
                }
            }
            
            foreach ($ipsResult['open_port'] as $key=>$value) {
                $ipResult['servers'] += count($value);
                $ipsResult['open_port_count'] += count($value);
            }
            
            foreach ($ipsResult['vulns_port'] as $key=>$value) {
                $ipsResult['vulns_port_count'] += count($value);
            }
            
            for ($i = 1; $i <= 5; $i++) {
                if (!isset($ipsResult['total_ip_vulns_count'][$ipResult['ip']][$i])) {
                    $ipsResult['total_ip_vulns_count'][$ipResult['ip']][$i] = 0;
                }
                $count = isset($ipResult['vulns'][$i]) ? count($ipResult['vulns'][$i]) : 0;
                $ipResult['vulns_count'] += $count;
                $ipsResult['total_ip_vulns_count'][$ipResult['ip']][$i] += $count;
                $ipsResult['total_ip_vulns'][$i]['total'] += $count;
                
                if (!isset($ipsResult['total_ip_potential_vulns_count'][$ipResult['ip']][$i])) {
                    $ipsResult['total_ip_potential_vulns_count'][$ipResult['ip']][$i] = 0;
                }
                $count = isset($ipResult['potential_vulns'][$i]) ? count($ipResult['potential_vulns'][$i]) : 0;
                $ipResult['potential_vulns_count'] += $count;
                $ipsResult['total_ip_potential_vulns_count'][$ipResult['ip']][$i] += $count;
                $ipsResult['total_ip_potential_vulns'][$i]['total'] += $count;
                
                if (!isset($ipsResult['total_ip_infos_count'][$ipResult['ip']][$i])) {
                    $ipsResult['total_ip_infos_count'][$ipResult['ip']][$i] = 0;
                }
                $count = isset($ipResult['infos'][$i]) ? count($ipResult['infos'][$i]) : 0;
                $ipResult['infos_count'] += $count;
                $ipsResult['total_ip_infos_count'][$ipResult['ip']][$i] += $count;
                $ipsResult['total_ip_infos'][$i]['total'] += $count;
            }
            
            $ipsResult['total_vulns_count'] += $ipResult['vulns_count'];
            $ipsResult['total_potential_vulns_count'] += $ipResult['potential_vulns_count'];
            $ipsResult['total_infos_count'] += $ipResult['infos_count'];
            for ($i = 1; $i <= 5; $i++) {
                $ipsResult['total_ip_vulns'][$i]['ips'][] = $ipResult['ip'] . '(' . $ipsResult['total_ip_vulns_count'][$ipResult['ip']][$i] . ')';
                $ipsResult['total_ip_potential_vulns'][$i]['ips'][] = $ipResult['ip'] . '(' . $ipsResult['total_ip_potential_vulns_count'][$ipResult['ip']][$i] . ')';
                $ipsResult['total_ip_infos'][$i]['ips'][] = $ipResult['ip'] . '(' . $ipsResult['total_ip_infos_count'][$ipResult['ip']][$i] . ')';
            }
            $ipsResult['ips'][] = $ipResult;
        }
        
        return $ipsResult;
    }

    private function _getVulns($cats, $lang) {
        $vulnResults = array();
        if ($cats) {
            $vulnCnInfoModel = M_Mysqli_Class('cro_scan', 'VulnsCnInfoModel');
            foreach ($cats as $cat) {
                $vulns = $cat->VULN;
                $misc = '';
                if (isset($cat->attributes()->misc)) {
                    $misc = $cat->attributes()->misc->__toString();
                }
                $port = '';
                if (isset($cat->attributes()->port)) {
                    $port = $cat->attributes()->port->__toString();
                }
                $protocol = '';
                if (isset($cat->attributes()->protocol)) {
                    $protocol = $cat->attributes()->protocol->__toString();
                }
                $serverName = '';
                if (isset($cat->attributes()->value)) {
                    $serverName = $cat->attributes()->value->__toString();
                }
                foreach ($vulns as $vuln) {
                    $qid = $vuln->attributes()->number->__toString();
                    $vulnResult = $vulnCnInfoModel->getVnlnInfoByQid($qid);
                    if ($vulnResult) {
                        if (isset($vuln->CVE_ID_LIST)) {
                            $vulnResult['cve'] = $this->_getCve($vuln->CVE_ID_LIST);
                        }
                        if (isset($vuln->CORRELATION->EXPLOITABILITY)) {
                            $vulnResult['exploitability'] = $this->_getExploitability($vuln->CORRELATION->EXPLOITABILITY->EXPLT_SRC);
                        }
                        if ($lang == 'en') {
                            $vulnResult['title'] = $vuln->TITLE;
                            $vulnResult['diagnosis'] = $vuln->DIAGNOSIS;
                            $vulnResult['consequence'] = $vuln->CONSEQUENCE;
                            $vulnResult['solution'] = $vuln->SOLUTION;
                        }
                        $vulnResult['result'] = $vuln->RESULT;
                        $vulnResult['port'] = $port;
                        $vulnResult['protocol'] = $protocol;
                        $vulnResult['misc'] = $misc;
                        $vulnResult['server_name'] = $serverName;
                        $vulnResults[$vulnResult['severity_level']][] = $vulnResult;
                    }
                }
            }
        }
        return $vulnResults;
    }

    private function _getPotentials($cats, $lang) {
        $potentialResults = array();
        if ($cats) {
            $vulnCnInfoModel = M_Mysqli_Class('cro_scan', 'VulnsCnInfoModel');
            foreach ($cats as $cat) {
                $potentials = $cat->PRACTICE;
                $misc = '';
                if (isset($cat->attributes()->misc)) {
                    $misc = $cat->attributes()->misc->__toString();
                }
                $port = '';
                if (isset($cat->attributes()->port)) {
                    $port = $cat->attributes()->port->__toString();
                }
                $protocol = '';
                if (isset($cat->attributes()->protocol)) {
                    $protocol = $cat->attributes()->protocol->__toString();
                }
                $serverName = '';
                if (isset($cat->attributes()->value)) {
                    $serverName = $cat->attributes()->value->__toString();
                }
                foreach ($potentials as $potential) {
                    $qid = $potential->attributes()->number->__toString();
                    $potentialResult = $vulnCnInfoModel->getVnlnInfoByQid($qid);
                    if ($potentialResult) {
                        if (isset($potential->CVE_ID_LIST)) {
                            $potentialResult['cve'] = $this->_getCve($potential->CVE_ID_LIST);
                        }
                        if (isset($vuln->CORRELATION->EXPLOITABILITY)) {
                            $potentialResult['exploitability'] = $this->_getExploitability($potential->CORRELATION->EXPLOITABILITY->EXPLT_SRC);
                        }
                        if ($lang == 'en') {
                            $potentialResult['title'] = $potential->TITLE;
                            $potentialResult['diagnosis'] = $potential->DIAGNOSIS;
                            $potentialResult['consequence'] = $potential->CONSEQUENCE;
                            $potentialResult['solution'] = $potential->SOLUTION;
                        }
                        $potentialResult['result'] = $potential->RESULT;
                        $potentialResult['port'] = $port;
                        $potentialResult['protocol'] = $protocol;
                        $potentialResult['misc'] = $misc;
                        $potentialResult['server_name'] = $serverName;
                        $potentialResults[$potentialResult['severity_level']][] = $potentialResult;
                    }
                }
            }
        }
        return $potentialResults;
    }

    private function _getInfos($cats, $lang) {
        $infoResults = array();
        if ($cats) {
            $vulnCnInfoModel = M_Mysqli_Class('cro_scan', 'VulnsCnInfoModel');
            foreach ($cats as $cat) {
                $infos = $cat->INFO;
                $misc = '';
                if (isset($cat->attributes()->misc)) {
                    $misc = $cat->attributes()->misc->__toString();
                }
                $port = '';
                if (isset($cat->attributes()->port)) {
                    $port = $cat->attributes()->port->__toString();
                }
                $protocol = '';
                if (isset($cat->attributes()->protocol)) {
                    $protocol = $cat->attributes()->protocol->__toString();
                }
                $serverName = '';
                if (isset($cat->attributes()->value)) {
                    $serverName = $cat->attributes()->value->__toString();
                }
                foreach ($infos as $info) {
                    $qid = $info->attributes()->number->__toString();
                    $infoResult = $vulnCnInfoModel->getVnlnInfoByQid($qid);
                    if ($infoResult) {
                        if (isset($info->CVE_ID_LIST)) {
                            $infoResult['cve'] = $this->_getCve($info->CVE_ID_LIST);
                        }
                        if (isset($vuln->CORRELATION->EXPLOITABILITY)) {
                            $infoResult['exploitability'] = $this->_getExploitability($info->CORRELATION->EXPLOITABILITY->EXPLT_SRC);
                        }
                        if ($info->attributes()->cveid) {
                            $cveIdStr = $info->attributes()->cveid->__toString();
                            if ($cveIdStr) {
                                $potentialResult['cve_id'] = $cveIdStr;
                                $infoResult['pocs'] = $this->_getPocs($cveIdStr);
                            }
                        }
                        if ($lang == 'en') {
                            $infoResult['title'] = $info->TITLE;
                            $infoResult['diagnosis'] = $info->DIAGNOSIS;
                            $infoResult['consequence'] = $info->CONSEQUENCE;
                            $infoResult['solution'] = $info->SOLUTION;
                        }
                        $infoResult['result'] = $info->RESULT;
                        $infoResult['port'] = $port;
                        $infoResult['protocol'] = $protocol;
                        $infoResult['misc'] = $misc;
                        $infoResult['server_name'] = $serverName;
                        $infoResults[$infoResult['severity_level']][] = $infoResult;
                    }
                }
            }
        }
        return $infoResults;
    }

    private function _getCve($cveList) {
        $cveResults = array();
        foreach ($cveList->CVE_ID as $cve) {
            $cveResult['cve_id'] = $cve->ID->__toString();
            if (isset($cve->URL)) {
                $cveResult['url'] = $cve->URL->__toString();
            }
            $cveResults[] = $cveResult;
        }
        return $cveResults;
    }

    private function _getExploitability($exploitability) {
        $pocsResult = array();
        $pocsResult['name'] = $exploitability->SRC_NAME->__toString();
        $pocsResult['explts'] = array();
        foreach ($exploitability->EXPLT_LIST->EXPLT as $poc) {
            $explt['ref'] = $poc->REF;
            $explt['link'] = $poc->LINK;
            $explt['description'] = $poc->DESC;
            $pocsResult['explts'][] = $explt;
        }
        return $pocsResult;
    }
}