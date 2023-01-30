<?php
require_once __DIR__.'/../runnable/RunGetFile.php';
require_once __DIR__.'/../main/StorageRunner.php';
/** this will be used to get (download) file
 * input:
 *  GET
 *   id = image id
 * output:
 *  image
 */
header('Content-Disposition: attachment; filename="vishotapic'.$_GET['id'].'.webp"');
StorageRunner::getInstance()->run(new RunGetFile());