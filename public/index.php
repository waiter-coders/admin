<?php
require '../start.php';

App::web()->set('route.default', 'home.show')->request(function(){})->route()->response(function($response){
    return '';
});