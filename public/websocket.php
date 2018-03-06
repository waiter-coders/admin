<?php
require '../start.php';

App::webSocket()->set('path.controller', 'webSocket')->request(function(){})->route()->response(function($response){
    return '';
});