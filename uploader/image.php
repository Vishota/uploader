<?php
    require_once 'PicsDatabase.php';

    if(!isset($_GET['id'])) exit;

    $type = 'image';
    $file = PicsDatabase::getInstance()->request('SELECT path FROM info WHERE id=?', [$_GET['id']])->response()[0]['path'];

    header('Content-Type:'.$type);
    header('Content-Length: ' . filesize($file));

    readfile($file);