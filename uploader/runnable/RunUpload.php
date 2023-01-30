<?php
require_once __DIR__.'/../__required/AbstractRunnable.php';
final class RunUpload extends AbstractRunnable {
    function use() : array {
        // later StorageFactory can be replaced with any class that implements StorageFactoryInterface for testing
        return StorageFactory::getInstance()->getFilesManager()->add($_FILES['file']);
    }
}