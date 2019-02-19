<?php
namespace Waiterphp\Admin;

use Waiterphp\Admin\Config\Base as ConfigBase;

trait BaseTrait
{
    protected $config;
    protected $dao;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->config = $this->setConfig();
        assert_exception($this->config instanceof ConfigBase, 'Config not admin config');
        $this->dao = $this->config->getDao();
    }

    abstract protected function setConfig();

    public function getConfig()
    {
        return $this->config->getConfig();
    }
}