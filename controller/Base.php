<?php
namespace Controller;

class Base
{
    protected function model($class)
    {
        return \Container::instance($class);
    }
}