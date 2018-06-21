<?php
namespace Waiterphp\Admin\Api;

trait AdminApi
{
    protected $adminConfigs = array();

    abstract public function getConfigs();

    protected function addAdminConfig(\Waiterphp\Admin\Config\AdminBase $config)
    {
        $this->adminConfigs[] = $config;
    }


    // 对外接口
    protected function getAdminConfigs()
    {
        $response = array();
        foreach ($this->adminConfigs as $pageConfig) {
            $response[] = $pageConfig->toArray();
        }
        return $response;
    }
}