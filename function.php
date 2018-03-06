<?php
// 语法糖
function table($table, $name = 'default') // 数据库访问
{
    return DB::table($table, $name);
}

function config($search) // 配置文件访问
{
    return Config::get($search);
}

function service($class)
{
    return Container::instance('service.'.$class);
}

function check_error($boolean, $errorMessage)
{
    if (!$boolean) {
        throw new Exception($errorMessage);
    }
}

function request()
{
    return Request::data('get');
}

function post()
{
    return Request::data('post');
}

function cli()
{
    return Request::data('cli');
}

function lowerToUpper($class)
{

}