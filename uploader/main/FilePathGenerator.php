<?php
class FilePathGenerator {
    /**
     * out: ['directory'=>string, 'filename'=>string]
     */
    use SingletonTrait;
    public function generatePath(int $number, int $depth = 4, int $limit = 1000, string $path = ''): array {
        if($depth == 1) return ['directory' => $path, 'filename' => $this->generateString($number)];
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