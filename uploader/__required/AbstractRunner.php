<?php
    require_once __DIR__.'/AbstractRunnable.php';
    require_once __DIR__.'/SingletonTrait.php';

    abstract class AbstractRunner {
        use SingletonTrait;
        abstract protected function prerun() : void;
        final function run(AbstractRunnable $command) : never {
            $this->prerun();
            echo json_encode($command->use());
            exit;
        }
    }