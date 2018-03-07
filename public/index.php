<?php
try {
    require '../start.php';
    Engine::addPath('Lib', dirname(__DIR__) . '/lib/admin');
    App::web()->route('admin')->response(function($response){
        return array('code'=>0, 'data'=>$response);
    });
} catch (Exception $e) {
    echo json_encode(array('code'=>$e->getCode(), 'msg'=>$e->getMessage()));
}
