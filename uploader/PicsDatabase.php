<?php
    require_once __DIR__.'/Database/AbstractSingleDatabase.php';

    class PicsDatabase extends AbstractSingleDatabase {
        function initConnectionData() {
            $this->setConnectionData('pics', 'root', 'mysql');
        }
    }