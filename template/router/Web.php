<?php
namespace Router;

class Web
{

    public function targetAction()
    {
        $path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
        $route = !empty($path) ? 'controller.' . str_replace('/', '.', $path) : 'controller.home.show';
        return $route;
    }
}