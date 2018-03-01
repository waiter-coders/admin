<?php
/**
 * 队列
 */

abstract class Queue
{
    public static function config(array $config)
    {

    }

    public static function scanPath($path)
    {

    }

    abstract public function consumer();
    abstract public function producer();
}