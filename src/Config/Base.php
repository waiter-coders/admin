<?php
namespace Waiterphp\Admin\Config;

abstract class Base
{
    protected $dao;
    protected $defaultQuery = array();
    protected $type = 'base';

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function getDao()
    {
        return $this->dao;
    }

    public function setDefaultQuery($condition)
    {
        $this->dao->setDefaultQuery($condition);
    }

    public function getType()
    {
        return $this->type;
    }

    abstract public function getConfig();
}