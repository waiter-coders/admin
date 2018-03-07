<?php
class AdminLayout extends AdminBase
{
    public function user()
    {

    }

    public function menu($classify, $name)
    {
        return Menu::classify($classify, $name);
    }

    public function tabs()
    {

    }
}



class Menu
{
    private static $classify = array();

    public static function classify($classify, $name, $icon='home')
    {
        if(!isset(self::$classify[$classify])){
            self::$classify[$classify] = new MenuClassify(array(
                'domain'   =>  $classify,
                'name'  =>  $name,
                'icon'  =>  $icon,
            ));
        }
        return self::$classify[$classify];
    }

    public static function allClassify()
    {
        $config = array();
        foreach (self::$classify as $menuClassify) {
            $config[] = $menuClassify->getConfig();
        }
        return $config;
    }

    public static  function current(){
        $request = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
        $requestArr = explode('/', $request);
        if (count($requestArr) < 2) {
            return false;
        }
        $menu['top'] = $requestArr[0];
        $menu['sub'] = $requestArr[0] . '/' . $requestArr[1];
        return $menu;
    }
}

class MenuClassify
{
    private $classify = array();
    private $items = array();
    public function __construct($config)
    {
        $this->classify = $config;
    }

    public function item($item, $name = '', $action = 'pageHome')
    {
        $this->items[] = array(
            'domain'   =>  $this->classify['domain'] . '/' . $item,
            'name'  =>  $name,
            'url'=>$this->classify['domain'] . '/' . $item . '/' . $action,
        );
        return $this;
    }

    public function getConfig()
    {
        $config = $this->classify;
        $config['subMenus'] = $this->items;
        return $config;
    }
}