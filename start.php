<?php
// 环境初始化
ini_set('register_global', false);
date_default_timezone_set('Asia/Shanghai'); // 默认为东八区
header("content-type;text/html;charset=utf8"); // 默认字符为utf8

// 注入方法
if (!function_exists('waiter_fetch_domain')) {
    function waiter_fetch_domain($class)
    {
        // 无命名空间的为类库类
        if (strpos($class, '\\') === false) {
            return 'Lib';
        }
        // 有命名空间的用根命名为域
        else {
            return ucfirst(substr($class, 0, strpos($class, '\\')));
        }
    }
}
// 注入方法
if (!function_exists('waiter_fetch_class_path')) {
    function waiter_fetch_class_path($class)
    {
        $paths = explode('\\', $class);
        if (count($paths) > 1) {
            array_shift($paths); // 去掉非lib域所占地址
        }
        $file = implode(DIRECTORY_SEPARATOR, $paths);
        return $file . '.php';
    }
}
if (!function_exists('waiter_get_environment')) {
    function waiter_get_environment()
    {
        return 'local';
    }
}


// 启动引擎自动装载机制
Engine::addPath('Lib', __DIR__ . DIRECTORY_SEPARATOR . 'lib'); // 引擎核心类
spl_autoload_register(array('Engine', 'load'), true, true);

// 尝试去加载配置文件信息


// 尝试加载语法糖函数
(!defined('USE_FUNCTION_FILE') || USE_FUNCTION_FILE == true) && include(__DIR__ . '/function.php');

// 尝试加载composer的自动装载
$composerFile = __DIR__ . '/vendor/autoload.php';
file_exists($composerFile) && include($composerFile);


/**
 * 引擎环境类
 */
class Engine
{
    private static $_config = array(); // 环境配置
    private static $_autoload = array(); // 全局加载类路径


    // 装载类文件
    public static function load($class)
    {
        $domain = waiter_fetch_domain($class); // 获取类所在域
        $domain = empty($domain) ? 'Lib' : $domain; // 域为空则默认为引擎域
        $rootPaths = isset(self::$_autoload[$domain]) ? self::$_autoload[$domain] : array();// 获取域下该类可能的根路径
        $classPath =  waiter_fetch_class_path($class); // 获取类的相对路径
        foreach ($rootPaths as $root) {
            $file = $root . DIRECTORY_SEPARATOR . $classPath;
            if (!is_file($file)) {/*&& $file == realpath($file) 先去掉，有问题*/
                continue;
            }
            // 加载文件
            require $file;
            // 有配置信息的自动初始化配置
            if (isset(self::$_config[$class])  && method_exists($class, 'config')) {
                call_user_func(array($class, 'config'), self::$_config[$class]);
                unset(self::$_config[$class]); // 初始化后，释放配置
            }
            return true;
        }
        return false;
    }

    // 添加加载路径信息
    public static function addPath($domain, $paths)
    {
        $paths = is_array($paths) ? $paths : array($paths);
        if (!isset(self::$_autoload[$domain])) {
            self::$_autoload[$domain] = $paths;
        } else {
            self::$_autoload[$domain] = array_merge($paths, self::$_autoload[$domain]);// 越后进入的越先被查找
        }
        return true;
    }

    public static function loadConfig($config)
    {
        if (is_string($config)) {
            $config = self::fetchConf($config);
        }
        if (!empty($config)) {
            self::$_config = array_merge_cover(self::$_config, $config);
        }
    }

    // config文件的契约实现
    private static function fetchConf($path, $isLocal = false)
    {
        $config = array();
        $file = $isLocal ? 'config.local.php' : 'config.php';
        if (is_file($path . '/' . $file)) { // 根目录下的config文件
            $config = require $path . '/' . $file;
        }
        else if (is_file($path . '/config/' . $file)) { //根目录config文件夹下的config文件
            $config = require $path . '/config/' . $file;
        }
        // local机制，加载local配置覆盖默认配置，解决多环境问题
        if (!$isLocal) {
            $config = array_merge_cover($config, self::fetchConf($path, true));
        }
        return $config;
    }

    // 环境类
    public static function environment()
    {
        return waiter_get_environment();
    }
}

/*
 * 基础功能函数
 */
// 不同于array_merge_recursive，array_merge_cover相同键名时，后者覆盖前者
function array_merge_cover($initArray, $mergeArray)
{
    foreach ($mergeArray as $key=>$value) {
        if (is_array($value)) {
            !isset($initArray[$key]) && $initArray[$key] = array();
            $initArray[$key] = array_merge_cover($initArray[$key], $value);
        } else {
            $initArray[$key] = $value;
        }
    }
    return $initArray;
}

// 把 controller.home.show 类型的转化为ControllerHome类和方法 show
function dotToMethod($dot, $hasMethod = true)
{

}




