
<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * 统计客户端
 * @author workerman.net
 */
class StatisticClient
{
    /**
     * [module=>[interface=>time_start, interface=>time_start ...], module=>[interface=>time_start ..], ... ]
     * @var array
     */
    protected static $timeMap = array();
    
    /**
     * 模块接口上报消耗时间记时
     * @param string $title
     * @param string $address
     * @return void
     */
    public static function tick($title = '', $address = '')
    {
        return self::$timeMap[$title][$address] = microtime(true);
    }
    
    /**
     * 上报统计数据
     * @param string $title
     * @param string $address
     * @param bool $success
     * @param int $code
     * @param string $msg
     * @param string $report_address
     * @return boolean
     */
    public static function report($title, $address, $success, $code, $msg, $report_address = '')
    {
        var_dump($msg,333);
        $report_address = $report_address ? $report_address : 'udp://120.27.224.152:55656';
        if(isset(self::$timeMap[$title][$address]) && self::$timeMap[$title][$address] > 0)
        {
            $time_start = self::$timeMap[$title][$address];
            self::$timeMap[$title][$address] = 0;
        }
        else if(isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0)
        {
            $time_start = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;
        }
        else
        {
            $time_start = microtime(true);
        }
         
        $cost_time = microtime(true) - $time_start;
        
        $bin_data = StatisticProtocol::encode($title, $address, $cost_time, $success, $code,$msg);

//        echo '=========';
//        var_dump($bin_data);die;
        return self::sendData($report_address, $bin_data);
    }
    
    /**
     * 发送数据给统计系统
     * @param string $address
     * @param string $buffer
     * @return boolean
     */
    public static function sendData($address, $buffer)
    {
        $socket = stream_socket_client($address,$errno, $errstr);
        if(!$socket)
        {
                echo "erreur : $errno - $errstr<br />n";

            return false;
        }
        return stream_socket_sendto($socket, $buffer) == strlen($buffer);
    }
    
}

/**
 *
 * struct statisticPortocol
 * {
 *     unsigned char module_name_len;
 *     unsigned char interface_name_len;
 *     float cost_time;
 *     unsigned char success;
 *     int code;
 *     unsigned short msg_len;
 *     unsigned int time;
 *     char[module_name_len] module_name;
 *     char[interface_name_len] interface_name;
 *     char[msg_len] msg;
 * }
 *
 * @author workerman.net
 */
class StatisticProtocol
{
    /**
     * 包头长度
     * @var integer
     */
    const PACKAGE_FIXED_LENGTH = 17;

    /**
     * udp 包最大长度
     * @var integer
     */
    const MAX_UDP_PACKGE_SIZE  = 65507;

    /**
     * char类型能保存的最大数值
     * @var integer
     */
    const MAX_CHAR_VALUE = 255;

    /**
     *  usigned short 能保存的最大数值
     * @var integer
     */
    const MAX_UNSIGNED_SHORT_VALUE = 65535;

    /**
     * 编码
     * @param string $title
     * @param string $address
     * @param float $cost_time
     * @param int $success
     * @param int $code
     * @param string $msg
     * @return string
     */
    public static function encode($title, $address , $cost_time, $success,  $code = 0, $msg = '')
    {
        // 防止模块名过长
        if(strlen($title) > self::MAX_CHAR_VALUE)
        {
            $title = substr($title, 0, self::MAX_CHAR_VALUE);
        }

        // 防止接口名过长
        if(strlen($address) > self::MAX_CHAR_VALUE)
        {
            $address = substr($address, 0, self::MAX_CHAR_VALUE);
        }

        // 防止msg过长
        $title_name_length = strlen($title);
        $address_name_length = strlen($address);
        $avalible_size = self::MAX_UDP_PACKGE_SIZE - self::PACKAGE_FIXED_LENGTH - $title_name_length - $address_name_length;
        if(strlen($msg) > $avalible_size)
        {
            $msg = substr($msg, 0, $avalible_size);
        }

        // 打包
        return pack('CCfCNnN', $title_name_length, $address_name_length, $cost_time, $success ? 1 : 0, $code, strlen($msg), time()).$title.$address.$msg;
    }
     
    /**
     * 解包
     * @param string $bin_data
     * @return array
     */
    public static function decode($bin_data)
    {
        // 解包
        $data = unpack("Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime", $bin_data);
        $title = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['module_name_len']);
        $address = substr($bin_data, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'], $data['interface_name_len']);
        $msg = substr($bin_data, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'] + $data['interface_name_len']);
        return array(
                'module'          => $title,
                'interface'        => $address,
                'cost_time' => $data['cost_time'],
                'success'           => $data['success'],
                'time'                => $data['time'],
                'code'               => $data['code'],
                'msg'                => $msg,
        );
    }

}

if(PHP_SAPI == 'cli' && isset($argv[0]) && $argv[0] == basename(__FILE__))
{
    StatisticClient::tick("TestModule", 'TestInterface');
    usleep(rand(10000, 600000));
    $success = rand(0,1);
    $code = rand(300, 400);
    $msg = '这个是测试消息';
    var_export(StatisticClient::report('TestModule', 'TestInterface', $success, $code, $msg));;
}
