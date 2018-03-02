<?php
require '../start.php';

App::web()->set('route.default', 'home.show')->request()->route()->response(function($response){
    return '';
});