<?php
require '../start.php';

App::web()->set('route.default', 'home.show')->route()->response(function($response){
    return '';
});