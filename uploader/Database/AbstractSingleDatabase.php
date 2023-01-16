<?php
    require_once __DIR__.'/Database.php';
    require_once __DIR__.'/../Common/SingletonTrait.php';

    abstract class AbstractSingleDatabase extends Database {
        use SingletonTrait;
        // $database, $user, $password, $host must be overriden
        protected function __init() {
            $this->initConnectionData();
            $this->connect();
        }
        abstract function initConnectionData();
        // setConnectionData(..) must be used in initConnectionData
    }