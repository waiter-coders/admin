<?php

class Config
{
    static private $path = array();

    static public function addPath($path)
    {
        array_unshift(self::$path, $path); // 越后进入的越先被加载
    }

    // 提供一种通过"."链接的配置项获取方式
    static public function get($config, $path = '')
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
                    $config = array_cover($content, $config);
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
    
}

