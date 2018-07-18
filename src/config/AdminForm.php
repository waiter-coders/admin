<?php

namespace Waiterphp\Admin\Config;


class AdminForm extends AdminBase
{
    private $type = 'admin-form';

    private $fieldsOrder = array();
    private $fieldsMap = array();
    private $groups = array();
    private $dataId = 0;
    private $url = '';

    public function __construct($dao)
    {
        parent::__construct($dao);
    }

    public function setShowFields($fields)
    {
        $fields = is_string($fields) ? explode(',', $fields) : $fields;
        $this->fieldsOrder = $fields;
    }

    public function setRatio($field)
    {
        $this->fieldsMap[$field]['showType'] = 'radio';
    }

    public function setDataId($dataId)
    {
        $this->dataId = $dataId;
    }

    public function group($name, $fields)
    {
        $fields = func_get_args();
        $name = array_pop($fields);
        $this->groups[] = array('name'=>$name, 'fields'=>$fields);
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getConfig()
    {
        $config =  array('type'=>$this->type);
        // 处理显示字段
        $daoFields = $this->dao->getFieldsInfo('main');
        $showFields = empty($this->fieldsOrder) ? array_keys($daoFields) : $this->fieldsOrder;
        foreach ($showFields as $field) {
            assertOrException(isset($daoFields[$field]), 'show field not exist:' . $field);
            $setFieldParam = isset($this->fieldsMap[$field]) ? $this->fieldsMap[$field] : array();
            $config['fields'][] = array_merge(array('field'=>$field), $daoFields[$field], $setFieldParam);
        }
        // 处理分组
        $config['groups'] = $this->groups;

        $config['primaryKey'] = $this->dao->primaryKey();

        // 处理submit地址
        $config['url'] = $this->url;
        return $config;
    }
}