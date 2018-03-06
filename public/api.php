<?php
require '../start.php';

App::web()->set('path.controller', 'api')->request(function(){})->route('api')->response(function($response){
    return '';
});