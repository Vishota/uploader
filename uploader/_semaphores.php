<?php
    if( !function_exists('ftok') )
    {
        function ftok($filename = "", $proj = "")
        {
            if( empty($filename) || !file_exists($filename) )
            {
                return -1;
            }
            else
            {
                $filename = $filename . (string) $proj;
                for($key = array(); sizeof($key) < strlen($filename); $key[] = ord(substr($filename, sizeof($key), 1)));
                return dechex(array_sum($key));
            }
        }
    }
    if (!function_exists('sem_get')) {
        function sem_get($key) {
            return fopen(__FILE__ . '.sem.' . $key, 'w+');
        }
        function sem_acquire($sem_id) {
            return flock($sem_id, LOCK_EX);
        }
        function sem_release($sem_id) {
            return flock($sem_id, LOCK_UN);
        }
    }