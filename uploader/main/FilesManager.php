<?php
// file size must be set in php.ini
final class FilesManager {
    use SingletonTrait;
    private DatabaseInterface $database;
    private StorageFactoryInterface $factory;
    const UPLOAD_ERROR_STRINGS = [
        UPLOAD_ERR_OK => 'UPLOAD_ERR_OK',
        UPLOAD_ERR_INI_SIZE => 'UPLOAD_ERR_INI_SIZE',
        UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE',
        UPLOAD_ERR_PARTIAL => 'UPLOAD_ERR_PARTIAL',
        UPLOAD_ERR_NO_FILE => 'UPLOAD_ERR_NO_FILE',
        UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
        UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
        UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION'
    ];
    private const IMAGE_MAX_SIZE_PX = 1000;
    private const IMAGE_MAX_SIZE_B = 400 * 1024;
    private const IMAGE_DEFAULT_QUALITY = 50;
    private static $storage_root;
    function __construct() {
        $this->factory = StorageFactory::getInstance();
        $this->database = $this->factory->getDatabase();
        self::$storage_root = $_SERVER['DOCUMENT_ROOT'] . '/storage/';
    }
    /**$file = $_FILE['filename']:
     *  'name'
     *  'type'      (unsafe, browser-provided)
     *  'size'
     *  'tmp_name'
     *  'error'     (== 0 if okay)
     *  'full_path' (PHP 8.1.0+)
     * */

    /**
     * algo:
     *  1. check errors (including checking if image, etc)
     *  2. convert file
     *  3. move file
     *  4. add to db
     */
    function add(array $file): array {
        sem_acquire(sem_get(1));
        try {
            $result = [];
            /**
             * $result:
             *  'status'    (0 if okay)
             */

            // 1. checkin
            $checked = $this->checkFile($file);
            if($checked != 0) {
                $result['status'] = $checked;
                return $result;
            }

            // generating path 
            $generatedPath = $this->generateNextFilePath();
            $finalPath = $this->moveFile($file['tmp_name'], $generatedPath);
            // 2-3. compressing & moving
            if($finalPath == false) {
                $result['status'] = 'NOT_MOVED';
                return $result;
            }

            $id = $this->addToDatabase($file, $finalPath);
            if($id == false) {
                $result['status'] = 'NOT_ADDED_TO_DB';
                return $result;
            }
            $result['id'] = $id;
        }
        catch (Exception $e) {
            $result['status'] = -1;
        }
        finally {
            sem_release(sem_get(1));
        }
        return $result;
    }
    public function readFile(int $id): string {
        $requestResult = $this->database->request('SELECT path, is_accessible FROM files WHERE id=?', [$id]);
        if($requestResult->rowCount() == 0) return 'NOFILE';
        if($requestResult->response()[0]['is_accessible'] == false) return 'BANNED';
        return file_get_contents($requestResult->response()[0]['path']);
    }
    
    /**
     * 0 if okay
     * else != 0
     */
    private function checkFile(array $file) {
        if($file['error'] != 0) {
            return self::UPLOAD_ERROR_STRINGS[$file['error']];
        }
        if(!@is_array(getimagesize($file['tmp_name']))) {
            return 'NOT_IMAGE';
        }
    }
    /**
     * converts and moves file
     * returns string filepath or false if failed
     */
    private function moveFile (string $tmpPath, array $generatedPath) {
        // if it will be possible to upload non-image files there will be check if file image or something else; now users can upload images only
        return $this->compressAndMoveImage($tmpPath, $generatedPath, self::IMAGE_DEFAULT_QUALITY, self::IMAGE_MAX_SIZE_PX);
    }
    /**
     * @return string filepath or false
     */
    private function compressAndMoveImage(string $tmpPath, array $generatedPath, int $quality, int $maxsizePx) {
        $origin = imagecreatefromstring(file_get_contents($tmpPath));
        $dir = self::$storage_root . $generatedPath['directory'];
        $file = $dir . $generatedPath['filename'] . '.webp';
        @mkdir($dir, 0777, true);

        list($origWidth, $origHeight) = getimagesize($tmpPath);
        $resizeCoeff = 1;
        if($origWidth > $maxsizePx || $origHeight > $maxsizePx) {
            $resizeCoeff = $maxsizePx / max($origWidth, $origHeight);
        }
        $newWidth = ceil($origWidth * $resizeCoeff);
        $newHeight = ceil($origHeight * $resizeCoeff);
        $compressed = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($compressed, $origin, 0, 0, 0, 0, $origWidth * $resizeCoeff, $origHeight * $resizeCoeff, $origWidth, $origHeight);
        imagewebp($compressed, $file, $quality);
        
        if (filesize($file) > self::IMAGE_MAX_SIZE_B && $quality != 0) {
            $this->compressAndMoveImage($tmpPath, $generatedPath, max(0, $quality - 10), $maxsizePx);
        }

        return $file;
    }
    
    /**
     * returns id or false
     */
    private function addToDatabase (array $file, string $fullPath) {
        $result = $this->database->request('INSERT INTO files (`original_name`, `path`, `size`, `is_image`, `is_text`) VALUES (?,?,?,?,?)', [substr($file['name'], 100), $fullPath, $file['size'], 1, 0 ]);
        return $result->rowCount() == 0 ? false : $result->lastInsertId();
    }

    /**
     * returns [ 'directory'=>string, 'filename'=>string ]
     */
    private function generateNextFilePath(): array {
        return FilePathGenerator::getInstance()->generatePath($this->getNextFileId());
    }
    private function getNextFileId(): int {
        $currentIdRequestResult = $this->database->request('SELECT id FROM files ORDER BY id DESC LIMIT 1');
        if($currentIdRequestResult->rowCount() == 0) return 1;
        return $currentIdRequestResult->response()[0]['id'] + 1;
    }
}
