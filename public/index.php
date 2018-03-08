<?php
try {
    require '../start.php';
    App::web()->set('path.loader', array(
        'lib'=>dirname(__DIR__) . '/lib/admin'
    ))->route('admin');
} catch (Exception $e) {
    echo json_encode(array('code'=>$e->getCode(), 'msg'=>$e->getMessage()));
}
