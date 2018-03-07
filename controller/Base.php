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

    protected function fetchView($template, $params)
    {
        return \View::fetch();
    }

    protected function config($config)
    {
        return \App::current()->config()->get($config);
    }

    protected function request()
    {
        return \Filter::data($_GET);
    }

    function post()
    {
        return \Filter::data($_POST);
    }
}