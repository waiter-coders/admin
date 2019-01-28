<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/15
 * Time: 10:53
 */

namespace Response;


class View
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function output()
    {
        return 'view';
    }

}