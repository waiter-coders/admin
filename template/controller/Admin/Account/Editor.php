<?php

namespace Controller\Admin\Account;
use Waiterphp\Admin\Config\AdminForm;

class Editor extends \Controller\Base
{
    protected $adminConfigs = [];

    public function __construct()
    {
        parent::__construct();

        // 表单
        $adminForm = new AdminForm($this->model('admin'));
        $adminForm->setRatio('sex');
        $adminForm->setShowFields('account,name,sex,birthday');

        $this->adminConfigs[] = $adminForm;
    }
    public function getConfigs()
    {
        return array_map(function($config){
            return call_user_func(array($config, 'getConfig'));
        }, $this->adminConfigs);
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

    public function getFormData()
    {
        $id = $this->request->getInt('id');
        return $this->activeDao()->infoById($id);
    }

    public function formSubmit()
    {
        $activeDao = $this->activeDao();
        $id = $this->request->getInt($activeDao->primaryKey(), 0);
        $formData = $this->request->getArray('formData');
        // 新加
        if (empty($id)) {
            $id = $activeDao->insert($formData);
        }
        // 编辑
        else {
            $id = $formData[$activeDao->primaryKey()];
            $activeDao->updateById($id, $formData);
        }
        return $id;
    }

    public function formUpload()
    {

    }

    public function formCheck()
    {

    }
}