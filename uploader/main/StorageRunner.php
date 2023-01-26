<?php
require_once __DIR__.'/../__required/AbstractRunner.php';
final class StorageRunner extends AbstractRunner {
    function prerun() : void {
        spl_autoload_register(function (string $class) {
            require_once  __DIR__."/../__required/_semaphores.php";
            @require_once __DIR__."/../__required/$class.php";
            @require_once __DIR__."/../runnable/$class.php";
            @require_once __DIR__."/$class.php";
        });
    }
}