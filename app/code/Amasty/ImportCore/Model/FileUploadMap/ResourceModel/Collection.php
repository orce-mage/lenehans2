<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\FileUploadMap\ResourceModel;

use Amasty\ImportCore\Model\FileUploadMap\FileUploadMap;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\FileUploadMap as FileUploadMapResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(FileUploadMap::class, FileUploadMapResource::class);
    }
}
