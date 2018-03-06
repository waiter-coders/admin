<?php
class FormConfig extends AdminConfigBase
{
    private $fieldsParams = array();
    private $selectLink = array();
    private $selectLinkCall = array();

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

    public function toTextArea($idField)
    {
        $this->fieldsParams[$idField] = array('type'=>'textarea');
    }

    public function toCheckBox($idField)
    {
        $this->fieldsParams[$idField] = array('type'=>'check');
    }

    public function toRadio($idField)
    {
        $this->fieldsParams[$idField] = array('type'=>'radio');
    }

    public function toEditor($idField)
    {
        $this->fieldsParams[$idField] = array('type'=>'editor');
    }

    public function toDataList($idField)
    {
        $this->fieldsParams[$idField] = array('type'=>'datalist');
    }

    public function group()
    {
        'fieldset '.'legend ';
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

    public function getConfigs()
    {
        $configs = parent::getConfigs();
        foreach ($configs['fields'] as $field=>$param) {
            if (isset($this->fieldsParams[$field])) {
                $configs['fields'][$field] = array_merge($param, $this->fieldsParams[$field]);
            }
        }
        $configs['selectLink'] = $this->selectLink;
        $configs['selectLinkCall'] = $this->selectLinkCall;
        return $configs;
    }
}