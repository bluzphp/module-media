<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

declare(strict_types=1);

namespace Application\Media;

use Bluz\Grid\Source\SqlSource;

/**
 * Grid based on SQL
 *
 * @category Application
 * @package  Media
 */
class Grid extends \Bluz\Grid\Grid
{
    protected $uid = 'options';

    /**
     * {@inheritdoc}
     */
    public function init() : void
    {
        // Array
        $adapter = new SqlSource();
        $adapter->setSource(
            '
             SELECT m.*, u.login
             FROM media m
             LEFT JOIN users u ON u.id = m.userId
             '
        );

        $this->setAdapter($adapter);
        $this->setDefaultLimit(25);
        $this->setAllowOrders(['id', 'login', 'title', 'type', 'created', 'deleted']);
        $this->setAllowFilters(['userId', 'title', 'file']);
    }
}
