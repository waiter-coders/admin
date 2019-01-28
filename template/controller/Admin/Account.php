<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/26
 * Time: 17:18
 */

namespace Controller\Admin;


use Waiterphp\Admin\Config\AdminList as AdminListConfig;


class Account extends \Controller\Base
{
    protected $adminConfigs = [];

    public function __construct()
    {
        parent::__construct();

        // 列表
        $adminList = new AdminListConfig($this->model('admin'));
//        $adminList->setShowFields('adminId,name,email,account');
        $adminList->setFastEdit('name');
        $adminList->setShortField('email', 12);
        $adminList->setSearch('name', 'like');
        $adminList->addTableAction('add')->setUrl('/admin/account/editor');
        $adminList->addTableAction('batchDelete')->setName('批量删除')->needSelectIds();
        $adminList->setFastAdd();
        $adminList->addRowAction('edit')->setUrl('/admin/account/editor?@primaryKey@=@data.adminId@');;
        $adminList->addRowAction('delete')->setUrl('/admin/account/delete?id=@data.adminId@');
//        $adminList->addRowAction('showTitle')->setName('标题')->setAjax()->setUrl('http://git.oschina.net/waiterall/waiterphp');
//        $adminList->setPageSize(12);
        $this->adminConfigs[] = $adminList;
    }

    protected function activeConfig()
    {
        $index = $this->request->getInt('index', 0);
        return $this->adminConfigs[$index];
    }

    protected function activeDao()
    {
        $config = $this->activeConfig();
        return call_user_func([$config, 'getDao']);
    }

    protected function formatSearch($search)
    {
        $where = [];
        $config = $this->activeConfig()->getConfig();
        foreach ($config['search'] as $row) {
            if (isset($search[$row['field']])) {
                $searchType = isset($row['searchType']) ? $row['searchType'] : '';
                switch ($searchType) {
                    case 'like':
                        $where[$row['field'] . ' like'] = '%' . $search[$row['field']] . '%';
                        break;
                    default:
                        $where[$row['field']] = $search[$row['field']];
                }
            }
        }
        return $where;
    }

    public function getConfigs()
    {
        return array_map(function($config){
            return call_user_func(array($config, 'getConfig'));
        }, $this->adminConfigs);
    }

    public function getList()
    {
        $search = $this->request->getArray('search', []);
        $limit = $this->request->getInt('limit', 15);
        $offset = $this->request->getInt('offset', 0);
        return $this->activeDao()->where($this->formatSearch($search))->limit($limit)->offset($offset)->getList();
    }

    public function getTotalNum()
    {
        $search = $this->request->getArray('search', []);
        return $this->activeDao()->where($this->formatSearch($search))->count();
    }

    public function delete()
    {
        $id = $this->request->getInt('id');
        return $this->activeDao()->deleteById($id);
    }

    public function batchDelete()
    {
        $ids = $this->request->getArray('ids');
        return $this->activeDao()->deleteByIds($ids);
    }

    public function update()
    {

    }

    public function download()
    {

    }
}