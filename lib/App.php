<?php
class App
{
    private static $currentApp = null;

    public static function web()
    {
        return self::$currentApp = new Web_App_Engine();
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
        return self::$currentApp = new App_Engine();
    }

    public static function current()
    {
        return self::$currentApp;
    }
}

class App_Engine
{
    protected $sets = array(
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
        'path.drives'=>__DIR__ . '/../drives',
        'path.loader'=>array(),
    );

    protected $configs = array();
    protected $isInit = false;
    protected $response = '';

    public function set($key, $value)
    {
        $this->sets[$key] = $value;
        return $this;
    }

    public function get($key)
    {
        return isset($this->sets[$key]) ? $this->sets[$key] : null;
    }

    public function register($event, $method)
    {
        Event::register($event, $method);
    }

    public function trigger($event)
    {
        Event::trigger($event);
    }

    public function route($routeName = 'web')
    {
        $routeFile = $this->sets['path.routes'] . '/' . $routeName . '.php';
        if (!is_file($routeFile)) {
            throw new Exception('route file not exist:' . $routeFile);
        }
        $dotMethod = require $routeFile;
        $dotMethod = dotToMethod($dotMethod);
        $this->init();
        $this->response = call_user_func_array($dotMethod, array());
        return $this;
    }

    public function response(callable $callback)
    {
        $this->response = $callback($this->response);
        return $this;
    }

    public function __destruct()
    {
        echo !is_string($this->response) ? json_encode($this->response) : $this->response;
    }

    protected function init()
    {
        if ($this->isInit == false) {
            // 初始化应用类
            Engine::addPath('Controller', $this->sets['path.controller']);
            Engine::addPath('Event', $this->sets['path.events']);
            Engine::addPath('Model', $this->sets['path.model']);
            Engine::addPath('Service', $this->sets['path.service']);
            Engine::addPath('Drives', $this->sets['path.drives']);

            // 初始化自定义加载类
            if (!empty($this->sets['path.loader'])) {
                foreach ($this->sets['path.loader'] as $domain=>$path) {
                    Engine::addPath(ucfirst($domain), $path);
                }
            }

            // 装载配置文件
            $this->configs = Config::create($this->sets['path.config']);

            // 设置状态
            $this->isInit = true;
        }
    }

}


class Web_App_Engine extends App_Engine
{


}

class Shell_App_Engine extends App_Engine
{

}

class Consumer_App_Engine extends App_Engine
{

}

class WebSocket_App_Engine extends App_Engine
{

}