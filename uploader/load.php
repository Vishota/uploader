<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: *");

    $out = [];
    move_uploaded_file($_FILES["file"]["tmp_name"], 'pics/'.rand(0, 10000).'.jpg');

    echo json_encode($out);