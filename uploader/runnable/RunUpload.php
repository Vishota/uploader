<?php
require_once __DIR__.'/../__required/AbstractRunnable.php';
final class RunUpload extends AbstractRunnable {
    function use() : array {
        return [1,2,3];
    }
}