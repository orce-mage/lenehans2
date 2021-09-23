<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\FileUploadMap\ResourceModel;

use Amasty\ImportCore\Model\FileUploadMap\FileUploadMap as FileUploadMapModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FileUploadMap extends AbstractDb
{
    const TABLE_NAME = 'amasty_import_file_upload_map';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, FileUploadMapModel::ID);
    }
}
