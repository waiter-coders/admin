<?php
namespace Waiterphp\Admin\Config;

abstract class Base
{
    protected $dao;
    protected $defaultQuery = array();
    protected $fieldDefaultValue = [];
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

    public function setFieldDefault($key, $value)
    {
        $this->fieldDefaultValue[$key] = $value;
    }

    public function getFieldsDefault()
    {
        return $this->fieldDefaultValue;
    }

    abstract public function getConfig();
}