<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

declare(strict_types=1);

namespace Application\Media;

use Application\Users;
use Bluz\Application\Exception\BadRequestException;
use Bluz\Config\ConfigException;
use Bluz\Proxy\Auth;
use Bluz\Proxy\Config;
use Image\Thumbnail;
use Zend\Diactoros\UploadedFile;

/**
 * Manager
 *
 * @category Application
 * @package  Media
 * @author   Anton Shevchuk
 */
class Manager
{
    const THUMB_HEIGHT = 196;
    const THUMB_WIDTH = 196;

    /**
     * @var Row
     */
    protected $media;

    /**
     * @var UploadedFile
     */
    protected $file;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $publicPath;

    /**
     * @var string
     */
    protected $uploadPath;

    /**
     * @param  Row $media
     * @param  UploadedFile $file
     *
     */
    public function __construct($media, $file)
    {
        $this->media = $media;
        $this->media->module = $this->media->module ?: 'users';
        $this->media->userId = $this->media->userId ?: Auth::getIdentity()->id ?: Users\Table::SYSTEM_USER;

        $this->file = $file;
        $this->name = $media->title ?? pathinfo($file->getClientFilename(), PATHINFO_FILENAME);

        $this->checkError();
        $this->checkType();
    }

    /**
     * Move file to directory
     *
     * @param  string $directory
     *
     * @return void
     * @throws ConfigException
     */
    public function moveToDir($directory)
    {
        $uploadPath = Config::getModuleData('media', 'upload_path');

        if (empty($uploadPath)) {
            throw new ConfigException('Upload path is not configured');
        }

        $fullPath = PATH_PUBLIC .'/'. $uploadPath .'/'. $directory;

        if (!is_dir($fullPath) && !@mkdir($fullPath, 0755, true)) {
            throw new ConfigException('Upload folder is not exists, please create it');
        }

        if (!is_writable($fullPath)) {
            throw new ConfigException('Upload folder is not writable');
        }

        $fileName = $this->getFileName($fullPath);

        $this->publicPath = $uploadPath .'/'. $directory .'/'. $fileName;
        $this->uploadPath = $fullPath .'/'. $fileName;

        $this->file->moveTo($this->uploadPath);
    }

    /**
     * Create thumbnail
     *
     * @return string
     */
    public function createThumbnail()
    {
        // set full path
        $image = new Thumbnail($this->getUploadPath());
        $image->setHeight(self::THUMB_HEIGHT);
        $image->setWidth(self::THUMB_WIDTH);
        $thumb = $image->generate();
        // crop full path
        $thumb = substr($thumb, strlen(PATH_PUBLIC) + 1);
        return $thumb;
    }

    /**
     * Check Error code
     *
     * @return void
     * @throws BadRequestException
     */
    protected function checkError()
    {
        // check upload errors
        if ($this->file->getError() !== UPLOAD_ERR_OK) {
            switch ($this->file->getError()) {
                case UPLOAD_ERR_NO_FILE:
                    $message = __('Please choose file for upload');
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    $message = __(
                        'The uploaded file size should be lower than %s',
                        ini_get('upload_max_filesize')
                    );
                    break;
                default:
                    $message = UploadedFile::ERROR_MESSAGES[$this->file->getError()];
            }
            throw new BadRequestException($message);
        }
    }

    /**
     * checkType
     *
     * @return void
     * @throws BadRequestException
     */
    protected function checkType()
    {
        // check files' types
        $allowTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif'];

        if (!in_array($this->file->getClientMediaType(), $allowTypes, true)) {
            throw new BadRequestException('Wrong file type');
        }
    }

    /**
     * Prepare File name for path
     *
     * @param  string $path
     *
     * @return string
     */
    protected function getFileName($path) : string
    {
        /**
         * Generate image name
         */
        $pathInfo = pathinfo($this->file->getClientFilename());

        $fileName = strtolower($this->name ?? $pathInfo['filename']);
        $fileExt  = strtolower($pathInfo['extension']);

        // Prepare filename
        $fileName = preg_replace('/[ _;:]+/', '-', $fileName);
        $fileName = preg_replace('/[^a-z0-9.-]+/i', '', $fileName);

        // If name is empty, generate it with current time
        if (empty($fileName)) {
            $fileName = date('Y-m-d-His');
        }

        // If file already exists, increment name
        $originFileName = $fileName;
        $counter = 0;
        while (file_exists($path .'/'.$fileName.'.'.$fileExt)) {
            $counter++;
            $fileName = $originFileName.'-'.$counter;
        }
        return $fileName.'.'.$fileExt;
    }

    /**
     * @return string
     */
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * @return string
     */
    public function getUploadPath(): string
    {
        return $this->uploadPath;
    }
}
