<?php
/*
 * 事件触发器
 * 实现类似观察者的机制
 */
class Event
{
    private static $action = array();

    public static function register($tab, $action)
    {
        self::$action[$tab][] = $action;
    }

    public static function trigger($tab, $params = array())
    {
        if (!isset(self::$action[$tab])) {
            return false;
        }
        foreach (self::$action[$tab] as $action) {
            $result = call_user_func_array($action, $params);
            if (!$result) {
                break;
            }
        }
        return true;
    }
}