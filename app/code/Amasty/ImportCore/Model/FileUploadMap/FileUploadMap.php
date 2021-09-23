<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\FileUploadMap;

use Magento\Framework\Model\AbstractModel;

/**
 * @method self setFilename(string $filename)
 * @method string getFilename()
 * @method self setHash(string $hash)
 * @method string getCreatedAt()
 * @method self setFileext(string $fileext)
 * @method string getFileext()
 */
class FileUploadMap extends AbstractModel
{
    const ID = 'id';
    const FILENAME = 'filename';
    const FILEEXT = 'fileext';
    const HASH = 'hash';
    const CREATED_AT = 'created_at';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\FileUploadMap::class);
        $this->setIdFieldName(self::ID);
    }
}
