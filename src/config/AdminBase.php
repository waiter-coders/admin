<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:41
 */

namespace Waiterphp\Admin\Config;


class AdminBase
{
    protected $dao;

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function getDao()
    {
        return $this->dao;
    }
}