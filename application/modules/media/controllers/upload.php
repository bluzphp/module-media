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

use Application\Media\Row;
use Application\Media\Service;
use Application\Media\Table;
use Bluz\Http\Exception\BadRequestException;
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

    $files = Request::getFile('files');

    if (!is_array($files)) {
        $files = [$files];
    }

    $rows = [];

    foreach ($files as $file) {
        try {
            /** @var Row $row */
            $row = Table::create();
            $row = Service::upload($row, $file, 'media', $this->user()->getId());
            $row->save();
            $rows[] = $row;
        } catch (ValidatorException $e) {
            // create error message
            $errors = array_values($e->getErrors());
            throw new BadRequestException(implode("\n", $errors));
        }
    }

    // displaying file info
    // `id` for media manager
    // `url` for redactor.js
    $response = [];

    if (count($rows) === 1) {
        $row = current($rows);
        $response = [
            'file' => [
                'id' => $row->id,
                'url' => $row->file,
            ]
        ];
    } else {
        foreach ($rows as $i => $row) {
            $response['file-'.$i] = [
                'id' => $row->id,
                'url' => $row->file,
            ];
        }
    }

    return $response;
};
