<?php
require_once __DIR__ . '/SingletonTrait.php';
abstract class AbstractRunnable {
    use SingletonTrait;
    abstract function use() : array;
}