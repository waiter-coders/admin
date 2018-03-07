<?php
class Instance
{
    private static $instances = array(); // 单例类

    // 单例工具
    public static function get($class, $params = array())
    {
        $class = (strpos($class, '.') > 0) ? dotToClass($class) : $class;
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }
        // 产生新对象
        self::$instances[$class] = new $class($params);
        return self::$instances[$class];
    }
}