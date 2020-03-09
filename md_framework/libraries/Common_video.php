<?php
class CI_Common_video {
    private static $key = 'video.aqdog.com/';
    var $CI;
    var $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_log();
        $this->log->log_debug('Common_video class be initialized');
    }

    public function getSourceVideo($deeppath, $videofilename) {
        return urlencode($this->encrypt(gzcompress($deeppath, 9) ). '_' . $videofilename);
    }

    private function encrypt($input) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $size - (strlen($input) % $size);
        $input = $input . str_repeat(chr($pad), $pad);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, self::$key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }
    
    public function getDeepPath() {
        $deep = time() % 2000;
        $deep = str_pad($deep, 4, '0', STR_PAD_LEFT) . '/';
        // $srcpath = PIC_SRC_PATH . $deep;
        // if (!is_dir($srcpath)) {
        // @mkdir($srcpath);
            // }
            $time = substr(microtime(TRUE), -6, 5);
            $time = intval(str_replace('.', '', $time));
            if (strlen($time) < 4) {
                $time = $time * 10;
            }
            $deep .= $time < 5000 ? '0' : '1';
            $deep .= substr($time, -3, 3);
            // $srcpath = PIC_SRC_PATH . $deep;
            // if (!is_dir($srcpath)) {
            // @mkdir($srcpath);
            // }
            return $deep;
        }
    
        public function mkdirs($dir) {
            if (is_dir($dir) || @mkdir($dir, DIR_WRITE_MODE))
                return TRUE;
            if (!$this->mkdirs(dirname($dir)))
                return FALSE;
            return @mkdir($dir, DIR_WRITE_MODE);
        }
    
        private function compressArray($data) {
            $str = '';
            foreach ($data as $key=>$value) {
                $str .= $value . '?';
            }
            $str = rtrim($str, '?');
            return gzcompress($str, 9);
        }
    
        public function createVideoFilename() {
            $num = mt_rand(100, 999) . round(microtime(TRUE) * 1000);
            static $arr = array(
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    'a',
                    'A',
                    'b',
                    'B',
                    'c',
                    'C',
                    'd',
                    'D',
                    'e',
                    'E',
                    'f',
                    'F',
                    'g',
                    'G',
                    'h',
                    'H',
                    'i',
                    'I',
                    'j',
                    'J',
                    'k',
                    'K',
                    'l',
                    'I',
                    'm',
                    'M',
                    'n',
                    'N',
                    'o',
                    'O',
                    'p',
                    'P',
                    'q',
                    'Q',
                    'r',
                    'R',
                    's',
                    'S',
                    't',
                    'T',
                    'u',
                    'U',
                    'v',
                    'V',
                    'w',
                    'W',
                    'x',
                    'X',
                    'y',
                    'Y',
                    'z',
                    'Z'
            );
            $t = "";
            $num = intval($num);
            if ($num === 0)
                continue;
            while($num > 0) {
                $t = $arr[$num % 62] . $t;
                $num = floor($num / 62);
            }
            $tlen = strlen($t);
            if ($tlen) {
                $t = str_pad("", 1, "0", STR_PAD_LEFT) . $t; // 不足一个字节长度，自动前面补充0
            }
            return $t;
        }
}