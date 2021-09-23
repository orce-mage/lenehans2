<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\Process\ResourceModel;

use Amasty\ImportCore\Model\Process\Process;
use Amasty\ImportCore\Model\Process\ResourceModel\Process as ProcessResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Process::class, ProcessResource::class);
    }
}
