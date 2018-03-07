<?php
namespace Model;

class Base
{
    protected function table($table, $name = 'default') // 数据库访问
    {
        return \DB::table($table, $name);
    }
}