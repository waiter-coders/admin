<?php
// 语法糖
function table($table, $name = 'default') // 数据库访问
{
    return DB::table($table, $name);
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