<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Observer;

use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Handlers\ReadHandler;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddAmountsAfterCollectionLoad implements ObserverInterface
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

    public function __construct(
        ReadHandler $readHandler
    ) {
        $this->readHandler = $readHandler;
    }

    /**
     * Add GiftCard Prices to loaded product collection
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();

        foreach ($collection as $item) {
            if ($item->getTypeId() == GiftCard::TYPE_AMGIFTCARD) {
                $attribute = $item->getResource()->getAttribute(Attributes::GIFTCARD_PRICES);
                if ($attribute && $attribute->getId()) {
                    $this->readHandler->execute($item);
                }
            }
        }
    }
}
