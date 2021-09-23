<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model\ResourceModel\Quote;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Quote;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Quote as QuoteResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection for Quote
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = QuoteInterface::ID;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(Quote::class, QuoteResource::class);
    }
}
