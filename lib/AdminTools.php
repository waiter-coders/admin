<?php

abstract class AdminTools
{
    /*  数据源  */
    protected $dao = '';
    protected $daoConfig = array();
    private static $daoTypeMap = array(
        'int'=>'input',
        'enum'=>'select',
        'text'=>'html',
        'image'=>'image',
        'varchar'=>'input',
    );
    private $showTypeMap = array();

    public function __construct(Dao &$dao)
    {
        $this->dao = $dao;
    }

    public function getParams()
    {
        $config = array();
        $config['dao']= $this->getDaoConfig();
        return $config;
    }

    public function setShowType($field, $type)
    {
        $this->showTypeMap[$field] = $type;
    }



    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
    }

    public function getDaoConfig()
    {
        $config = array();
        $config['primaryKey'] = $this->dao->primaryKey();

        // 获取字段信息
        $config['fields'] = $this->dao->getMainFields(true);
        $config['fieldKeys'] = array_keys($config['fields']);

        // 字段处理
        foreach ($config['fieldKeys'] as $field) {
            if (isset(self::$daoTypeMap[$config['fields'][$field]['type']])) {
                $config['fields'][$field]['type'] = self::$daoTypeMap[$config['fields'][$field]['type']];
            }
            if (isset($this->showTypeMap[$field])) {
                $config['fields'][$field]['type'] = $this->showTypeMap[$field];
            }
            if (!isset($config['fields'][$field]['type'])) {
                $config['fields'][$field]['type'] = 'input';
            }
            if (isset($param['enum'])) {
                $config['fields'][$field]['options'] = $param['enum'];
                unset($config['fields'][$field]['enum']);
            }
        }
        return $config;
    }


    public static function domainUrl()
    {
        list($domain, $action) = self::domainAndAction();
        return Http::url() . '/' . $domain;
    }

    public static function baseUrl()
    {
        return Http::url() . '/';
    }

    public static function controllerUrl()
    {
        list($domain, $controller, $action) = self::domainAndAction();
        return Http::url() . '/' . $domain . '/' . $controller;
    }

    public static function domainAndAction()
    {
        $request = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
        if (empty($request)) {
            return array('/', 'Home', 'show');
        }
        $split = explode('/', $request);
        $action = array_pop($split);
        $controller = array_pop($split);
        $domain = implode('/', $split);
        return array($domain, $controller, $action);
    }
}