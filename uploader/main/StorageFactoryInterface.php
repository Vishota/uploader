<?php
interface StorageFactoryInterface {
    static function getInstance(): StorageFactoryInterface;
    function getDatabase(): DatabaseInterface;
    function getRunner(): AbstractRunner;
}