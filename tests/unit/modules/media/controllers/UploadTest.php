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

/**
 * @group    module-media
 *
 * @package  Application\Tests\Media
 * @author   Anton Shevchuk
 * @created  27.05.2014 14:26
 */
class UploadTest extends ControllerTestCase
{
    /**
     * Dispatch module/controller
     */
    public function testUpload()
    {
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Dispatch module/controller
     */
    public function testUploadError()
    {
        self::setupSuperUserIdentity();

        $this->dispatch('media/upload');
        self::assertModule('error');
        self::assertController('index');
    }
}
