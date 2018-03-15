<?php
namespace Controller;

class AdminBase extends Base
{
    protected $url = array();
    protected $menu = array();
    protected $tabs = array();

    protected $userId = array();
    protected $user = array();
    protected $power = array();

    protected $viewTemplate = array();

    public function __construct()
    {
        parent::__construct();
//        // 用户信息
//        $this->userId = $this->model('web.session')->userId();
//        $this->user = $this->model('web.session')->baseInfo();
//
//        // 权限
//        $this->power = $this->model('web.power')->getPower($this->userId);
//
//        // 菜单
//        $menu = $this->model('web.menu')->getAll();
//        $menu = $this->filterMenuByPower($menu, $this->power);
//        $currentMenu = $this->model('web.menu')->current();
//        assertOrException(!empty($menu), 'menu config not set');
//        $this->menu = array('list'=>$menu, 'current'=>$currentMenu);
//        // 分页标签
//
//        // url信息
//        list($domain, $action) = AdminTools::domainAndAction();
//        $this->url = array(
//            'base'=>\Url::baseUrl(),
//            'domain'=>$domain,
//            'action'=>$action,
//        );
    }

    private function filterMenuByPower($menu, $power)
    {
        return $menu;
    }

    public function addTab($name, $url)
    {
        $this->tabs[] = array(
            'name'=>$name,
            'url'=>$url
        );
    }

//    public function assign($key, $value, $extendType = '')
//    {
//        $value = $this->wrapReplaceTemplate($value);
//        parent::assign($key, $value, $extendType); // TODO: Change the autogenerated stub
//    }

    private function wrapReplaceTemplate($data)
    {
        if (is_string($data)) {
            return $this->replaceTemplate($data);
        } else {
            foreach ($data as $key=> $value) {
                $data[$key] = $this->wrapReplaceTemplate($value);
            }
            return $data;
        }
    }

    private function replaceTemplate($record)
    {
        $pattern = '@(\w+)@';
        while (preg_match($pattern, $record, $matches)) {
            $field = $matches[0];
            $record = str_replace('@'.$field.'@', $this->getValue($field), $record);
        }
        return $record;
    }

    protected function getValue($field)
    {
        if ($field == 'controllerUrl') {
            return \AdminTools::controllerUrl();
        }
        return $field;
    }

    public function display($template)
    {
        $this->assign('menu', $this->menu, 'json');
        $this->assign('url', $this->url, 'json');
        $this->assign('tabs', $this->tabs, 'json');

        $this->assign('userId', $this->userId, 'json');
        $this->assign('user', $this->user, 'json');
        $this->assign('power', $this->power, 'json');
        parent::display($template);
    }

    protected function isAjax($action)
    {
        return (strpos($action, 'page') === 0) ? false : true;
    }

    protected function newConfig($configName, &$dao)
    {
        $configName = ucfirst($configName).'Config';
        return new $configName($dao);
    }

    protected function paging($dao)
    {
        return new \AdminPaging($dao);
    }
}
