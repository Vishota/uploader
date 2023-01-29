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
    private const IMAGE_MAX_SIZE_B = 250 * 1024;
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
            // 2-3. compressing & moving
            if($this->moveFile($file['tmp_name'], $generatedPath) == false) {
                $result['status'] = 'NOT_MOVED';
                return $result;
            }

            $id = $this->addToDatabase($file, $generatedPath['directory'] . $generatedPath['filename']);
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
     */
    private function moveFile (string $tmpPath, array $generatedPath): bool {
        $this->compressAndMoveImage($tmpPath, $generatedPath, self::IMAGE_DEFAULT_QUALITY, self::IMAGE_MAX_SIZE_PX);
        return true;
        //return move_uploaded_file($tmpPath, $generatedPath['directory'].$generatedPath['file']);
    }
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
        /*$imagetype = exif_imagetype($tmpPath);
        switch($imagetype) {
            case IMAGETYPE_GIF:
                imagewebp(imagecreatefromgif($tmpPath), $generatedPath['directory'].$generatedPath['filename'].'.webp', $quality);
                break;
            case IMAGETYPE_JPEG:
                $origin = imagecreatefromjpeg($tmpPath);
                list($origWidth, $origHeight) = getimagesize($tmpPath);
                $resizeCoeff = 1;
                if($origWidth > $maxsize || $origHeight > $maxsize) {
                    $resizeCoeff = $maxsize / max($origWidth, $origHeight);
                }
                $newWidth = ceil($origWidth * $resizeCoeff);
                $newHeight = ceil($origHeight * $resizeCoeff);
                $compressed = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($compressed, $origin, 0, 0, 0, 0, $origWidth * $resizeCoeff, $origHeight * $resizeCoeff, $origWidth, $origHeight);

                imagewebp($compressed, $generatedPath['directory'].$generatedPath['filename'].'.webp', $quality);

                if (filesize($generatedPath['directory'] . $generatedPath['filename'] . '.webp') > self::IMAGE_MAX_SIZE_B && $quality != 0) {
                    $this->compressAndMoveImage($tmpPath, $generatedPath, max(0, $quality - 10), $maxsize);
                }
                break;
            default: return false;
        }*/
    }
    /**
     * returns id or false
     */
    private function addToDatabase (array $file, string $fullPath) {
        $result = $this->database->request('INSERT INTO files (`original_name`, `path`, `size`, `is_image`, `is_text`) VALUES (?,?,?,?,?)', [substr($file['name'], 100), $fullPath, $file['size'], 1, 0 ]);
        return $result->rowCount() == 0 ? false : $result->lastInsertId();
    }

    /**
     * [
     * 'directory'=>string
     * 'filename'=>string
     * ]
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
