<?php
    trait SingletonTrait {
        private static $instance;
        public static function getInstance() : self {
            if(self::$instance === null) self::$instance = new static();
            return self::$instance;
        }
        private function preventDuplication() {
            if(self::$instance !== null) {
                throw new SingletonDuplicationException(gettype($this));
            }
        }
        protected function __construct() {
            $this->preventDuplication();
            $this->__init();
        }
        protected function __init() {}
    }
    class SingletonDuplicationException extends Exception {
        function __construct(string $message) {
            parent::__construct('[Singleton duplication error] ' . $message);
        }
    }