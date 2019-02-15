<?php

namespace Waiterphp\Admin\Config;


class AdminForm extends AdminBase
{
    protected $type = 'adminForm';

    private $fieldsOrder = array();
    private $fieldsMap = array();
    private $groups = array();
    private $dataId = 0;
    private $url = '';
    private $formActionMap = array();
    private $formActionOrder = array();
    private $fieldDefaultValue = array();

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

    public function getConfig()
    {
        $config =  array('type'=>$this->type);
        // 处理显示字段
        $daoFields = $this->dao->getFieldsInfo('main');
        $showFields = empty($this->fieldsOrder) ? array_keys($daoFields) : $this->fieldsOrder;
        foreach ($showFields as $field) {
            assert_exception(isset($daoFields[$field]), 'show field not exist:' . $field);
            $setFieldParam = isset($this->fieldsMap[$field]) ? $this->fieldsMap[$field] : array();
            $config['fields'][] = array_merge(array('field'=>$field), $daoFields[$field], $setFieldParam);
        }
        // 处理分组
        $config['groups'] = $this->groups;

        $config['primaryKey'] = $this->dao->primaryKey();

        // 处理action
        // 处理行操作
        if (!empty($this->formActionOrder)) {
            foreach($this->formActionOrder as $action) {
                $config['actions'][] = call_user_func(array($this->formActionMap[$action], 'getConfig'));
            }
        }
        return $config;
    }
}