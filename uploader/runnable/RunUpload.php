<?php
require_once __DIR__.'/../__required/AbstractRunnable.php';
final class RunUpload extends AbstractRunnable {
    function use() : array {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
        // later StorageFactory can be replaced with any class that implements StorageFactoryInterface for testing
        return StorageFactory::getInstance()->getFilesManager()->add($_FILES['file']);
    }
}