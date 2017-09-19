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

use Application\Media\Manager;
use Bluz\Application\Exception\BadRequestException;
use Bluz\Config\ConfigException;
use Bluz\Controller\Controller;
use Bluz\Proxy\Request;
use Bluz\Validator\Exception\ValidatorException;
use Zend\Diactoros\UploadedFile;

/**
 * @privilege Upload
 * @method POST
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

    // save media data
    $media = new Media\Row();
    $media->module = 'media';
    $media->userId = $this->user()->id;

    $media->processRequestFile(Request::getFile('file'));

    try {
        $media->save();
    } catch (ValidatorException $e) {
        // remove invalid files
        $media->deleteFiles();

        // create error message
        $errors = array_values($e->getErrors());
        throw new BadRequestException(implode("\n", $errors));
    }

    // displaying file info
    // `id` for media manager
    // `filelink` for editor.js
    return [
        'id' => $media->id,
        'filelink' => $media->file
    ];
};
