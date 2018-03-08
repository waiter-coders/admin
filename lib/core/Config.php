<?php

class Config
{
    private static $configs = array();

    public static function create($paths, $name = 'default')
    {
        return self::$configs[$name] = new Config($paths);
    }

    public static function instance($name)
    {
        return isset(self::$configs[$name]) ? self::$configs[$name] : null;
    }


    private $paths = array();
    private $config = array();

    private function __construct($paths)
    {
        $this->paths = !is_array($paths) ? array($paths) : $paths;
    }

    public function get($config)
    {
        $config = explode('.', $config, 2);var_dump($config);
        $domain = array_shift($config);
        if (!isset($this->config[$domain])) {
            $this->config[$domain] = $this->loadFile($domain);
        }
        return getDataByDot($this->config[$domain], array_shift($config));
    }

    public function loadFile($domain)
    {
        $config = array();
        $files = $this->fetchDomainFiles($domain);
        foreach ($files as $file) {
            $fileConfig = require $file;
            $fileConfig = empty($fileConfig) ? array() : $fileConfig;
            $config = array_merge_cover($fileConfig, $config);
        }
        return $config;
    }

    private function fetchDomainFiles($domain)
    {
        $paths = array();
        foreach ($this->paths as $path) {
            $file = $path . '/' . $domain . '.php';
            is_file($file) && $paths[] = $file;
            $localFile = $path . '/' . $domain . '.local.php';
            is_file($localFile) && $paths[] = $localFile;
        }
        return $paths;
    }
}

