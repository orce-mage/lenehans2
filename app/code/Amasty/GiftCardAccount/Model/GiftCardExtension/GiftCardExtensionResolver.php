<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension;

use Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Magento\Framework\DataObject;

class GiftCardExtensionResolver
{
    /**
     * @var Order\Handlers\ReadHandler
     */
    private $orderReadHandler;

    /**
     * @var Invoice\Handlers\ReadHandler
     */
    private $invoiceReadHandler;

    /**
     * @var Creditmemo\Handlers\ReadHandler
     */
    private $memoReadHandler;

    public function __construct(
        \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler $orderReadHandler,
        \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\ReadHandler $invoiceReadHandler,
        \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\ReadHandler $memoReadHandler
    ) {
        $this->orderReadHandler = $orderReadHandler;
        $this->invoiceReadHandler = $invoiceReadHandler;
        $this->memoReadHandler = $memoReadHandler;
    }

    /**
     * @param DataObject $source
     *
     * @return GiftCardCreditmemoInterface|GiftCardInvoiceInterface|GiftCardOrderInterface|null
     */
    public function resolve(DataObject $source)
    {
        switch (true) {
            case $source instanceof \Magento\Sales\Api\Data\OrderInterface:
                $this->orderReadHandler->loadAttributes($source);
                $gCardExt = $source->getExtensionAttributes()->getAmGiftcardOrder();
                break;
            case $source instanceof \Magento\Sales\Api\Data\InvoiceInterface:
                $this->invoiceReadHandler->loadAttributes($source);
                $gCardExt = $source->getExtensionAttributes()->getAmGiftcardInvoice();
                break;
            case $source instanceof \Magento\Sales\Api\Data\CreditmemoInterface:
                $this->memoReadHandler->loadAttributes($source);
                $gCardExt = $source->getExtensionAttributes()->getAmGiftcardCreditmemo();
                break;
            default:
                $gCardExt = null;
        }

        return $gCardExt;
    }
}
