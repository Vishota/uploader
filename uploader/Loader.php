<?php
    require_once __DIR__.'/_semaphores.php';
    
    class Loader {
        private Database $database;
        private $semaphore;

        function __construct(Database $database) {
            $this->semaphore = sem_get(1);
            $this->database = $database;
        }

        function load(string $loaded) : array {
            try {
                sem_acquire($this->semaphore);
                
                $generated = $this->newPath();
                $dir = 'storage/'.$generated['path'];
                $filename = $generated['file'].'.jpg';


                if(@is_array(getimagesize($loaded))){
                    $image = true;
                } else {
                    $image = false;
                }

                if(!$image) return ['image'=>false];

                $moved = move_uploaded_file($loaded, $dir.$filename);
                if(!$moved) return ['moved'=>false]; 

                $insert = $this->database->request('INSERT INTO info (`path`, `original_filename`) VALUES (?, ?)', [$dir.$filename, '']);
                sem_release($this->semaphore);
                return ['id' => $insert->lastInsertId()];
            }
            catch (Throwable $e) {
                sem_release($this->semaphore);
                return ['thrown' => $e->__toString()];
            }
        }
        private function newPadth() : array {
            return $this->generatePath($this->lastUploadedId() + 1);
        }
        private function generatePath(int $number, int $depth = 4, int $limit = 1000, string $path = '') : array {
            if($depth == 1) return ['path' => $path, 'file' => $this->generateString($number)];
            $path .= $this->generateString($number % $limit) . '/';
            $number = floor($number / $limit);
            return $this->generatePath($number, $depth - 1, $limit, $path);
        }
        private function generateString(int $number, string $charset = 'abcdefghijklmnopqrstuvwxyz', string $string = '') : string {
             if($number == 0) return ($string == '' ? $charset[0] : $string);
             $charsetLength = strlen($charset);
             $string = $charset[$number % $charsetLength] . $string;
             $number = floor($number / $charsetLength);
             return $this->generateString($number, $charset, $string);
        }
        private function lastUploadedId() : int {
            $requestResult = $this->database->request('SELECT id FROM info ORDER BY id DESC LIMIT 1');
            if($requestResult->rowCount() == 0) return 0;
            return $requestResult->response()[0]['id'];
        }
    }