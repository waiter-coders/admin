<?php
class Container
{
    private static $instances = array(); // 单例类

    // 单例工具
    public static function instance($className, $params = array())
    {
        $class = dotToClass($className);
        if (isset(self::$instances[$className])) {
            return self::$instances[$className];
        }
        // 产生新对象
        self::$instances[$className] = new $class($params);
        return self::$instances[$className];
    }
}