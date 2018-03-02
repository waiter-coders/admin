<?php
class App
{
    public static function web()
    {
        return new Web_App_Engine();
    }

    public static function Shell()
    {
        return new Shell_App_Engine();
    }

    public static function consumer()
    {
        return new Consumer_App_Engine();
    }
}

class App_Engine
{
    protected $configs = array(
        'path.config'=>__DIR__ . '/../config',
        'path.routes'=>__DIR__ . '/../routes',
        'path.controller'=>__DIR__ . '/../controller',
        'path.events'=>__DIR__ . '/../events',
        'path.model'=>__DIR__ . '/../model',
        'path.service'=>__DIR__ . '/../service',
        'path.views.compile'=>__DIR__ . '/../storage/views',
        'path.queue.consumer'=>__DIR__ . '/../consumer',
        'path.shell'=>__DIR__ . '/../shell',
        'path.session'=>__DIR__ . '/../storage/session',
        'path.logs'=>__DIR__ . '/../storage/logs',
    );

    public function set($key, $value)
    {
        $this->configs[$key] = $value;
        return $this;
    }

}


class Web_App_Engine extends App_Engine
{
    private $response = array();

    public function route($routeName = 'web')
    {
        return $this;
    }

    public function request(callable $callback)
    {
        $this->response = $callback($this->response);
        return $this;
    }

    public function response(callable $callback)
    {
        $this->response = $callback($this->response);
        return $this;
    }
}

class Shell_App_Engine extends App_Engine
{

}

class Consumer_App_Engine extends App_Engine
{

}