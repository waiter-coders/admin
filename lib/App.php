<?php
class App
{
    private static $mainPath = '';
    private static $apps = array();

    private static $defaultConfig = array(
        'route'=>array('default'=>'controller.page'),
        'app'=>array(
        ),
    );

    public static function get($appPath, $config = array())
    {
        // 转化为全路径
        $rulePath = realpath($appPath);
        if (!is_dir($rulePath)) {
            throw new Exception('path not exist:'. $rulePath);
        }
        // 创建
        self::$mainPath = empty(self::$mainPath) ? $appPath : self::$mainPath; // 首次装载的应用路径为主应用路径
        if (!isset(self::$apps[$appPath])) {
            $config = empty($config) ? self::$defaultConfig : array_cover(self::$defaultConfig, $config);
            self::$apps[$appPath] =  new AppInstance($appPath, $config);
        }
        return self::$apps[$appPath];
    }

    public static function main()
    {
        return self::get(self::$mainPath);
        Engine::addPath('Lib', __DIR__ . DIRECTORY_SEPARATOR);
        Engine::addPath('App', dirname(dirname( __DIR__)) . DIRECTORY_SEPARATOR . 'App');
        Service::setPath(dirname(dirname( __DIR__))  . '/Service');// 功能服务文件根路径
    }
}



class Dispatcher
{

}


class AppRequest
{
    public function __call($method, $arguments)
    {
        return call_user_func_array(array('\Request', $method), $arguments);
    }
}


class QueueBase extends AppBase
{

}

//class Request
//{
//
//}
//
//class Response
//{
//
//}