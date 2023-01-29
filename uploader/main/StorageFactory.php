<?php
require_once __DIR__.'/StorageFactoryInterface.php';
require_once __DIR__.'/StorageDatabase.php';
final class StorageFactory implements StorageFactoryInterface {
    private Database $db;
    use SingletonTrait;
    public function getRunner(): AbstractRunner {
        return StorageRunner::getInstance();
	}
    public function getDatabase(): DatabaseInterface {
        if (!isset($this->db)) {
            $this->db = new Database('storage', 'root', 'mysql');
        }
        return $this->db;
        //return StorageDatabase::getInstance();
    }
    public function getFilesManager(): FilesManager {
        return FilesManager::getInstance();
	}
}