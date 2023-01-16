<?php
    class DatabaseRequestResult {
        private int $rowCount;
        private int $columnCount;
        private int $lastInsertId;
        private array $response;
        function __construct(PDO $dbh, PDOStatement $sth, int $fetchMode = PDO::FETCH_ASSOC) {
            $this->rowCount = $sth->rowCount();
            $this->columnCount = $sth->columnCount();
            $this->response = $sth->fetchAll($fetchMode);
            $this->lastInsertId = $dbh->lastInsertId();
        }

        public function rowCount() : int {
            return $this->rowCount;
        }
        public function columnCount() : int {
            return $this->columnCount;
        }
        public function response() : array {
            return $this->response;
        }
        public function lastInsertId() : int {
            return $this->lastInsertId;
        }
    }