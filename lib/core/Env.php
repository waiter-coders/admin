<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17
 * Time: 18:33
 */
class Env
{
    public static function loadEnvFile($file, $path, $upNum = 0)
    {
        $env = array();
        $searchPath = $path;
        do {
            assertOrException(is_dir($searchPath), 'search path not exist');
            $searchFile = $searchPath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($searchFile)) {
                $env = require $searchFile;
                assertOrException(is_array($env), 'file config error' . $searchFile);
                break;
            }
            $searchPath = dirname($searchPath);
            $upNum--;
        } while ($upNum > 0);
        return $env;
    }

    public static function checkAndRegister($env, $checkKeys = array())
    {

    }

    public static function get($key)
    {

    }
}