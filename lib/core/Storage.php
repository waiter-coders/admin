<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/22
 * Time: 9:54
 */
class Storage
{
    private static $path = '';

    public static function setTempPath($path)
    {
        self::$path = $path;
    }

    public static function set($key, $value, $expireTime = 0)
    {
        $file = self::$path . DIRECTORY_SEPARATOR . $key;
        $expireTime = empty($expireTime) ? 0 : time() + $expireTime;
        $content = json_encode(array('value'=>$value, 'expire'=>$expireTime));
        file_put_contents($file, $content);
    }

    public static function get($key)
    {
        $file = self::$path . DIRECTORY_SEPARATOR . $key;
        if (!is_file($file)) {
            return '';
        }
        $content = file_get_contents($file);
        $content = json_decode($content, true);
        if (!empty($content['expire']) && $content['expire'] < time()) {
            return '';
        }
        return $content['value'];
    }
}