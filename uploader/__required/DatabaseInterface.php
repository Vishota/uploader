<?php
interface DatabaseInterface {
    function __construct(string $database, string $user, string $password, string $host = 'localhost');
    function setConnectionData(string $database, string $user, string $password, string $host = 'localhost');
    function connect();
    function request(string $request, array $params = []) : DatabaseRequestResult;
    function beginTransaction();
    function rollBack();
    function commit();
};