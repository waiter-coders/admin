<?php
// 环境初始化
ini_set('register_global', false);
date_default_timezone_set('Asia/Shanghai'); // 默认为东八区
header("content-type;text/html;charset=utf8"); // 默认字符为utf8

// 启动引擎自动装载机制
Engine::addPath('Lib', __DIR__ . '/lib'); // 引擎类
spl_autoload_register(array('Engine', 'load'), true, true);

// 尝试加载composer的自动装载
$composerFile = __DIR__ . '/vendor/autoload.php';
file_exists($composerFile) && include($composerFile);

// 尝试加载语法糖函数
(!defined('USE_FUNCTION_FILE') || USE_FUNCTION_FILE == true) && include(__DIR__ . '/function.php');


/**
 * 引擎环境类
 */
class Engine
{
    private static $_autoload = array(); // 全局加载类路径

    // 装载类文件
    public static function load($class)
    {
        $domain = self::extractDomain($class); // 获取类所在域
        $domain = empty($domain) ? 'Lib' : $domain; // 域为空则默认为引擎域
        if (!isset(self::$_autoload[$domain])) {
            return false;
        }
        $classPath =  self::extractPath($class); // 获取类的相对路径
        foreach (self::$_autoload[$domain] as $root) {
            $file = $root . '/' . $classPath;
            if (is_file($file)) {
                return require $file; // 加载文件
            }
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

    // 获得相对类路径
    private static function extractPath($class)
    {
        $paths = explode('\\', $class);
        count($paths) > 1 && array_shift($paths); // 去掉非lib域所占地址
        return implode('/', $paths) . '.php';
    }

    // 获取顶级类域
    private static function extractDomain($class)
    {
        return ucfirst(substr($class, 0, strpos($class, '\\')));
    }
}

/*
 * 基础功能函数
 */
// 不同于array_merge_recursive，array_merge_cover相同键名时，后者覆盖前者
function array_merge_cover($baseArray, $mergeArray)
{
    foreach ($mergeArray as $key=>$value) {
        if (is_array($value)) {
            !isset($baseArray[$key]) && $baseArray[$key] = array();
            $baseArray[$key] = array_merge_cover($baseArray[$key], $value);
        } else {
            $baseArray[$key] = $value;
        }
    }
    return $baseArray;
}

// 检查是否是dot结构
function isDot($dot)
{
    return (is_string($dot) && strpos($dot, '.') > 0);
}

// 把 controller.home.show 类型的转化为ControllerHome类和方法 show
function dotToMethod($dot)
{
    $class = explode('.', $dot);
    $method = array_pop($class);
    return array(dotToClass($class), $method);
}

// 把 controller.home类型的转化为ControllerHome类
function dotToClass($dot)
{
    $class = is_array($dot) ? $dot : explode('.', $dot);
    foreach ($class as $key=>$value) {
        $class[$key] = ucfirst($value);
    }
    return '\\' . implode('\\', $class);
}

// 根据dot键名获取数组数据
function getDataByDot($data, $dot)
{
    // 一层一层搜索键值数组
    $dot = empty($dot) ? array() : explode('.', $dot);
    foreach ($dot as $key) {
        assertOrException(isset($data[$key]), 'has no item:' . $key);
        $data = $data[$key];
    }
    return $data;
}

function assertOrException($boolean, $errorMessage, $code = 500)
{
    if (!$boolean) {
        throw new Exception($errorMessage, $code);
    }
}

function lowerToUpper($class)
{

}

// 语法糖
function table($table, $name = 'default') // 数据库访问
{
    return DB::table($table, $name);
}