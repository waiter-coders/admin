<?php
class Router
{
    public static function create()
    {
        return new RouterInstance();
    }
}

class RouterInstance
{
    private $routeTable = null;

    public function group()
    {
        return $this;
    }

    public function route($signal = null)
    {
        $signal = $this->parseSignal($signal);
        $routeTarget = $this->searchTarget($this->routeTable, $signal);
        $this->routeTo($routeTarget);

    }

    private function fetchUrlSignal()
    {
        return isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
    }

    private function searchTarget($routes, $signal)
    {
        foreach ($routes as $route) {
            if (!isset($route[0]) && !isset($route['url'])) {
                throw new \Exception('route not set');
            }
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
        if (empty($class) || empty($function)) {
            throw new Exception('call api error:'.json_encode($api));
        }
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