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
use Bluz\Http\RequestMethod;

/**
 * @group    module-media
 *
 * @package  Application\Tests\Media
 * @author   Anton Shevchuk
 * @created  27.05.2014 14:26
 */
class ListTest extends ControllerTestCase
{
    /**
     * Dispatch module/controller
     */
    public function testControllerPage()
    {
        self::setupSuperUserIdentity();

        $this->dispatch('/media/list/', [], RequestMethod::GET, true);
        self::assertModule('media');
        self::assertController('list');
        self::assertOk();
    }
}
