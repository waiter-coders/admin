<?php

abstract class AdminTools
{
    private static $generateId = 0;

    protected $dao = '';
    protected $type = 'base';
    protected $css = array();

    public function __construct(Dao &$dao)
    {
        $this->dao = $dao;
    }

    abstract public function getConfig();

    public function setBox($width, $height)
    {
        $this->css = array_merge($this->css, array('width'=>$width, 'height'=>$height));
    }

    protected function makeName()
    {
        self::$generateId++;
        return $this->type . '_' . self::$generateId;
    }
}