<?php
$request = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';
$route = !empty($request) ? 'controller.' . str_replace('/', '.', $request) : 'controller.home.show';
return $route;