<?php
try {
    // 初始化环境
    ini_set('register_global', false);
    date_default_timezone_set('Asia/Shanghai'); // 默认为东八区
    header("Content-type: text/html; charset=utf-8"); // 默认字符为utf8
    define('APP_PATH', realpath(__DIR__ . '/../'));

    // 装载composer的autoload
    require APP_PATH . '/vendor/autoload.php';

    // 初始化场景
    $scenes = scenes('main');

    // 加载配置文件
    $config = (isset($_ENV['config']) && is_array($_ENV['config'])) ? $_ENV['config'] : []; // php环境配置
    $config = array_deep_cover($config, loadConfig('web.php', APP_PATH . '/../config')); // 服务器公共配置
    $config = array_deep_cover($config, loadConfig(array('web.php', 'web.local.php'), APP_PATH . '/config/')); // 项目配置和开发本地配置
    $scenes->setConfig($config);

    // 初始化相关类库
    isset($config['database']) && \Waiterphp\Core\DB::register($config['database']); // 配置数据库
//    isset($config['cache']) && \Waiterphp\Core\Storage::register($config['cache']); // 视图

    // 处理请求
    $request = Request\Web::get();
    $scenes->set('request', $request);

    // 执行路由
    $action = (new \Router\Web($request))->targetAction();
    $scenes->set('action', $action);

    // 执行业务控制器
    $response = run($action, $request);
    $scenes->set('response', $response);

    // 处理返回值
    if ($response !== NULL) {
        $builder = is_object($response) ? (new Response\View($response)) : (new Response\Json($response));
        echo $builder->output();
    }
}

// 处理异常行为
catch (Exception $e) {
    echo (new Response\Error($e))->output();
}
