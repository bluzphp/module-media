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
     *
     * @return integer
     * @throws \Bluz\Db\Exception\DbException
     * @throws \Bluz\Db\Exception\InvalidPrimaryKeyException
     * @throws \Bluz\Db\Exception\TableNotFoundException
     * @throws \Bluz\Http\Exception\BadRequestException
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
        $row = Service::upload($row, Request::getFile('file'));

        return $row->save();
    }
}
