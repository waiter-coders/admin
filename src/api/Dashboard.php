<?php
namespace Waiterphp\Admin\Api;

use Waiterphp\Core\FilterTrait;
use Waiterphp\Admin\Config\Dashboard as DashboardConfig;

trait Dashboard
{
    private $dashboard;

    public function __construct()
    {
        $this->dashboard = $this->dashboard();
        assert_exception($this->dashboard instanceof DashboardConfig, 'dashboard not return DashboardConfig');
    }

    abstract protected function dashboard();

    public function getConfigs()
    {
        return $this->dashboard->getConfigs();
    }

    public function query($request)
    {
        $plateId = $request->getInt('index', 0);
        $palte = $this->dashboard->get($plateId);
        assert_exception($palte != null, 'plate not set');
        $api = $this->fetchApiObject($palte);
        $action = $request->getString('action');
        return call_user_func_array(array($api, $action), array($request));
    }

    private function fetchApiObject($plate)
    {
        return instance('waiterphp.admin.api.'.$plate->getType(), $plate);
    }
}