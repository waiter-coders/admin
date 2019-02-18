<?php

namespace Waiterphp\Admin\Config;


class Form extends Base
{
    protected $type = 'AdminForm';

    private $fieldsOrder = [];
    private $fieldsMap = [];
    private $groups = [];
    private $dataId = 0;
    private $url = '';
    private $formActionMap = [];
    private $formActionOrder = [];
    private $fieldDefaultValue = [];

    public function __construct($dao)
    {
        parent::__construct($dao);
    }

    public function setShowFields($fields)
    {
        $fields = is_string($fields) ? explode(',', $fields) : $fields;
        $this->fieldsOrder = $fields;
    }

    public function setFile($field, $basePath, $baseUrl, $pathType = 'date')
    {
        assert_exception();
    }

    public function setImage($field, $basePath, $baseUrl, $width, $height, $pathType = 'date')
    {
        // assert_exception(isset($this->fieldsMap[$field]), 'not has show field :' . $field);
        $this->fieldsMap[$field]['type'] = 'image';
        $this->fieldsMap[$field]['basePath'] = $basePath;
        $this->fieldsMap[$field]['baseUrl'] = $baseUrl;
        $this->fieldsMap[$field]['width'] = $width;
        $this->fieldsMap[$field]['height'] = $height;
        $this->fieldsMap[$field]['pathType'] = $pathType;
    }

    public function setEditor($field, $basePath, $baseUrl, $width, $height, $pathType = 'date')
    {
        // assert_exception(isset($this->fieldsMap[$field]), 'not has show field :' . $field);
        $this->fieldsMap[$field]['type'] = 'editor';
        $this->fieldsMap[$field]['basePath'] = $basePath;
        $this->fieldsMap[$field]['baseUrl'] = $baseUrl;
        $this->fieldsMap[$field]['width'] = $width;
        $this->fieldsMap[$field]['height'] = $height;
        $this->fieldsMap[$field]['pathType'] = $pathType;
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
        $this->groups[] = ['name'=>$name, 'fields'=>$fields];
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function addAction($action)
    {
        if (!isset($this->formActionMap[$action])) {
            $this->formActionMap[$action] = new Action\Form($action);
            $this->formActionOrder[] = $action;
        }
        return $this->formActionMap[$action];
    }

    public function bindQuestion($field, $bindField)
    {
        $this->fieldsMap[$field]['question'] = $bindField;
    }

    public function setFieldDefault($key, $value)
    {
        $this->fieldDefaultValue[$key] = $value;
    }

    public function getFieldsDefault()
    {
        return $this->fieldDefaultValue;
    }

    public function getField($field)
    {
        $daoField = $this->dao->getField($field);
        $setFieldParam = isset($this->fieldsMap[$field]) ? $this->fieldsMap[$field] : [];
        return array_merge(['field'=>$field], $daoField, $setFieldParam);
    }

    public function getConfig()
    {
        $config =  ['type'=>$this->type];
        // 处理显示字段
        $daoFields = $this->dao->getFieldsInfo('main');
        $showFields = empty($this->fieldsOrder) ? array_keys($daoFields) : $this->fieldsOrder;
        foreach ($showFields as $field) {
            assert_exception(isset($daoFields[$field]), 'show field not exist:' . $field);
            $setFieldParam = isset($this->fieldsMap[$field]) ? $this->fieldsMap[$field] : [];
            $config['fields'][] = array_merge(['field'=>$field], $daoFields[$field], $setFieldParam);
        }
        // 处理分组
        $config['groups'] = $this->groups;

        $config['primaryKey'] = $this->dao->primaryKey();

        // 处理action
        // 处理行操作
        if (!empty($this->formActionOrder)) {
            foreach($this->formActionOrder as $action) {
                $config['actions'][] = call_user_func([$this->formActionMap[$action], 'getConfig']);
            }
        }
        return $config;
    }
}