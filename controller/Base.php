<?php
namespace Controller;

class Base
{
    protected function model($class)
    {
        return \Container::instance('model.' . $class);
    }

    protected function service($class)
    {
        return \Container::instance('service.' . $class);
    }

    protected function render($template, $params)
    {
        return \View::render();
    }

    protected function config($config)
    {
        return \App::current()->config()->get($config);
    }
}