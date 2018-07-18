<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin\Api;


trait AdminForm
{
    abstract protected function requestConfig();

    protected function newAdminFormConfig($dao)
    {
        return new \Waiterphp\Admin\Config\AdminForm($dao);
    }

    public function submit()
    {
        $config = $this->requestConfig();

    }

    public function formUpload()
    {

    }
}