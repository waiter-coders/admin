<?php
namespace Controller;

class Base
{
    protected function model($class)
    {
        return \Instance::get('model.' . $class);
    }

    protected function service($class)
    {
        return \Instance::get('service.' . $class);
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