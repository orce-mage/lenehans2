<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Test\Integration\Traits\CreateInvoice;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OrderWithGiftcardProcessingTest extends TestCase
{
    use CreateInvoice;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Amasty_GiftCardAccount::Test/Integration/_files/order_with_applied_giftcard.php
     */
    public function testInvoiceAndMemoCreation()
    {
        $order = $this->objectManager->create(OrderInterface::class)->load('100000001', 'increment_id');
        $this->objectManager->create(ReadHandler::class)->loadAttributes($order);

        /** @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order $gCardOrder */
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
        $invoice = $this->createForOrder('100000001');

        /** @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Invoice $gCardInvoice */
        $gCardInvoice = $invoice->getExtensionAttributes()->getAmGiftcardInvoice();
        $this->assertEquals($gCardOrder->getBaseInvoiceGiftAmount(), $gCardInvoice->getBaseGiftAmount());

        /** @var CreditmemoFactory $creditMemoFactory */
        $creditMemoFactory = $this->objectManager->create(CreditmemoFactory::class);
        /** @var CreditmemoService $creditmemoService */
        $creditmemoService = $this->objectManager->create(CreditmemoService::class);

        $creditmemo = $creditMemoFactory->createByOrder($order);
        $creditmemo->setInvoice($invoice);
        $creditmemoService->refund($creditmemo);

        /** @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Creditmemo $gCardMemo */
        $gCardMemo = $creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo();
        $this->assertEquals($gCardOrder->getBaseRefundGiftAmount(), $gCardMemo->getBaseGiftAmount());
    }
}
