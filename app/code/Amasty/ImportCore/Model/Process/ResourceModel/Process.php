<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\Process\ResourceModel;

use Amasty\ImportCore\Model\Process\Process as ProcessModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Process extends AbstractDb
{
    const TABLE_NAME = 'amasty_import_process';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ProcessModel::ID);
    }
}
