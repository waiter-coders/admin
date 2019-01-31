<?php
namespace Waiterphp\Admin\Config;


class Dashboard
{
    private $configMap = array();

    public function add(AdminBase $adminConfig)
    {
        $this->configMap[] = $adminConfig;
    }

    public function get($index)
    {
        return $this->configMap[$index];
    }

    public function getconfigs()
    {
        $configs = array();
        foreach ($this->configMap as $config) {
            $configs[] = $config->getConfig();
        }
        return $configs;
    }
}