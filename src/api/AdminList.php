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
    protected function newAdminListConfig($dao)
    {
        return new \Waiterphp\Admin\Config\AdminList($dao);
    }

    public function getAdminListData()
    {

    }

    public function getAdminListTotalNum()
    {

    }

    public function deleteAdminListRecords()
    {

    }

    public function updateAdminListRecords()
    {

    }

    public function downloadAdminListRecords()
    {

    }
}