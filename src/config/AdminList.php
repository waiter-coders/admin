<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin\Config;


class AdminList extends AdminBase
{
    protected $type = 'adminList';

    /* 表格字段和字段属性 */
    private $fieldsOrder = array();
    private $fieldsMap = array();

    /* 搜索区块 */
    private $search = array(
        'fieldsOrder'=>array(),
        'fieldsMap'=>array()
    );

    /* 表格按钮组 */
    private $tableActionsOrder = array();
    private $tableActionsMap = array();

    /* 行按钮组 */
    private $rowActionsOrder = array();
    private $rowActionsMap = array();

    /* 分页器 */
    private $paging = array(
        'pageSize'=>10,
    );


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
        $this->search['fieldsMap'][$field] = array('searchType'=>$searchType);
        $this->search['fieldsOrder'][] = $field;
    }

    public function setPageSize($size)
    {
        $this->paging['pageSize'] = (int)$size;
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
        // 处理搜索
        if (!empty($this->search['fieldsOrder'])) {
            foreach ($this->search['fieldsOrder'] as $searchField) {
                assert_exception(isset($daoFields[$searchField]), 'search field not exist:' . $searchField);
                $config['search'][] = array_merge(array('field'=>$searchField), $this->search['fieldsMap'][$searchField]);
            }
        }
        // 处理表格操作
        if (!empty($this->tableActionsOrder)) {
            foreach($this->tableActionsOrder as $action) {
                $config['tableActions'][] = call_user_func(array($this->tableActionsMap[$action], 'getConfig'));
            }
        }
        // 处理行操作
        if (!empty($this->rowActionsOrder)) {
            foreach($this->rowActionsOrder as $action) {
                $config['rowActions'][] = call_user_func(array($this->rowActionsMap[$action], 'getConfig'));
            }
        }
        // 处理分页
        $config['paging'] = $this->paging;
        return $config;
    }
}



