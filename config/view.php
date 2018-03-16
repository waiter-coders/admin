<?php
$appPath = dirname(__DIR__);
return array(
    'type'=>'smarty',
    'compile'=>$appPath . '/storage/views',
    'template'=>$appPath . '/template',
);