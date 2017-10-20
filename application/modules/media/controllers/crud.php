<?php
/**
 * CRUD for media
 *
 * @author   Anton Shevchuk
 */

/**
 * @namespace
 */
namespace Application;

use Application\Media;
use Bluz\Controller\Controller;
use Bluz\Controller\Mapper\Crud;
use Bluz\Proxy\Layout;
use Bluz\Proxy\Request;
use Bluz\Proxy\Response;
use Bluz\Proxy\Session;

/**
 * @accept HTML
 * @accept JSON
 * @privilege Management
 *
 * @return Controller
 * @throws Exception
 * @throws \Bluz\Application\Exception\ForbiddenException
 * @throws \Bluz\Application\Exception\NotImplementedException
 */
return function () {
    /**
     * @var Controller $this
     */
    Session::start();
    if (!Request::isXmlHttpRequest()) {
        $this->useLayout('dashboard.phtml');
    }
    Layout::breadCrumbs(
        [
            Layout::ahref('Dashboard', ['dashboard', 'index']),
            Layout::ahref('Media', ['media', 'grid']),
            __('Upload')
        ]
    );
    if (!$this->user()) {
        throw new Exception('User not found');
    }

    $crudController = new Crud(Media\Crud::getInstance());

    $crudController->get('system', 'crud/get');
    $crudController->post('system', 'crud/post');
    $crudController->put('system', 'crud/put');
    $crudController->delete('system', 'crud/delete');

    $result = $crudController->run();

    // back to grid after create or update media file
    if (Request::isPost() || Request::isPut()) {
        Response::reload();
    }

    return $result;
};
