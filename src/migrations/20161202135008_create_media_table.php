<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

use Phinx\Migration\AbstractMigration;

/**
 * CreateMediaTable
 */
class CreateMediaTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS media (id BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT, userId BIGINT(20) unsigned NOT NULL, module VARCHAR(24) DEFAULT \'users\' NOT NULL, title LONGTEXT, type VARCHAR(24), file VARCHAR(255), thumb VARCHAR(255), size INT(11) unsigned NOT NULL, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, updated TIMESTAMP NOT NULL, CONSTRAINT media_users_id_fk FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE);');
        $this->execute('REPLACE INTO `acl_privileges` (`roleId`, `module`, `privilege`) VALUES (2,\'media\',\'Management\'), (2,\'media\',\'Upload\'), (3,\'media\',\'Upload\');');
    }
    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('media');
        $this->execute('DELETE FROM `acl_privileges` WHERE module=\'media\'');
    }
}
