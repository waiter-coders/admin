<?php
namespace Waiterphp\Admin;

trait TableTrait
{
    use BaseTrait;

    public function getList($request)
    {
        $search = $request->getArray('search', []);
        $limit = $request->getInt('limit', 15);
        $offset = $request->getInt('offset', 0);
        return $this->dao->where($this->formatSearch($search))->limit($limit)->offset($offset)->getList();
    }

    public function getTotalNum($request)
    {
        $search = $request->getArray('search', []);
        return $this->dao->where($this->formatSearch($search))->count();
    }

    public function delete($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $request->getInt($primaryKey);
        return $this->dao->deleteById($id);
    }

    public function batchDelete($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $ids = $request->getArray($primaryKey);
        return $this->dao->deleteByIds($ids);
    }

    public function update($request)
    {

    }

    private function formatSearch($search)
    {
        $where = [];
        $config = $this->config->getConfig();
        if (empty($search) || !isset($config['search'])) {
            return [];
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

}