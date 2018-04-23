<?php

namespace Controller;


class AdminBase extends Base
{
    protected function canUseDomains()
    {
        return array(
            array('domain'=>'user', 'name'=>'用户管理', ),
            array('domain'=>'user.account', 'name'=>'用户账号'),
            array('domain'=>'record', 'name'=>'数据记录'),
            array('domain'=>'record.simpleList', 'name'=>'简单列表'),
            array('domain'=>'record.simpleForm', 'name'=>'简单表单'),
        );
    }

    protected function hasPower()
    {
        return true;
    }

}