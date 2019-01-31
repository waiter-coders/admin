<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 17:28
 */

namespace Waiterphp\Admin\Api;

class AdminList
{
    private $adminConfig;
    private $adminDao;

    public function __construct($adminConfig)
    {
        $this->adminConfig = $adminConfig;
        $this->adminDao = $adminConfig->getDao();
    }

    private function formatSearch($search)
    {
        $where = array();
        $config = $this->adminConfig->getConfig();
        if (empty($search) || !isset($config['search'])) {
            return array();
        }
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

    public function getList($request)
    {
        $search = $request->getArray('search', array());
        $limit = $request->getInt('limit', 15);
        $offset = $request->getInt('offset', 0);
        return $this->adminDao->where($this->formatSearch($search))->limit($limit)->offset($offset)->getList();
    }

    public function getTotalNum($request)
    {
        $search = $request->getArray('search', array());
        return $this->adminDao->where($this->formatSearch($search))->count();
    }

    public function delete($request)
    {
        $id = $request->getInt('productId');
        return $this->adminDao->deleteById($id);
    }

    public function batchDelete($request)
    {
        $ids = $request->getArray('ids');
        return $this->adminDao->deleteByIds($ids);
    }

    public function update($request)
    {

    }

    public function download()
    {

    }

}