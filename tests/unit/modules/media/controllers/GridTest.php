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
 * @group    grid
 *
 * @package  Application\Tests\Media
 * @author   Anton Shevchuk
 * @created  27.05.2014 14:26
 */
class GridTest extends ControllerTestCase
{
    /**
     * Dispatch module/controller
     */
    public function testControllerPage()
    {
        self::setupSuperUserIdentity();

        $this->dispatch('/media/grid/');
        self::assertModule('media');
        self::assertController('grid');
        self::assertOk();
        self::assertQuery('div[data-spy="grid"]');
    }
}
