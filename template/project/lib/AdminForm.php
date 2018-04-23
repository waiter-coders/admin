<?php
class AdminForm extends AdminTools
{
    private $fieldsParams = array();
    private $selectLink = array();
    private $selectLinkCall = array();
    private $inputUpperLimit = 50;
    private $dataId = 0;
    private $groups = array();
    private $postUrl = '';

    public function linkSelect($mainSelect, $linkSelect, $defaultMessage, callable $dataCall)
    {
        $this->selectLink[] = array(
            'mainSelect'=>$mainSelect,
            'linkSelect'=>$linkSelect,
            'tip'=>$defaultMessage,
        );
        $this->selectLinkCall[$linkSelect] = $dataCall;
    }

    public function toSelect($idField, $nameField)
    {
        $this->fieldsParams[$idField] = array('type'=>'select', 'nameId'=>$nameField, 'options'=>array());
        $this->hidden($nameField);
    }

    public function toEmail($idField)
    {

    }

    public function toUpload($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'upload';
        $this->fieldsParams[$idField]['object'] = new AdminUpload($idField);
        return $this->fieldsParams[$idField]['object'];
    }

    public function toTextArea($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'textarea';
    }

    public function toCheckBox($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'check';
    }

    public function toRadio($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'radio';
    }

    public function toEditor($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'editor';
    }

    public function toDataList($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'datalist';
    }

    public function toPassword($idField)
    {
        $this->fieldsParams[$idField]['type'] = 'password';
    }

    public function group()
    {
        $fields = func_get_args();
        $this->groups[] = $fields;
    }

    public function submitCheck()
    {
        return true;
    }


    public function disabled()
    {

    }

    public function readonly()
    {

    }

    public function setId($id)
    {
        if (!empty($id)) {
            $this->dataId = $id;
        }
    }

    public function setPostUrl($url)
    {
        $this->postUrl = $url;
    }

    public function setInputUpperLimit($upperLimit)
    {
        $this->inputUpperLimit = $upperLimit;
    }

    public function getConfig()
    {
        $data = array();
        if (!empty($this->dataId)) {
            $data = $this->dao->infoById($this->dataId);
        }
        $fields = array();
        $hasDate = false;
        $hasEditor = false;
        $fieldsInfo = $this->dao->getAllFields(true);
        $fieldsFilters = $this->dao->getFilters();
        foreach ($fieldsInfo as $field=>$param) {
            if ($field == $this->dao->primaryKey()) {
                continue;
            }
            if ($this->dao->isReadonly($field)) { echo 11;
                continue;
            }
            $fields[$field]['name'] = isset($param['name']) ? $param['name'] : $field;
            $fields[$field]['type'] = $this->getDefaultFormType($param);
            $fields[$field]['value'] = isset($data[$field]) ? $data[$field] : '';
            $fields[$field]['filter'] = isset($fieldsFilters[$field]) ? $fieldsFilters[$field] : array();
            if (isset($this->fieldsParams[$field])) {
                $fields[$field] = array_merge($fields[$field], $this->fieldsParams[$field]);
            }
            if ($fields[$field]['type'] == 'html') {
                $hasEditor = true;
            }
            if (in_array($fields[$field]['type'], array('date', 'datetime', 'time'))) {
                $hasDate = true;
            }
        }

        $groups = array();
        foreach ($this->groups as $group) {
            $groups[]['fields'] = $this->extractFields($group, $fields);
        }
        $groups[]['fields'] = $this->extractFields(array_keys($fields), $fields);

        if (!empty($data)) {
            $url = AdminTools::controllerUrl() . '/update' . '?' . $this->dao->primaryKey() . '=' . $data[$this->dao->primaryKey()];
        }else {
            $url = AdminTools::controllerUrl() . '/Add';
        }
       
       return array(
           'url'=>$url,
           'groups'=>$groups,
           'hasDate'=>$hasDate,
           'hasEditor'=>$hasEditor,
       );
    }


    private function getDefaultFormType($param)
    {
        $type = $param['type'];
        $daoTypeMap = array(
            'int'=>'input',
            'tinyint'=>'input',
            'smallint'=>'input',
            'varchar'=>'input',
            'text'=>'textarea',
            'html'=>'html',
            'datetime'=>'datetime',
            'timestamp'=>'datetime'
        );
        if (!isset($daoTypeMap[$param['type']])) {
            throw new Exception('ERROR TYPE :'.$type);
        }
        $type = $daoTypeMap[$param['type']];
        if ($type == 'input' && isset($param['length']) && $param['length'] > $this->inputUpperLimit) {
            $type = 'textarea';
        }
        return $type;
    }


    public function extractFields($group, &$fields)
    {
        $item = array();
        foreach ($group as $field) {
            if (!isset($fields[$field])) {
                throw new Exception('group field error');
            }
            $item[$field] = $fields[$field];
            unset($fields[$field]);
        }
        return $item;
    }


}