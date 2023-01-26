<?php
final class StorageFactory implements StorageFactoryInterface {
    function getDatabase() {
        return StorageDatabase::getInstance();
    }
}