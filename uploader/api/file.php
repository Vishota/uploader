<?php
require_once __DIR__.'/../runnable/RunUpload.php';
require_once __DIR__.'/../main/StorageRunner.php';
/** this will be used to get (download) file
 * input:
 *  GET
 *   id = file id
 * output:
 *  file
 */
StorageRunner::getInstance()->run(RunUpload::getInstance());