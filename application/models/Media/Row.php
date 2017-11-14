<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

declare(strict_types=1);

namespace Application\Media;

use Application\Users;
use Bluz\Proxy\Auth;
use Bluz\Validator\Traits\Validator;
use Zend\Diactoros\UploadedFile;

/**
 * Media Row
 *
 * @category Application
 * @package  Media
 *
 * @property integer $id
 * @property integer $userId
 * @property string $title
 * @property string $module
 * @property string $type
 * @property string $file
 * @property string $thumb
 * @property integer $size
 * @property string $created
 * @property string $updated
 */
class Row extends \Bluz\Db\Row
{
    use Validator;

    /**
     * {@inheritdoc}
     */
    protected function beforeSave() : void
    {
        $this->addValidator('title')
            ->required()
            ->latinNumeric(' -_.');
    }

    /**
     * __insert
     *
     * @return void
     */
    protected function beforeInsert() : void
    {
        $this->created = gmdate('Y-m-d H:i:s');

        // set default module
        if (!$this->module) {
            $this->module = 'users';
        }
        // set user ID
        if ($user = Auth::getIdentity()) {
            $this->userId = $user->getId();
        } else {
            $this->userId = Users\Table::SYSTEM_USER;
        }
    }

    /**
     * __update
     *
     * @return void
     */
    protected function beforeUpdate() : void
    {
        $this->updated = gmdate('Y-m-d H:i:s');
    }

    /**
     * postDelete
     *
     * @return void
     */
    protected function afterDelete() : void
    {
        $this->deleteFiles();
    }

    /**
     * processRequestFile
     *
     * @param UploadedFile $file
     *
     * @return void
     * @throws \Bluz\Config\ConfigException
     */
    public function processRequestFile($file)
    {
        // process request image
        $fileManager = new Manager($this, $file);

        // move request file
        $fileManager->moveToDir($this->userId.'/'.$this->module);

        // fill row data
        $this->title = $this->title ?: pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
        $this->file = $fileManager->getPublicPath();
        $this->type = $file->getClientMediaType();
        $this->size = $file->getSize();

        // create thumbnail
        $this->thumb = $fileManager->createThumbnail();
    }

    /**
     * Delete Files
     *
     * @return void
     */
    public function deleteFiles()
    {
        if ($this->file && is_file(PATH_PUBLIC.'/'.$this->file)) {
            @unlink(PATH_PUBLIC.'/'.$this->file);
        }
        if ($this->thumb && is_file(PATH_PUBLIC.'/'.$this->thumb)) {
            @unlink(PATH_PUBLIC.'/'.$this->thumb);
        }
    }
}
