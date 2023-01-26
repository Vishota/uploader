<?php
    require_once $_SERVER['DOCUMENT_ROOT'].'/__required/Database/AbstractSingleDatabase.php';

    final class StorageDatabase extends AbstractSingleDatabase {
        function initConnectionData() : void {
            $this->setConnectionData('pics', 'root', 'mysql');
        }
    }