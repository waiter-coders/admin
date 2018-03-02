<?php
require '../start.php';

App::web()->set('path.controller', 'page')->request(function(){})->route('api')->response(function($response){
    return '';
});