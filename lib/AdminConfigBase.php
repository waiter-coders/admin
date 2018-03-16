<?php

abstract class AdminConfigBase
{
    protected $hiddenFields = array();
    protected $disableFields = array();
    protected $tabs = array();
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

    public function getConfigs()
    {
        $config = get_object_vars($this);
        unset($config['daoConfig'], $config['hiddenFields']);
        $config['primaryKey'] = $this->dao->primaryKey();

        // 过滤掉Hidden的字符
        $config['fields'] = array_diff($this->dao->getAllFields(), $this->hiddenFields);
        $config['fieldKeys'] = $config['fields'];

        // 字段处理
        foreach ($config['fields'] as $field) {
            $param = $this->dao->getFieldInfo($field);
            if (isset(self::$daoTypeMap[$param['type']])) {
                $config['fields'][$field]['type'] = self::$daoTypeMap[$param['type']];
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

    public function setShowType($field, $type)
    {
        $this->showTypeMap[$field] = $type;
    }

    public function hidden($fields)
    {
        $fields = func_get_args();
        $fields = implode(',', $fields);
        $fields = explode(',', $fields);
        $this->hiddenFields = array_merge($this->hiddenFields, $fields);
    }

    public function disable($fields)
    {
        $fields = func_get_args();
        $fields = implode(',', $fields);
        $fields = explode(',', $fields);
        $this->disableFileds = array_merge($this->disableFields, $fields);
    }

    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
    }
}

$assign = array(
    'config'=>array(
        'fields'=>array(
            'articleId'=>array('name'=>'文章id'),
            'title'=>array('name'=>'标题', ),
            'brief'=>array('name'=>'简介'),
        ),
        'search'=>array('title'),
        'paging'=>array(),
        'tableHeader'=>array(),
        'select'=>array(),
        'brief'=>array(),
        'actionsPublic'=>array(),
        'actionsItem'=>array(),
    ),
    'data'=>array(
        'list'=>array(
            array('articleId'=>1, 'title'=>'测试', 'brief'=>'我的视图'),
            array('articleId'=>2, 'title'=>'测试', 'brief'=>'我的视图'),
            array('articleId'=>3, 'title'=>'测试', 'brief'=>'我的视图'),
        ),
        'totalNum'=>'',

    ),
);