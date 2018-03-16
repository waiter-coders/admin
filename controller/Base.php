<?php
namespace Controller;

class Base
{
    protected $request = null;
    protected $post = null;

    public function __construct()
    {
        $this->request = \Filter::create($_GET);
        $this->post = \Filter::create($_POST);
    }

    protected function model($class)
    {
        return \Instance::get('model.' . $class);
    }

    protected function service($class)
    {
        return \Instance::get('service.' . $class);
    }

    protected function render($template, array $params)
    {
        echo \View::get('app')->fetch($template, $params);
    }

    protected function config($config)
    {
        return \Config::instance('app')->get($config);
    }
}