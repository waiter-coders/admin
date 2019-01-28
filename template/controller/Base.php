<?php
namespace Controller;

class Base
{
    protected $request;
    protected $scenes;

    public function __construct()
    {
        $this->request = new \Request\Web();
        $this->scenes =  scenes('main');
    }

    protected function model($class)
    {
        return $this->scenes->object('model.' . $class);
    }

    protected function service($class)
    {
        return $this->scenes->object('service.' . $class);
    }

    protected function behavior($behavior)
    {
        return $this->scenes->object('behavior.' . $behavior);
    }

    protected function config($config)
    {
        return $this->scenes->getConfig($config);
    }

    protected function view($viewFile, $viewParams = [])
    {
        return \Waiterphp\Core\View::fetch($viewFile, $viewParams);
    }
}