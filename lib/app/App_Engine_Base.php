<?php
class App_Engine_Base
{
    protected $sets = array();

    protected $config = array();
    protected $isInit = false;
    protected $response = null;

    public function __construct()
    {
        $this->sets = $this->defaultSets();
    }

    public function set($key, $value)
    {
        $this->sets[$key] = $value;
        return $this;
    }

    public function get($key)
    {
        return isset($this->sets[$key]) ? $this->sets[$key] : null;
    }

    public function bind($event, $method)
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
        assertOrException(is_file($routeFile), 'route file not exist:' . $routeFile);
        $this->init();
        $router = require $routeFile;
        $this->response = $this->executeRouter($router);
        return $this;
    }

    public function response(callable $callback)
    {
        $callback($this->response);
        return $this;
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


            // 初始化数据库
            $database = $this->config->get('database');
            assertOrException(!empty($database), 'database not set');
            DB::register($database);

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
            $object = Instance::get($class);
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

    private function defaultSets()
    {
        $appPath = realpath(dirname(dirname(__DIR__)));
        return array(
            'path.config'=> $appPath . '/config',
            'path.routes'=> $appPath . '/routes',
            'path.controller'=> $appPath . '/controller',
            'path.events'=> $appPath . '/events',
            'path.model'=> $appPath . '/model',
            'path.service'=> $appPath . '/service',
            'path.views.compile'=> $appPath . '/storage/views',
            'path.queue.consumer'=>$appPath . '/consumer',
            'path.shell'=> $appPath . '/shell',
            'path.session'=> $appPath . '/storage/session',
            'path.logs'=> $appPath . '/storage/logs',
            'path.drives'=> $appPath . '/drives',
            'path.loader'=>array(),
        );
    }
}