<?php
namespace Controller;

class AdminBase extends Base
{
    protected $domain = null;

    public function canUseDomains()
    {
        return array(
            array('domain'=>'user', 'name'=>'用户管理', 'children'=>array(
                array('domain'=>'account', 'name'=>'用户账号'),
            )),
            array('domain'=>'record', 'name'=>'数据记录', 'children'=>array(
                array('domain'=>'simpleList', 'name'=>'简单列表'),
                array('domain'=>'simpleForm', 'name'=>'简单表单'),
            )),
        );
    }

    public function hasPower()
    {
        return true;
    }

    public function action()
    {

    }


}
