<?php
    class Loader {
        private int $lastUploadedId;

        function __construct() {
            $this->lastUploadedId = 65000000000000-3;
        }

        function load(string $loaded) : array {
            try {
                $generated = $this->newPath();
                $dir = 'storage/'.$generated['path'];
                $filename = $generated['file'].'.jpg';

                echo $dir.$filename."\n";
                @mkdir($dir, 0777, true);

                if(@is_array(getimagesize($loaded))){
                    $image = true;
                } else {
                    $image = false;
                }

                if(!$image) return ['image'=>false];

                $moved = move_uploaded_file($loaded, $dir.$filename);
                if(!$moved) return ['moved'=>false];

                return ['path' => $dir.$filename];
            }
            catch (Throwable $e) {
                return ['thrown' => $e->__toString()];
            }
        }
        private function newPath() : array {
            $this->lastUploadedId++;
            return $this->generatePath($this->lastUploadedId);
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
    }