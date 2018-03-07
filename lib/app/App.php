<?php
class App
{
    private static $currentApp = null;

    public static function web()
    {
        return self::$currentApp = new App_Engine_Web();
    }

    public static function Shell()
    {
        return self::$currentApp = new Shell_App_Engine();
    }

    public static function consumer()
    {
        return self::$currentApp = new Consumer_App_Engine();
    }

    public static function webSocket()
    {
        return self::$currentApp = new WebSocket_App_Engine();
    }

    public static function create()
    {
        return self::$currentApp = new App_Engine_Base();
    }

    public static function current()
    {
        return self::$currentApp;
    }
}


class Shell_App_Engine extends App_Engine_Base
{

}

class Consumer_App_Engine extends App_Engine_Base
{

}

class WebSocket_App_Engine extends App_Engine_Base
{

}