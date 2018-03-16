<?php
class App
{
    private static $currentApp = null;

    public static function web()
    {
        return self::$currentApp = new AppWebCore();
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
        return self::$currentApp = new AppCore();
    }

    public static function current()
    {
        return self::$currentApp;
    }
}


class Shell_App_Engine extends AppCore
{

}

class Consumer_App_Engine extends AppCore
{

}

class WebSocket_App_Engine extends AppCore
{

}