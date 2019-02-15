<?php
namespace Waiterphp\Admin\Api;

use Waiterphp\Core\FilterTrait;
use Waiterphp\Admin\Config\Dashboard as DashboardConfig;

trait Dashboard
{
    abstract protected function dashboard($request);

    public function getConfigs(Request $request)
    {
        $dashboard = $this->dashboard($request);
        return $dashboard->getConfigs();
    }

    public function query($request)
    {
        $plateId = $request->getInt('index', 0);
        $action = $request->getString('action');
        $dashboard = $this->dashboard($request);
        $plate = $dashboard->get($plateId);
        assertOrException($plate != null, 'plate not set');
        $apiObject = instance('tools.admin.api.'.$plate->getType(), $plate);        
        return call_user_func_array(array($apiObject, $action), array($request));
    }
}