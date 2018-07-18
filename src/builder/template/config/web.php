<?php
$config = array();

// 添加数据库配置
$config['database'] = loadConfig(array('database.php', 'database.local.php'), __DIR__);

// 后台可管理的平台
$config['platform'] = array(
    1=>'平台A',
    2=>'平台B',
    3=>'平台C',
    4=>'平台D'
);

return $config;