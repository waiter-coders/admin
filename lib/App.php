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

    protected $config = array();
    protected $isInit = false;
    protected $response = '';
    protected $exceptionCallback = null;

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

    public function config()
    {
        return $this->config;
    }

    public function route($routeName = 'web')
    {
        $routeFile = $this->sets['path.routes'] . '/' . $routeName . '.php';
        if (!is_file($routeFile)) {
            throw new Exception('route file not exist:' . $routeFile);
        }
        $this->init();
        $router = require $routeFile;
        $this->response = $this->executeRouter($router);
        return $this;
    }

    public function response(callable $callback)
    {
        $this->response = $callback($this->response);
        return $this;
    }

    public function exception(callable $callback)
    {
        $this->exceptionCallback = $callback;
    }

    public function __destruct()
    {
        if (!empty($this->response)) {
            echo !is_string($this->response) ? json_encode($this->response) : $this->response;
        }
    }

    protected function init()
    {
        if ($this->isInit == false) {
            Event::trigger('app.init.start');
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
            $this->config = Config::create($this->sets['path.config'], 'app');

            // 设置状态
            $this->isInit = true;
            Event::trigger('app.init.end');
        }
    }


    private function executeRouter($router)
    {
        $response = '';
        Event::trigger('app.route.start');
        if (is_string($router)) {
            list($class, $method) = dotToMethod($router);
            $object = Container::instance($class);
            $response = call_user_func_array(array($object, $method), array());
        }
        else if (is_array($router)) {
            $response = Router::create()->setTable($router)->route();
        }
        else if (is_callable($router)) {
            $response = $router();
        }
        Event::trigger('app.route.end');
        return $response;
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