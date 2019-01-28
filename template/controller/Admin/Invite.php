<?php
namespace Controller\Admin;

use Waiterphp\Admin\Config\AdminForm;

class Invite extends \Controller\Base
{
    protected $adminConfigs = [];

    public function __construct()
    {
        parent::__construct();

        // 表单
        $adminForm = new AdminForm($this->model('adminInvite'));
        $adminForm->setRatio('sex');

        $this->adminConfigs[] = $adminForm;
    }
    public function getConfigs()
    {
        return array_map(function($config){
            return call_user_func(array($config, 'getConfig'));
        }, $this->adminConfigs);
    }

    public function formSubmit()
    {
        $config = $this->getConfigs();

    }

    public function formUpload()
    {

    }

    public function formCheck()
    {

    }
}