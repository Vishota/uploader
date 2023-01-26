<?php
    require_once __DIR__.'/DatabaseRequestResult.php';
    require_once __DIR__.'/DatabaseInterface.php';

    class Database implements DatabaseInterface {
        private PDO $dbh;
        private string $database, $user, $password, $host;
        function __construct(string $database, string $user, string $password, string $host = 'localhost') {
            $this->setConnectionData($database, $user, $password, $host);
            $this->connect();
        }
        function setConnectionData(string $database, string $user, string $password, string $host = 'localhost') {
            $this->database = $database;
            $this->user = $user;
            $this->password = $password;
            $this->host = $host;
        }
        function connect() {
            $this->dbh = new PDO('mysql:dbname=' . $this->database . ';host=' . $this->host, $this->user, $this->password);
        }
        function request(string $request, array $params = []) : DatabaseRequestResult {
            $sth = $this->dbh->prepare($request);
            $sth->execute($params);
            return new DatabaseRequestResult($this->dbh, $sth);
        }
        function beginTransaction() {
            $this->dbh->beginTransaction();
        }
        function rollBack() {
            $this->dbh->rollBack();
        }
        function commit() {
            $this->dbh->commit();
        }
    }