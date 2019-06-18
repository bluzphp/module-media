<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

declare(strict_types=1);

namespace Application\Media;

use Application\Users;
use Bluz\Config\ConfigException;
use Bluz\Http\Exception\BadRequestException;
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
    public const THUMB_HEIGHT = 196;
    public const THUMB_WIDTH = 196;

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
     * @param Row          $media
     * @param UploadedFile $file
     *
     * @throws BadRequestException
     */
    public function __construct($media, $file)
    {
        $this->media = $media;
        $this->media->module = $this->media->module ?: 'users';
        $this->media->userId = $this->media->userId ?: Auth::getIdentity()->getId() ?: Users\Table::SYSTEM_USER;

        $this->file = $file;
        $this->name = $media->title ?? pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
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
        $uploadPath = Config::get('module.media', 'upload_path');

        if (empty($uploadPath)) {
            throw new ConfigException('Upload path is not configured');
        }

        $fullPath = PATH_PUBLIC.'/'.$uploadPath.'/'.$directory;

        if (!@mkdir($fullPath, 0755, true) && !is_dir($fullPath)) {
            throw new ConfigException('Upload folder is not exists, please create it');
        }

        if (!is_writable($fullPath)) {
            throw new ConfigException('Upload folder is not writable');
        }

        $fileName = $this->getFileName($fullPath);

        $this->publicPath = $uploadPath.'/'.$directory.'/'.$fileName;
        $this->uploadPath = $fullPath.'/'.$fileName;

        $this->file->moveTo($this->uploadPath);
    }

    /**
     * Create thumbnail
     *
     * @return string
     * @throws \Image\Exception
     * @throws \ImagickException
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
