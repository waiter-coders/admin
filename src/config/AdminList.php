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
        $this->list['fields'] = $dao->getAllFields(true);
    }


    public function addPublicAction($action)
    {
        if (!isset($this->publicActions[$action])) {
            $this->publicActions[$action] = new PublicAction($action);
        }
        return $this->publicActions[$action];
    }

    public function addItemAction($action)
    {
        if (!isset($this->itemActions[$action])) {
            $this->itemActions[$action] = new ItemAction($action);
        }
        return $this->itemActions[$action];
    }

    public function addSelectAction($action)
    {
        if (!isset($this->selectActions[$action])) {
            $this->selectActions[$action] = new SelectAction($action);
        }
        return $this->selectActions[$action];
    }

    private function checkAction($action)
    {
        if (!is_array($action) || !isset($action['type']) || !isset($action['name'])
            || !isset($action['click']) || !isset($action['url'])) {
            return false;
        }
        return true;
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

    public function getParams()
    {
        $config =  array();
        $config['search'] = $this->search;
        $config['actions'] = $this->extractActionsParams($this->publicActions);
        $config['list'] = $this->list;

        list($list, $totalNum) = array_values($this->dao->paging(
            $this->paging['current'], $this->paging['pageSize'], $this->formatSearch($this->search['fields'])));
        $list = $this->appendListActions($list, $this->itemActions);
        $config['list']['data'] = $list;
        $config['list']['hasActions'] = empty($this->itemActions) ? false : true ;
        $config['list']['fastEditFields'] = $this->list['fastEditFields'];
        $config['list']['fastEditUrl'] = AdminTools::controllerUrl().'/fieldUpdate';
        $config['paging'] = $this->expandPagingInfo($this->paging, $totalNum);
        $config['selectActions'] = $this->extractActionsParams($this->selectActions);
        if (!empty($this->selectActions)) {
            $config['list']['isCheckBox'] = true;
        }
        return $config;
    }

    private function appendListActions($list, $actions)
    {
        foreach ($list as $key=>$record) {
            $list[$key]['actions'] = $this->extractActionsParams($actions, $record);
        }
        return $list;
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

    private function expandPagingInfo($pagingInfo, $total, $sideNum = 3)
    {
        $baseUrl = Url::all();
        $preNum = max($pagingInfo['current'] - 1, 1);
        $nextNum = min($pagingInfo['current'] + 1, $total);
        $pagingInfo['total'] = $total;
        $pagingInfo['firstUrl'] = Url::refreshUrlQuery($baseUrl, array('page'=>1));
        $pagingInfo['lastUrl'] = Url::refreshUrlQuery($baseUrl, array('page'=>$total));
        $pagingInfo['preUrl'] = Url::refreshUrlQuery($baseUrl, array('page'=>$preNum));
        $pagingInfo['nextUrl'] = Url::refreshUrlQuery($baseUrl, array('page'=>$nextNum));
        $pagingInfo['pageRange'] = $this->getPageRange($total, $sideNum, $pagingInfo['current']);
        return $pagingInfo;
    }

    private function getPageRange($total, $sideNum, $current = 1)
    {
        $left = 1;
        $right = $total;
        if (2 * $sideNum + 1 < $total) { // 要显示的数字标签超过总标签时，隐去部分标签
            $leftOffset = $total - $current >= $sideNum ? $sideNum :  2 * $sideNum - ($total - $current);
            $rightOffset = $current - 1 >= $sideNum ? $sideNum : 2 *  $sideNum - ($current - 1);
            $left = $current - $leftOffset > 1 ? $current - $leftOffset : 1;
            $right = $current + $rightOffset > $total ? $total : $current + $rightOffset;
        }
        $pageNumRange = range($left, $right);

        $pageRange = array();
        foreach ($pageNumRange as $pageNum) {
            $pageRange[$pageNum] = Url::refreshUrlQuery(Url::all(), array('page'=>$pageNum));
        }
        return $pageRange;
    }
}