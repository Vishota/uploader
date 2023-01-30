<?php
require_once __DIR__.'/../runnable/RunGetFile.php';
require_once __DIR__.'/../main/StorageRunner.php';
/** this will be used to get image
 * input:
 *  GET
 *   id = image id
 * output:
 *  image
 */
header('Content-type: image');
StorageRunner::getInstance()->run(new RunGetFile());