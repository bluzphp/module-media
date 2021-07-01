<?php
/**
 * List of user images in JSON
 *
 * @author   Anton Shevchuk
 * @created  12.02.13 14:18
 */

/**
 * @namespace
 */
namespace Application;

use Application\Media\Table;
use Bluz\Controller\Controller;

/**
 * @return array
 */
return function () {
    /**
     * @var Controller $this
     */
    $this->useJson();

    $images = Table::getInstance()->getImages();

    $images = array_map(static function ($image) {
        return [
            'id' => $image->id,
            'title' => $image->title,
            'url' => $image->file,
            'thumb' => $image->thumb
        ];
    }, $images);

    return $images;
};
