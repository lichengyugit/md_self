<?php

// 文件操作类语言包
$lang['file_not_exists'] = '文件不存在';
$lang['dir_not_exists'] = '文件夹不存在';

// 文件上传类语言包
$lang['upload_error_1'] = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
$lang['upload_error_2'] = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
$lang['upload_error_3'] = '文件只有部分被上传';
$lang['upload_error_4'] = '没有文件被上传';
$lang['upload_error_6'] = '找不到临时文件夹';
$lang['upload_error_7'] = '文件写入失败';
$lang['upload_error_unknow'] = '未知上传错误';
$lang['upload_rename_rule_error'] = '文件命名规则错误';
$lang['upload_nofile_upload'] = '没有上传的文件';
$lang['upload_image_illegal'] = '非法图像文件';
$lang['upload_file_illegal'] = '上传非法文件';
$lang['upload_driver_not_exists'] = '上传驱动不存在';
$lang['upload_file_size_error'] = '上传文件大小不符';
$lang['upload_file_mime_unallow'] = '上传文件MIME类型不允许';
$lang['upload_file_ext_unallow'] = '上传文件后缀不允许';
$lang['upload_file_exists'] = '同名文件已存在';
$lang['upload_file_save_error'] = '上传文件保存错误';
$lang['upload_dir_unwriteable'] = '上传目录不可写';
$lang['upload_image_xxs'] = '上传的图片文件含xxs攻击';
$lang['upload_picture_sizes_error'] = '上传图片尺寸不符合要求';
$lang['upload_create_dir_error'] = '创建目录失败';

// 重复提交类
$lang['unrepeat_token_exists'] = '请勿重复提交';

// curl操作类
$lang['curl_not_open'] = 'php.ini配置文件中没有开启curl选项';
$lang['curl_overtime'] = 'curl获取时超时';

// 数据库文档生成类
$lang['dbdoc_only_on_dev'] = '仅在允许在开发模式下使用';
$lang['dbdoc_allow'] = '允许';
$lang['dbdoc_un_allow'] = '不允许';

// Rsa加密类
$lang['ras_encrypt_error'] = '加密失败，可能是字符串太长';

// 过滤关键字
$lang['filterword_has_combination_keyword'] = '含组合关键字';
$lang['filterword_has_keyword'] = '含关键字';
return $lang;
/* End of file date_lang.php */
/* Location: ./system/language/english/date_lang.php */