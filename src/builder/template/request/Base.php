<?php
namespace Request;

use Waiterphp\Core\FilterTrait;

class Base
{
    public static function get()
    {
        static $instance = null;
        if (!$instance) {
            $instance = new static();
        }
        return $instance;
    }

    use FilterTrait;
}