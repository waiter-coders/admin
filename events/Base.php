<?php
namespace Events;

abstract class Base
{
    abstract public function listen();

    protected function register($hook, $method)
    {

    }
}