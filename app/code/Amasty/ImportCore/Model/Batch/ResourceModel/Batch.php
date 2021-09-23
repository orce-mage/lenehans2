<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\Batch\ResourceModel;

use Amasty\ImportCore\Model\Batch\Batch as BatchModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Batch extends AbstractDb
{
    const TABLE_NAME = 'amasty_import_batch';

    protected $_serializableFields = [
        BatchModel::BATCH_DATA => ['[]', []]
    ];

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, BatchModel::ID);
    }

    /**
     * Delete all batches related to specified profile id and return number of deleted batches
     * @param string $processIdentity
     * @return int
     */
    public function deleteProcessData(string $processIdentity): int
    {
        return $this->getConnection()->delete(
            $this->getMainTable(),
            BatchModel::PROCESS_IDENTITY . ' = "' . $processIdentity . '"'
        );
    }
}
