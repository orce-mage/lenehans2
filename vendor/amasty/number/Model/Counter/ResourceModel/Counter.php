<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Counter\ResourceModel;

use Amasty\Number\Api\Data\CounterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Amasty\Number\Model\ConfigProvider;

class Counter extends AbstractDb
{
    const SEPARATE_CONNECTION_NAME = 'amasty_number_connection';

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        $connectionName = null
    ) {
        if ($configProvider->isUseSeparateConnection()) {
            $connectionName = self::SEPARATE_CONNECTION_NAME;
        }

        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('amasty_number_counter_data', CounterInterface::COUNTER_ID);
    }
}
