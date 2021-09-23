<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Test\Integration\Model\GiftCard\Handlers;

use Amasty\GiftCard\Model\GiftCard\Handlers\ReadHandler;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    const GIFT_CARD_AMOUNT_1 = 10;
    const GIFT_CARD_AMOUNT_2 = 20;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->readHandler = $this->objectManager->create(ReadHandler::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_open_amount.php
     */
    public function testExecuteOpenAmount()
    {
        $giftCard = $this->productRepository->get(
            'am_giftcard_open_amount',
            false,
            null,
            true
        );
        $giftCard = $this->readHandler->execute($giftCard);

        /** @var \Amasty\GiftCard\Api\GiftCardPriceRepositoryInterface[] $giftCardAmounts */
        $giftCardAmounts = $giftCard->getExtensionAttributes()->getAmGiftcardPrices();
        $this->assertEmpty($giftCardAmounts);
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_combined_with_fixed_amount.php
     */
    public function testExecuteFixedAmount()
    {
        $giftCard = $this->productRepository->get(
            'am_giftcard_fixed_amount',
            false,
            null,
            true
        );
        $giftCard = $this->readHandler->execute($giftCard);

        /** @var \Amasty\GiftCard\Api\GiftCardPriceRepositoryInterface[] $giftCardAmounts */
        $giftCardAmounts = array_values($giftCard->getExtensionAttributes()->getAmGiftcardPrices());
        $this->assertEquals(self::GIFT_CARD_AMOUNT_1, $giftCardAmounts[0]->getValue());
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_virtual_with_amounts.php
     */
    public function testExecuteTwoAmounts()
    {
        $giftCard = $this->productRepository->get(
            'am_giftcard_with_amounts',
            false,
            null,
            true
        );
        $this->readHandler->execute($giftCard);

        /** @var \Amasty\GiftCard\Api\GiftCardPriceRepositoryInterface[] $giftCardAmounts */
        $giftCardAmounts = array_values($giftCard->getExtensionAttributes()->getAmGiftcardPrices());
        $this->assertEquals(self::GIFT_CARD_AMOUNT_1, $giftCardAmounts[0]->getValue());
        $this->assertEquals(self::GIFT_CARD_AMOUNT_2, $giftCardAmounts[1]->getValue());
    }
}
