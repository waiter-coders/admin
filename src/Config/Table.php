<?php

namespace Waiterphp\Admin\Config;


class Table extends Base
{
    protected $type = 'AdminTable';

    /* 表格字段和字段属性 */
    private $fieldsOrder = [];
    private $fieldsMap = [];

    /* 搜索区块 */
    private $search = [
        'fieldsOrder'=>[],
        'fieldsMap'=>[]
    ];

    /* 表格按钮组 */
    private $tableActionsOrder = [];
    private $tableActionsMap = [];

    /* 行按钮组 */
    private $rowActionsOrder = [];
    private $rowActionsMap = [];

    /* 分页器 */
    private $paging = [
        'pageSize'=>10,
    ];

    /* id对应的详情页链接 */
    private $detailUrl = '';

    public function __construct($dao)
    {
        parent::__construct($dao);
    }

    public function setShowFields($fields)
    {
        $fields = is_string($fields) ? explode(',', $fields) : $fields;
        $this->fieldsOrder = $fields;
    }

    public function setFastEdit($field)
    {
        $this->fieldsMap[$field]['fastEdit'] = true;
    }

    public function setShortField($field, $length = 10)
    {
        $this->fieldsMap[$field]['isShort'] = $length;
    }

    public function addTableAction($action)
    {
        if (!isset($this->tableActionsMap[$action])) {
            $this->tableActionsMap[$action] = new Action\Table($action);
            $this->tableActionsMap[$action]->set('location', 'public');
            $this->tableActionsOrder[] = $action;
        }
        return $this->tableActionsMap[$action];
    }

    public function addRowAction($action)
    {
        if (!isset($this->rowActionsMap[$action])) {
            $this->rowActionsMap[$action] = new Action\Row($action);
            $this->rowActionsMap[$action]->set('location', 'item');
            $this->rowActionsOrder[] = $action;
        }
        return $this->rowActionsMap[$action];
    }

    public function setFastAdd()
    {
        $this->addTableAction('fastAdd');
    }

    public function setSearch($field, $searchType = '=')
    {
        $this->search['fieldsMap'][$field] = ['searchType'=>$searchType];
        $this->search['fieldsOrder'][] = $field;
    }

    public function setPageSize($size)
    {
        $this->paging['pageSize'] = (int)$size;
    }

    public function setDetail($url)
    {
        $this->detailUrl = $url;
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
        // 处理搜索
        if (!empty($this->search['fieldsOrder'])) {
            foreach ($this->search['fieldsOrder'] as $searchField) {
                assert_exception(isset($daoFields[$searchField]), 'search field not exist:' . $searchField);
                $config['search'][] = array_merge(['field'=>$searchField], $this->search['fieldsMap'][$searchField]);
            }
        }
        // 处理表格操作
        if (!empty($this->tableActionsOrder)) {
            foreach($this->tableActionsOrder as $action) {
                $config['tableActions'][] = call_user_func([$this->tableActionsMap[$action], 'getConfig']);
            }
        }
        // 处理行操作
        if (!empty($this->rowActionsOrder)) {
            foreach($this->rowActionsOrder as $action) {
                $config['rowActions'][] = call_user_func([$this->rowActionsMap[$action], 'getConfig']);
            }
        }
        // 处理分页
        $config['paging'] = $this->paging;

        //处理详情页链接
        $config['detail'] = $this->detailUrl;

        return $config;
    }
}



