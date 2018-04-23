<?php
class AdminList extends AdminTools
{
    protected $type = 'list';

    /* 搜索区块 */
    private $search = array(
        'url'=>'', // 搜索操作的url
        'fields'=>array(),
        'order'=>'',
    );

    /* 公共操作按钮 */
    private $publicActions = array();
    private $itemActions = array();
    private $selectActions = array();

    /* 列表 */
    private $list = array(
        'idField'=>'',
        'fields'=>array(),
        'actions'=>array(),
        'data'=>array(),
        'isCheckBox'=>false,
        'fastEditFields'=>array(),
        'fastAdd'=>false,
    );

    private $short = array();

    /* 分页器 */
    private $paging = array(
        'current'=>1,
        'pageSize'=>12,
    );


    public function __construct(Dao $dao)
    {
        parent::__construct($dao);
        $this->list['idField'] = $dao->primaryKey();
        $this->list['fields'] = $dao->getAllFields();
    }


    public function addPublicAction($action)
    {
        assertOrException(!isset($this->publicActions[$action]), 'public action is set:' . $action);
        return $this->publicActions[$action] = new PublicAction($action);
    }

    public function addItemAction($action)
    {
        assertOrException(!isset($this->itemActions[$action]), 'item action is set:' . $action);
        return $this->itemActions[$action] = new ItemAction($action);
    }

    public function addSelectAction($action)
    {
        if (!isset($this->selectActions[$action])) {
            $this->selectActions[$action] = new SelectAction($action);
        }
        return $this->selectActions[$action];
    }


    public function short($field, $length = 10)
    {
        $this->short[$field] = $length;
    }

    public function setPageNum($pageNum)
    {
        $this->paging['current'] = (int)$pageNum;
    }

    public function setPageSize($size)
    {
        $this->paging['pageSize'] = (int)$size;
    }

    public function setSearch($field, $value = '', $searchType = 'eq')
    {
        $fields = $this->dao->getAllFields(true);
        if (!isset($fields[$field])) {
            throw new Exception('search field not exist');
        }
        $fields[$field]['value'] = $value;
        $fields[$field]['searchType'] = $searchType;
        $this->search['fields'][$field] = $fields[$field];
    }

    public function setFastEdit($field, $options = array())
    {
        $this->list['fastEditFields'][$field] = $options;
    }

    public function setFastAdd()
    {
        $this->list['fastAdd'] = true;
        $this->addPublicAction('fastAdd');
    }

    public function getConfig()
    {
        $config =  array();
        $config['type'] = $this->type;
        $config['name'] = $this->makeName();
        $config['search'] = $this->search;
        $config['fields'] = $this->dao->getMainFields();
        $config['publicActions'] = $this->extractActionsParams($this->publicActions);
        $config['itemActions'] = $this->extractActionsParams($this->itemActions);
        $config['selectActions'] = $this->extractActionsParams($this->selectActions);
        return $config;
    }

    private function extractActionsParams($actions, $record = '')
    {
        $actionsParams = array();
        $primaryKey = $this->list['idField'];
        foreach ($actions as $name=>$action) {
            $params = $action->getParams($record);
            foreach ($params as $key=>$value) {
                $id = empty($record) ? '' : $record[$primaryKey];
                $value = str_replace('@data.id@', $id, $value);
                $value = str_replace('@primaryKey@', $primaryKey, $value);
                $actionsParams[$name][$key] = $value;
            }
        }
        return $actionsParams;
    }

    public function fetchSearchFromUrl($fields)
    {
        $search = array();
        foreach ($fields as $field) {
            $param = $this->daoConfig->fields[$field];
            $type = $param['type'];
            if ($type == 'int' || $type == 'enum') {
                $condition = $this->request->getInt($field, 0);
            } else {
                $condition = $this->request->getString($field, '');
            }
            if (!empty($condition)) {
                $search[$field] = $condition;
            }
        }
        return $search;
    }

    private function formatSearch(array $search)
    {
        $where = array();
        foreach ($search as $field=>$params) {
            $value = $params['value'];
            if (empty($value)) {
                continue;
            }
            // 模糊匹配的搜索方式
            if ($this->search['fields'][$field]['searchType'] == 'like') {
                $where[$field . ' like'] = '%'.$value.'%';
                continue;
            }
            $where[$field] = $value;
        }
        return $where;
    }
}

class AdminPagingAction
{
    protected $disabledCallback;
    protected $params = array(
        'type'=>'item',
        'click'=>'page',
        'isShow'=>true,
        'isDisabled'=>false,
        'url'=>'',
        'name'=>'',
        'message'=>'',
    );

    public function __construct($action = '')
    {
        $defaultActions = $this->defaultActions();
        if (isset($defaultActions[$action])) {
            $this->params = array_merge($this->params, $defaultActions[$action]);
        } else {
            $this->params['name'] = $action;
            $this->params['url'] = AdminTools::controllerUrl().'/'.$action.'/?@primaryKey@=@data.id@';
        }
    }

    public function appendParams($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function setDisabled(callable $callback)
    {
        $this->disabledCallback = $callback;
        return $this;
    }

    public function setHidden(callable $callback)
    {
        $this->hiddenCallback = $callback;
        return $this;
    }

    public function setName($name)
    {
        $this->params['name'] = $name;
        return $this;
    }

    public function setUrl($url)
    {
        $url = AdminTools::baseUrl() . $url;
        $this->params['url'] = $url;
        return $this;
    }

    public function setAjax($message = '')
    {
        $this->params['click'] = 'ajax';
        $this->params['message'] = $message;
        return $this;
    }

    public function getParams($record = '')
    {
        if (!empty($record) && !empty($this->disabledCallback)) {
            $this->params['disabled'] = call_user_func($this->disabledCallback, $record);
        }
        if (!empty($record) && !empty($this->hiddenCallback)) {
            $this->params['isShow'] = !call_user_func($this->hiddenCallback, $record);
        }
        return $this->params;
    }

    protected function defaultActions()
    {
        return array();
    }

}

class PublicAction extends AdminPagingAction
{

    protected function defaultActions()
    {
        return array(
            'add'=>array('type'=>'public', 'name'=>'添加', 'isShow'=>true, 'isDisabled'=>false, 'click'=>'page'),
            'fastAdd'=>array('type'=>'public', 'name'=>'快速添加', 'isShow'=>true, 'isDisabled'=>false, 'click'=>'ajax', 'url'=>AdminTools::controllerUrl().'/fastAdd'),
            'batchAdd'=>array('type'=>'public', 'name'=>'批量添加', 'isShow'=>true, 'isDisabled'=>false, 'click'=>'ajax', 'url'=>AdminTools::controllerUrl().'/batchAdd')
        );
    }
}

class ItemAction extends AdminPagingAction
{
    protected function defaultActions()
    {
        return array(
            'edit'=>array('type'=>'item', 'name'=>'编辑', 'click'=>'page'),
            'delete'=>array('type'=>'item', 'name'=>'删除', 'click'=>'ajax', 'message'=>'您确定要删除@data.id@吗？', 'url'=>AdminTools::controllerUrl().'/delete/?@primaryKey@=@data.id@'),
            'audit'=>array('type'=>'item', 'name'=>'审核', 'click'=>'dialog', 'url'=>AdminTools::controllerUrl().'/audit'),
        );
    }
}

class SelectAction extends AdminPagingAction
{

}