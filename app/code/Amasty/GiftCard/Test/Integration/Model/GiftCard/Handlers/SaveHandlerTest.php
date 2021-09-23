<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Test\Integration\Model\GiftCard\Handlers;

use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Amasty\GiftCard\Model\GiftCard\Handlers\SaveHandler;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->saveHandler = $this->objectManager->get(SaveHandler::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_virtual_with_amounts.php
     */
    public function testExecuteAmountsAdded()
    {
        $giftCard = $this->productRepository->get('am_giftcard_with_amounts');
        $amounts = $giftCard->getExtensionAttributes()->getAmGiftcardPrices();
        $originalAmountsCount = count($amounts);

        /** @var GiftCardPriceInterface $newAmount */
        $newAmount = $this->objectManager->create(GiftCardPriceInterface::class);
        $newAmount->setValue(50)->setWebsiteId(0);
        $amounts[] = $newAmount;

        $giftCard->getExtensionAttributes()->setAmGiftcardPrices($amounts);
        $this->saveHandler->execute($giftCard);

        $giftCard = $this->productRepository->get('am_giftcard_with_amounts');
        $amounts = $giftCard->getExtensionAttributes()->getAmGiftcardPrices();

        $this->assertEquals($originalAmountsCount + 1, count($amounts));
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_virtual_with_amounts.php
     */
    public function testExecuteRemoveAllAmounts()
    {
        $giftCard = $this->productRepository->get('am_giftcard_with_amounts');
        $giftCard->getExtensionAttributes()->setAmGiftcardPrices([]);
        $this->saveHandler->execute($giftCard);

        $giftCard = $this->productRepository->get('am_giftcard_with_amounts');
        $amounts = $giftCard->getExtensionAttributes()->getAmGiftcardPrices();

        $this->assertEmpty($amounts);
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_virtual_with_amounts.php
     */
    public function testExecuteAmountsReplaced()
    {
        $giftCard = $this->productRepository->get('am_giftcard_with_amounts');

        /** @var GiftCardPriceInterface $newAmount */
        $newAmount = $this->objectManager->create(GiftCardPriceInterface::class);
        $newAmount->setValue(50)->setWebsiteId(0);
        $giftCard->getExtensionAttributes()->setAmGiftcardPrices([$newAmount]);
        $this->saveHandler->execute($giftCard);

        $giftCard = $this->productRepository->get('am_giftcard_with_amounts');
        $amounts = array_values($giftCard->getExtensionAttributes()->getAmGiftcardPrices());

        $this->assertEquals(1, count($amounts));
        $this->assertEquals(50, $amounts[0]->getValue());
    }
}
