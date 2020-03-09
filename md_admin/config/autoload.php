<?php
if (!defined('ROOTPATH')) {
    $url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . '/error404';
    header('Location: ' . $url, TRUE, 302);
    exit();
}
return array(
        'packages' => array(),
        'libraries' => array(
                'session',
                'smarty',
                'form_validation' 
        ),
        'helper' => array(),
        'config' => array() 
);