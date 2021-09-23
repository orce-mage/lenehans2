<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings;

use Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings as StockstatusSettingsResource;
use Amasty\Stockstatus\Model\StockstatusSettings;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(StockstatusSettings::class, StockstatusSettingsResource::class);
    }
}
