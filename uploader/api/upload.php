<?php
require_once __DIR__.'/../runnable/RunUpload.php';
require_once __DIR__.'/../main/StorageRunner.php';
/** this will be used to upload files
 * input:
 *  file
 * output:
 *  id
 */
StorageRunner::getInstance()->run(new RunUpload());
