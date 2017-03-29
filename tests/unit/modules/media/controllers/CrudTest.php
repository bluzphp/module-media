<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

/**
 * @namespace
 */
namespace Application\Tests\Media;

use Application\Tests\ControllerTestCase;
use Application\Tests\Tools;
use Application\Exception;
use Application\Tests\Fixtures\Users\UserFixtureContainer;
use Application\Tests\Fixtures\Users\UserHasPermission;
use Bluz\Http\RequestMethod;
use Bluz\Http\StatusCode;
use Bluz\Proxy\Auth;
use Bluz\Proxy\Config;
use Bluz\Proxy\Db;
use Bluz\Proxy\Request;
use Zend\Diactoros\UploadedFile;

/**
 * @group    module-media
 * @group    crud
 *
 * @package  Application\Tests\Media
 * @author   Anton Shevchuk
 * @created  21.05.14 11:28
 */
class CrudTest extends ControllerTestCase
{
    /**
     * Drop photo after the test
     */
    public static function tearDownAfterClass()
    {
        Db::delete('media')->where('userId', [1])->execute();
        $path = Config::getModuleData('media', 'upload_path').'/1';
        Tools\Cleaner::delete($path);
    }

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        self::getApp()->useLayout(false);
        Auth::setIdentity(new UserHasPermission(UserFixtureContainer::$fixture));
    }

    /**
     * Test upload file
     */
    public function testUploadFile()
    {
        // get path from config
        $path = Config::getData('temp', 'path');
        if (empty($path)) {
            throw new Exception('Temporary path is not configured');
        }

        $file = new UploadedFile($path, filesize($path), UPLOAD_ERR_OK, 'test.jpg', 'image/jpeg');

        $request = self::prepareRequest(
            'media/crud',
            [],
            ['title' => 'test'],
            RequestMethod::POST,
            ['accept' => 'text/html']
        )->withUploadedFiles(['file' => $file]);

        Request::setInstance($request);

        $this->getApp()->process();

        self::assertQueryCount('input[name="title"]', 1);
        self::assertOk();
    }

    /**
     * GET request should return FORM for create record
     */
    public function testCreateForm()
    {
        $this->dispatch('/media/crud/');

        self::assertOk();
        self::assertQueryCount('form[method="POST"]', 1);
    }

    /**
     * GET request with ID record should return FORM for edit
     */
    public function testEditForm()
    {
        /*
        $this->dispatchRouter('/media/crud/', ['id' => 1]);
        self::assertOk();

        self::assertQueryCount('form[method="PUT"]', 1);

        self::assertQueryCount('input[name="id"][value="1"]', 1);
        */
        // Remove the following lines when you implement this test.
        // Need to create element with ID
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * GET request with wrong ID record should return ERROR 404
     */
    public function testEditFormError()
    {
        $this->dispatch('/media/crud/', ['id' => 100042]);
        self::assertResponseCode(StatusCode::NOT_FOUND);
    }
}
