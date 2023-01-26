<?php
interface StorageFactoryInterface {
    function getDatabase(): DatabaseInterface;
    function getRunner(): AbstractRunner;
}