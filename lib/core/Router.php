<?php
class Router
{
    public static function create()
    {
        return new RouterInstance();
    }

    public static function load($file)
    {

    }
}

class RouterInstance
{
    private $routeTable = array();

    public function setTable($routeTable)
    {
        $this->routeTable = $routeTable;
        return $this;
    }

    public function group()
    {
        return $this;
    }

    public function route($signal = null)
    {
        $routeTarget = $this->target($signal);
        return $this->routeTo($routeTarget);
    }

    public function target($signal = null)
    {
        $signal = $this->parseSignal($signal);
        return $this->searchTarget($this->routeTable, $signal);
    }

    private function fetchUrlSignal()
    {
        return isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
    }

    private function searchTarget($routes, $signal)
    {
        foreach ($routes as $route) {
            assertOrException(isset($route[0]) || isset($route['url']), 'route not set');
            $pattern = isset($route[0]) ? $route[0] : $route['url'];
            if (preg_match($this->formatPattern($pattern), $signal, $matches)) {
                return $this->generateCmd($route[1], $matches);
            }
        }
        return false;
    }

    private function formatPattern($pattern)
    {
        return "/" . str_replace('/', '\/', $pattern) . '/i';
    }

    private function generateCmd($action, $matches)
    {
        array_shift($matches);
        foreach ($matches as $key=>$match) {
            $action = str_replace('$'.$key, $match, $action);
        }
        return $action;
    }

    private function routeTo($action)
    {
        // 转化为可执行对象
        $api = explode('.', $action);
        $function = array_pop($api);
        $class = implode('\\', $api);
        assertOrException(!empty($class) && !empty($function), 'call api error:'.json_encode($api));
        $params = array();
        if (func_num_args() > 1) {
            $params = func_get_args();
            array_shift($params);
        }
        $object = new $class();
        $response = call_user_func_array(array($object, $function), $params);
        return $response;
    }

    private function parseSignal($signal)
    {
        if (is_callable($signal)) {
            return $signal();
        }
        if (empty($signal)) {
            return $this->fetchUrlSignal();
        }
        return $signal;
    }
}