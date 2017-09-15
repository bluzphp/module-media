<?php
/**
 * Upload media content to server
 *
 * @author   Anton Shevchuk
 * @created  06.02.13 18:16
 */

/**
 * @namespace
 */
namespace Application;

use Bluz\Application\Exception\BadRequestException;
use Bluz\Config\ConfigException;
use Bluz\Controller\Controller;
use Bluz\Proxy\Config;
use Bluz\Proxy\Request;
use Bluz\Validator\Exception\ValidatorException;
use Zend\Diactoros\UploadedFile;

/**
 * @privilege Upload
 * @return array
 * @throws BadRequestException
 * @throws ConfigException
 */
return function () {
    /**
     * @var Controller $this
     * @var UploadedFile $file
     */
    // switch to JSON response
    $this->useJson();

    $file = Request::getFile('file');

    // check upload errors
    if ($file->getError() !== UPLOAD_ERR_OK) {
        switch ($file->getError()) {
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

    // check upload types
    $allowTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif'];

    if (!in_array($file->getClientMediaType(), $allowTypes, true)) {
        throw new BadRequestException('Wrong file type');
    }

    // process image
    // save original name
    $original = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);

    // rename file to date/time stamp
    $filename = date('Ymd_Hi').'.'.pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

    $userId = $this->user()->id;

    // directory structure:
    //   uploads/
    //     %userId%/
    //       %module%/
    //         filename.ext
    $path = Config::getModuleData('media', 'upload_path');
    if (empty($path)) {
        throw new ConfigException('Upload path is not configured');
    }

    $file->moveTo($path.'/'.$userId.'/media/'.$filename);

    // save media data
    $media = new Media\Row();
    $media->userId = $userId;
    $media->module = 'media';
    $media->type = $file->getClientMediaType();
    $media->title = $original;
    $media->file = 'uploads/'.$userId.'/media/'.$filename;
    $media->size = filesize($path.'/'.$userId.'/media/'.$filename);

    try {
        $media->save();
    } catch (ValidatorException $e) {
        // remove invalid files
        $media->deleteFiles();

        // create error message
        $errors = array_values($e->getErrors());
        throw new BadRequestException(implode("\n", $errors));
    }

    // displaying file
    return ['filelink' =>  $media->file];
};
