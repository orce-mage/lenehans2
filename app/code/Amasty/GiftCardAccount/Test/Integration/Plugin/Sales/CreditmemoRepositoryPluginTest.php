<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Plugin\Sales;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Test\Integration\Traits\CreateInvoice;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CreditmemoRepositoryPluginTest extends TestCase
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
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/order_with_giftcard_order_item.php
     */
    public function testAccountRemoveAfterMemoSaved()
    {
        /** @var Repository $accountRepo */
        $accountRepo = $this->objectManager->create(Repository::class);

        $invoice = $this->createForOrder('100000001');
        $newAccount = $accountRepo->getByCode('TEST_CODE');
        $this->assertNotNull($newAccount, __('Failed to create account from invoice.')->render());

        $order = $this->objectManager->create(OrderInterface::class)->load('100000001', 'increment_id');
        /** @var CreditmemoFactory $creditMemoFactory */
        $creditMemoFactory = $this->objectManager->create(CreditmemoFactory::class);
        /** @var CreditmemoService $creditmemoService */
        $creditmemoService = $this->objectManager->create(CreditmemoService::class);

        $creditmemo = $creditMemoFactory->createByOrder($order);
        $creditmemo->setInvoice($invoice);
        $creditmemoService->refund($creditmemo);

        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $accountRepo->getByCode('TEST_CODE');
    }
}
