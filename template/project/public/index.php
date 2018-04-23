<?php
try {
    require '../start.php';
    App::web()->route('admin')->response(function($response){
        if (!empty($response)) {
            echo json_encode(array('code'=>0, 'data'=>$response));
        }
    });
} catch (Exception $e) {
    echo json_encode(array('code'=>$e->getCode(), 'msg'=>$e->getMessage()));
}
