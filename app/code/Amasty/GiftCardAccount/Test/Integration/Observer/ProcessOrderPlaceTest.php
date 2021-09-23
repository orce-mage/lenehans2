<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Observer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\ReadHandler;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ProcessOrderPlaceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Amasty_GiftCardAccount::Test/Integration/_files/quote_with_giftcard_account.php
     */
    public function testExecute()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $this->objectManager->create(ReadHandler::class)
            ->loadAttributes($quote);

        $quote->getShippingAddress()->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
        $quote->getPayment()->importData(['method' => 'checkmo']);

        /** @var QuoteManagement $quoteManagement */
        $quoteManagement = $this->objectManager->create(QuoteManagement::class);

        $result = $quoteManagement->submit($quote);
        $this->assertNotNull($result);

        $appliedGiftAmount = $result->getExtensionAttributes()->getAmGiftcardOrder()->getBaseGiftAmount();
        /** @var Account $account */
        $account = $this->objectManager->get(Repository::class)->getByCode('TEST_CODE_USED');

        $this->assertEquals($account->getCurrentValue(), $account->getInitialValue() - $appliedGiftAmount);
    }
}
