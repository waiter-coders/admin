<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin\Api;


trait AdminList
{
    abstract protected function requestConfig();

    protected function newAdminListConfig($dao)
    {
        return new \Waiterphp\Admin\Config\AdminList($dao);
    }


}