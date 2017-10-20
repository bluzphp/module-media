<?php
/**
 * Media manager for user
 *
 * @author   Anton Shevchuk
 * @created  02.09.16 14:48
 */

/**
 * @namespace
 */
namespace Application;

use Application\Media\Table;
use Bluz\Controller\Controller;
use Bluz\Proxy\Session;

/**
 * @return array
 */
return function () {
    /**
     * @var Controller $this
     */
    return ['images' => Table::getInstance()->getImages()];
};
