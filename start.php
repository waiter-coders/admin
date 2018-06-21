<?php
// 环境初始化
ini_set('register_global', false);
date_default_timezone_set('Asia/Shanghai'); // 默认为东八区
header("content-type;text/html;charset=utf8"); // 默认字符为utf8

// 启动引擎自动装载机制
Loader::addLayer('Lib', __DIR__ . '/lib/core'); // 引擎核心类
spl_autoload_register(array('Loader', 'load'), true, true);

// 尝试加载composer的自动装载
$composerFile = __DIR__ . '/vendor/autoload.php';
file_exists($composerFile) && include($composerFile);

// 尝试加载语法糖函数
(!defined('USE_FUNCTION_FILE') || USE_FUNCTION_FILE == true) && include(__DIR__ . '/function.php');

// 初始化环境
$env = isset($_ENV['env']) ? $_ENV['env'] : Env::loadEnvFile('env.php', __DIR__, 2);
Env::checkAndRegister($env, array(
    'database.default',
    ''
));

/**
 * 引擎环境类
 */
class Loader
{
    private static $_autoload = array(); // 全局加载类路径

    // 装载类文件
    public static function load($class)
    {
        $layer = self::extractLayer($class); // 获取类所在域
        $layer = empty($layer) ? 'Lib' : $layer; // 域为空则默认为引擎域
        if (!isset(self::$_autoload[$layer])) {
            return false;
        }
        $classPath =  self::extractPath($class); // 获取类的相对路径
        foreach (self::$_autoload[$layer] as $root) {
            $file = $root . '/' . $classPath;
            if (is_file($file)) {
                return require $file; // 加载文件
            }
        }
        return false;
    }

    // 添加加载路径信息
    public static function addLayer($layer, $paths)
    {
        $paths = is_array($paths) ? $paths : array($paths);
        if (!isset(self::$_autoload[$layer])) {
            self::$_autoload[$layer] = $paths;
        } else {
            self::$_autoload[$layer] = array_merge($paths, self::$_autoload[$layer]);// 越后进入的越先被查找
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
    private static function extractLayer($class)
    {
        return ucfirst(substr($class, 0, strpos($class, '\\')));
    }
}

