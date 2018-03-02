<?php
class Container
{
    private static $instanceClass = array(); // 单例类

    // 单例工具
    public static function instance($className, $params = array())
    {
        $class = self::formatClassName($className);
        if (isset(self::$instanceClass[$className])) {
            return self::$instanceClass[$className];
        }
        // 产生新对象
        self::$instanceClass[$className] = new $class($params);
        return self::$instanceClass[$className];
    }

    private static function formatClassName($class)
    {
        $class = str_replace('.', '\\', $class);
        $class = explode('\\', $class);
        foreach ($class as $key=>$value) {
            $class[$key] = ucfirst($value);
        }
        return '\\'.implode('\\', $class);
    }
}