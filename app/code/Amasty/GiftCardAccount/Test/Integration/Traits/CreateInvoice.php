<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Traits;

use Amasty\GiftCard\Model\CodePool\ResourceModel\Collection;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Helper\Bootstrap;

trait CreateInvoice
{
    public function createForOrder(string $incrementId): Invoice
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var InvoiceService $invoiceService */
        $invoiceService = $objectManager->create(InvoiceService::class);
        /** @var Transaction $transaction */
        $transaction = $objectManager->create(Transaction::class);

        $order = $this->objectManager->create(OrderInterface::class)->load($incrementId, 'increment_id');
        $codePoolId = $objectManager->create(Collection::class)->getLastItem()->getCodePoolId();
        $productOptions[Attributes::CODE_SET] = $codePoolId;

        /** @var Item $orderItem */
        $orderItem = $this->objectManager->create(Item::class)->load('amgiftcard', 'product_type');

        if ($orderItem->getId()) {
            $orderItem->setProductOptions($productOptions)->save();
        }
        $invoice = $invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);
        $transaction->addObject($invoice)->addObject($invoice->getOrder())->save();

        return $invoice;
    }
}
