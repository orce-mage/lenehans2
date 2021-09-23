<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\Batch;

use Magento\Framework\Model\AbstractModel;

/**
 * @method self setProcessIdentity(string $processIdentity)
 * @method string getProcessIdentity()
 * @method self setBatchData(array $serializedData)
 * @method array getBatchData()
 */
class Batch extends AbstractModel
{
    const ID = 'id';
    const CREATED_AT = 'created_at';
    const PROCESS_IDENTITY = 'process_identity';
    const BATCH_DATA = 'batch_data';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Batch::class);
        $this->setIdFieldName(self::ID);
    }
}
