<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: *");
    header('Access-Control-Allow-Methods: GET, POST');

    require_once 'Loader.php';
    $out = (new Loader())->load($_FILES["file"]["tmp_name"]);

    echo json_encode($out);