<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

use Phinx\Migration\AbstractMigration;

/**
 * CreateMediaTable
 */
class MediaPermissions extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $data = [
            [
                'roleId' => 2,
                'module' => 'media',
                'privilege' => 'Management'
            ],
            [
                'roleId' => 2,
                'module' => 'media',
                'privilege' => 'Upload'
            ],
            [
                'roleId' => 3,
                'module' => 'media',
                'privilege' => 'Upload'
            ],
        ];

        $privileges = $this->table('acl_privileges');
        $privileges->insert($data)
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DELETE FROM acl_privileges WHERE module = "media"');
    }
}
