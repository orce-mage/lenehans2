<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class GiftCardPriceCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCard\Model\GiftCard\GiftCardPrice::class,
            \Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
