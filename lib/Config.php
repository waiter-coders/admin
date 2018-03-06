<?php

class Config
{
    private $path = array();
    private $config = array();

    public static function create($paths)
    {
        return new Config($paths);
    }

    private function __construct($paths)
    {
        $this->paths = !is_array($paths) ? array($paths) : $paths;
    }

    public function get($config)
    {
        return dotGetFromArray($config, $this->config);
    }

    static public function addPath($path)
    {
        array_unshift(self::$path, $path); // 越后进入的越先被加载
    }

    // 提供一种通过"."链接的配置项获取方式
    static public function get($path)
    {
        if (empty(self::$path)) {
            throw new Exception('config path not set');
        }

        list($file, $index) = self::sliceInfo($config);
        $data = self::fileData($file);
        return self::dataByIndex($data, $index);
    }

    private static function sliceInfo($config)
    {
        $array = explode('.', $config);
        $file = array_shift($array);
        return array($file, $array);
    }

    private static function fileData($file)
    {
        static $data = array();
        if (!isset($data[$file])) {
            $config = array();
            foreach (self::$path as $path) {
                $filePath = $path . '/' . $file . '.php';
                if (is_file($filePath)) {
                    $content = require $filePath;
                    $content = empty($content) ? array() : $content;
                    $config = array_merge_cover($content, $config);
                }
            }
            if (empty($config)) {
                throw new Exception('not has config file：' . $file);
            }
            $data[$file] = $config;
        }
        return $data[$file];
    }

    private static function dataByIndex($data, $index)
    {
        // 一层一层搜索键值数组
        foreach ($index as $key) {
            if (!isset($data[$key])) {
                throw new Exception('has no item:' . $key);
            }
            $data = $data[$key];
        }

        return $data;
    }

    // en_name 和 ch_name的读取

    public static function loadConfig($config)
    {
        if (is_string($config)) {
            $config = self::fetchConf($config);
        }
        if (!empty($config)) {
//            self::$_config = array_merge_cover(self::$_config, $config);
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
    
}

