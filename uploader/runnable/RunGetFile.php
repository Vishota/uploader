<?php
require_once __DIR__.'/../__required/AbstractRunnable.php';
final class RunGetFile extends AbstractRunnable {
    function use(): array {
        echo StorageFactory::getInstance()->getFilesManager()->readFile($_GET['id']);
        exit;
    }
}