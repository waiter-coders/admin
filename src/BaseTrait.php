<?php
namespace Waiterphp\Admin;

use Waiterphp\Admin\Config\Base as ConfigBase;

trait BaseTrait
{
    protected $adminConfig;
    protected $adminDao;

    public function __construct()
    {
        $this->adminConfig = $this->setConfig();
        assert_exception($this->adminConfig instanceof ConfigBase, 'Config not admin config');
        $this->adminDao = $this->adminConfig->getDao();
    }

    abstract protected function setConfig();

    public function getConfig()
    {
        return $this->adminConfig->getConfig();
    }
}