<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Api;

use Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GiftCardAccountManagementInterfaceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GiftCardAccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->create(GiftCardAccountManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Amasty_GiftCardAccount::Test/Integration/_files/giftcard_account.php
     * @magentoDataFixture Amasty_GiftCardAccount::Test/Integration/_files/quote_with_address_and_product.php
     */
    public function testApplyAndRemoveGiftCard()
    {
        // Adding Gift Card Account to cart
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $oldGrandTotal = $quote->getBaseGrandTotal();
        $quoteId = $quote->getId();

        $this->accountManagement->applyGiftCardToCart($quoteId, 'TEST_CODE_USED');
        $quote = $this->cartRepository->getActive($quoteId);
        $this->assertEquals(0, $quote->getBaseGrandTotal());
        $this->assertEquals(0, $quote->getGrandTotal());

        $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
        $this->assertEquals($oldGrandTotal, $gCardQuote->getBaseGiftAmountUsed());

        //Removing Gift Card Account from cart
        $this->accountManagement->removeGiftCardFromCart($quoteId, 'TEST_CODE_USED');
        $quote = $this->cartRepository->getActive($quoteId);

        $this->assertEquals(20, $quote->getBaseGrandTotal());
        $this->assertEquals(20, $quote->getGrandTotal());

        $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
        $this->assertEquals(0, $gCardQuote->getBaseGiftAmountUsed());
    }
}
