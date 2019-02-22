<?php
namespace Waiterphp\Admin;

trait TableTrait
{
    use BaseTrait;

    public function getList($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $search = $request->getArray('search', []);
        $limit = $request->getInt('limit', 15);
        $offset = $request->getInt('offset', 0);
        $orderBy = $request->getString('orderBy', $primaryKey . ' desc');
        return $this->dao->where($this->formatSearch($search))->orderBy($orderBy)->limit($limit)->offset($offset)->getList();
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

    public function fieldUpdate($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $request->getInt($primaryKey);
        $field = $request->getString('field');
        $value = $request->getString('value');
        return $this->dao->updateField($id, $field, $value);
    }

    public function submit($request)
    {
        $primaryKey = $this->dao->primaryKey();
        $id = $request->getInt($primaryKey, 0);
        $formData = $request->getArray('formData');
        // 新加
        if (empty($id)) {
            $defaultData = $this->config->getFieldsDefault();
            $formData = array_merge($defaultData, $formData);
            $id = $this->dao->insert($formData);
        }
        // 编辑
        else {
            $this->dao->updateById($id, $formData);
        }
        return $id;
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
                    case 'range':
                        $where[$row['field'] . ' between'] = $search[$row['field']];
                        break;
                    default:
                        $where[$row['field']] = $search[$row['field']];
                }
            }
        }
        return $where;
    }

}