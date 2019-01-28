<?php

namespace Waiterphp\Admin\Builder\Behaviors;

use Waiterphp\Core\Builder\BuilderInterface;

class Project extends BuilderInterface
{
    public static function build($request)
    {
        $appPath = $request->getString('appPath');
        

    }
}