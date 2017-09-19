<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

declare(strict_types=1);

namespace Application\Media;

use Bluz\Proxy\Request;

/**
 * Class Crud of Media
 * @package Application\Media
 *
 * @method Table getTable()
 */
class Crud extends \Bluz\Crud\Table
{
    /**
     * createOne
     *
     * @param array $data
     * @throws \Application\Exception
     * @return integer
     */
    public function createOne($data)
    {
        /**
         * @var Row $row
         */
        $row = $this->getTable()->create();

        $data = $this->filterData($data);

        $row->setFromArray($data);

        /**
         * Process HTTP File
         */
        $row->processRequestFile(Request::getFile('file'));

        return $row->save();
    }
}
