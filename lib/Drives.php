<?php

class Drives
{
    public static function load($drives, $params = array())
    {
        return Instance::get('drives.'.$drives, $params);
    }
}