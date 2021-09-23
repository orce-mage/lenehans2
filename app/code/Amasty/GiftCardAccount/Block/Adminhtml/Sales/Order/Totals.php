<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Adminhtml\Sales\Order;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Magento\Framework\DataObject;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler
     */
    private $readHandler;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler $readHandler,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->readHandler = $readHandler;
    }

    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $order = $this->getOrder();
        $this->readHandler->loadAttributes($order);

        if (!$order->getExtensionAttributes() || !$order->getExtensionAttributes()->getAmGiftcardOrder()) {
            return $this;
        }
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        foreach ($gCardOrder->getGiftCards() as $card) {
            $total = new DataObject(
                [
                    'code' => $this->getNameInLayout() . $card[GiftCardCartProcessor::GIFT_CARD_ID],
                    'label' => __('Gift Card %1', $card[GiftCardCartProcessor::GIFT_CARD_CODE]),
                    'value' => -$card[GiftCardCartProcessor::GIFT_CARD_AMOUNT],
                    'base_value' => -$card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT]
                ]
            );
            if ($this->getBeforeCondition()) {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            } else {
                $this->getParentBlock()->addTotal($total, $this->getAfterCondition());
            }
        }

        return $this;
    }
}
