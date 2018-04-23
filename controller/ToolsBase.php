<?php
namespace Controller;

class ToolsBase extends AdminBase
{
    protected $domain = null;
    protected $dao = null;
    protected $adminTools = array();

    public function getConfig()
    {
        $configs = array();
        foreach ($this->adminTools as $adminTools) {
            $configs[] = $adminTools->getConfig();
        }
        return $configs;
    }

    // 行为解析接口，提供另一种接口的访问方式
    public function action()
    {
        $action = $this->post->getString('action');
        assertOrException(!empty($action) && method_exists($this, $action), 'action error');
        $ids = $this->post->getArray('ids', array());
        $data = $this->post->getArray('data', array());
        return call_user_func(array($this, $action), $ids, $data);
    }
}
