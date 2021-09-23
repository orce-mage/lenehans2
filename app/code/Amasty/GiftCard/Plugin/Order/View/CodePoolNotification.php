<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Plugin\Order\View;

use Amasty\GiftCard\Model\Code\ResourceModel\CollectionFactory;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

class CodePoolNotification
{
    const AVAILIABLE_CODES_KEY = 'availiable_code';
    const REQUESTED_CODES_KEY = 'requested_codes';

    /**
     * @var CollectionFactory
     */
    private $codeCollectionFactory;

    /**
     * @var array
     */
    private $codesArray = [];

    /**
     * @var array
     */
    private $checkedOrders = [];

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        CollectionFactory $codeCollectionFactory,
        ManagerInterface $messageManager
    ) {
        $this->codeCollectionFactory = $codeCollectionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param View $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGetOrder(View $subject, OrderInterface $order): OrderInterface
    {
        if (in_array($order->getId(), $this->checkedOrders)) {
            return $order;
        }
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getProductType() != \Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard::TYPE_AMGIFTCARD
                || $item->getQtyOrdered() == $item->getQtyInvoiced()) {
                continue;
            }
            $orderedQty = $item->getQtyOrdered();
            $productOptions = $item->getProductOptions();
            $codePool = $productOptions[Attributes::CODE_SET] ?? null;
            $codesCount = $this->codeCollectionFactory->create()->countOfFreeCodesByCodeSet((int)$codePool);

            if (isset($this->codesArray[$codePool])) {
                $this->codesArray[$codePool][self::REQUESTED_CODES_KEY] += $orderedQty;
            } else {
                $this->codesArray[$codePool] = [
                    self::REQUESTED_CODES_KEY => $orderedQty,
                    self::AVAILIABLE_CODES_KEY => $codesCount
                ];
            }
        }
        $invalidCodePoolsArray = [];

        foreach ($this->codesArray as $codePoolId => $data) {
            if ($data[self::AVAILIABLE_CODES_KEY] < $data[self::REQUESTED_CODES_KEY]) {
                $invalidCodePoolsArray[] = $codePoolId;
            }
        }

        if ($invalidCodePoolsArray) {
            $this->messageManager->addWarningMessage(
                __('Not enough free gift card codes in the code pool(s) with id %1.'
                    . ' Please generate more codes before invoicing the order.', implode(',', $invalidCodePoolsArray))
            );
        }
        $this->checkedOrders[] = $order->getId();

        return $order;
    }
}
