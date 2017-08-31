<?php
defined('THINK_PATH') or die();
return array(
    'title' => '亿速网络安装向导',
    'is_write_dir' => [ // 目录可写监测
        'Install',
        'Uploads',
        'Application/Common/Conf',
        'Application/Runtime',
        'Application/Runtime/Cache',
        'Application/Runtime/Data',
        'Application/Runtime/Logs',
        'Application/Runtime/Temp'
    ],
    'file' => './2017-8-23-shopsn.sql',
    'config' => './config.php',
    'installed_config' => './Application/Common/Conf/db.php',
    'put_file'         => './Install/install.lock',
);