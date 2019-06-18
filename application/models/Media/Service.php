<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

declare(strict_types=1);

namespace Application\Media;

use Bluz\Config\ConfigException;
use Bluz\Http\Exception\BadRequestException;
use Bluz\Proxy\Config;
use Exception;
use Image\Thumbnail;
use Zend\Diactoros\UploadedFile;

/**
 * Service
 *
 * @package  models\Media
 * @author   dark
 */
class Service
{
    public const THUMB_HEIGHT = 196;
    public const THUMB_WIDTH = 196;

    /**
     * @param Row          $row
     * @param UploadedFile $file
     * @param string       $module
     * @param integer      $userId
     *
     * @return Row
     * @throws Exception
     */
    public static function upload($row, $file, $module = 'users', $userId = null): Row
    {
        self::checkUploadError($file);
        self::checkImageType($file);

        $row->module = $module;
        $row->userId = $userId;

        // save media data
        try {
            // fill row data
            $row->title = $row->title ?? pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
            $row->type = $file->getClientMediaType();
            $row->size = $file->getSize();

            // process request image
            $row->file = self::save($row, $file);

            // create thumbnail
            $row->thumb = self::thumbnail($row);
        } catch (Exception $e) {
            self::delete($row);
            /** @var Exception $e */
            throw $e;
        }

        return $row;
    }

    /**
     * Move files
     *
     * @param Row          $row
     * @param UploadedFile $file
     *
     * @return string
     * @throws ConfigException
     */
    protected static function save($row, $file): string
    {
        $uploadPath = Config::get('module.media', 'upload_path');

        if (empty($uploadPath)) {
            throw new ConfigException('Upload path is not configured');
        }

        $fullPath = PATH_PUBLIC.'/'.$uploadPath.'/'.$row->userId.'/'.$row->module;

        if (!is_dir($fullPath) && !mkdir($fullPath, 0755, true) && !is_dir($fullPath)) {
            throw new ConfigException('Upload folder is not exists, please create it');
        }

        if (!is_writable($fullPath)) {
            throw new ConfigException('Upload folder is not writable');
        }

        $fileName = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
        $fileExt  = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

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
        while (file_exists($fullPath .'/'.$fileName.'.'.$fileExt)) {
            $counter++;
            $fileName = $originFileName.'-'.$counter;
        }
        $fileName = $fileName.'.'.$fileExt;

        $file->moveTo($fullPath.'/'.$fileName);

        return $uploadPath.'/'.$row->userId.'/'.$row->module.'/'.$fileName;
    }

    /**
     * Create thumbnail
     *
     * @param Row $row
     * @param int $width
     * @param int $height
     *
     * @return string
     * @throws \Image\Exception
     * @throws \ImagickException
     */
    public static function thumbnail($row, $width = self::THUMB_WIDTH, $height = self::THUMB_HEIGHT)
    {
        // set full path
        $image = new Thumbnail(PATH_PUBLIC .'/'. $row->file);
        $image->setWidth($width);
        $image->setHeight($height);
        $thumb = $image->generate();
        // crop full path
        return substr($thumb, strlen(PATH_PUBLIC) + 1);
    }

    /**
     * Delete files
     *
     * @param Row $row
     */
    public static function delete($row): void
    {
        if ($row->file && is_file(PATH_PUBLIC.'/'.$row->file)) {
            @unlink(PATH_PUBLIC.'/'.$row->file);
        }
        if ($row->thumb && is_file(PATH_PUBLIC.'/'.$row->thumb)) {
            @unlink(PATH_PUBLIC.'/'.$row->thumb);
        }
    }

    /**
     * Check Error code
     *
     * @param UploadedFile $file
     *
     * @return void
     * @throws BadRequestException
     */
    public static function checkUploadError($file): void
    {
        // check upload errors
        if ($file->getError() !== UPLOAD_ERR_OK) {
            switch ($file->getError()) {
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
                    $message = UploadedFile::ERROR_MESSAGES[$file->getError()];
            }
            throw new BadRequestException($message);
        }
    }

    /**
     * Check Media Type
     *
     * @param UploadedFile $file
     *
     * @return void
     * @throws BadRequestException
     */
    public static function checkImageType($file): void
    {
        // check files' types
        $allowTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif'];

        if (!in_array($file->getClientMediaType(), $allowTypes, true)) {
            throw new BadRequestException('Wrong file type');
        }
    }
}
