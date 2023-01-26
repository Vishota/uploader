<?php
    require_once __DIR__.'/Database.php';
    require_once __DIR__.'/../Common/SingletonTrait.php';

    abstract class AbstractSingleDatabase extends Database {
        use SingletonTrait;
        protected function __init() {
            $this->initConnectionData();
            $this->connect();
        }
        // setConnectionData(..) must be used in initConnectionData in child class
        abstract function initConnectionData();
    }