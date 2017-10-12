<?php
/**
 * Grid of Media
 *
 * @author   Anton Shevchuk
 */

/**
 * @namespace
 */
namespace Application;

use Bluz\Controller\Controller;
use Bluz\Proxy\Layout;
use Bluz\Proxy\Request;
use Bluz\Proxy\Response;
use Bluz\Proxy\Session;

/**
 * @privilege Management
 *
 * @return array
 */
return function () {
    /**
     * @var Controller $this
     */
    Session::set('rollback', ['media', 'grid']);
    Layout::setTemplate('dashboard.phtml');
    Layout::breadCrumbs(
        [
            Layout::ahref('Dashboard', ['dashboard', 'index']),
            __('Media')
        ]
    );
    $grid = new Media\Grid();

    return [
        'grid' => $grid
    ];
};
