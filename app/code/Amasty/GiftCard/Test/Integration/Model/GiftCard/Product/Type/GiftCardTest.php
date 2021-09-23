<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Test\Integration\Model\GiftCard\Product\Type;

use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Model\Image\Image;
use Amasty\GiftCard\Test\Integration\Traits\ImageUpload;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Zend\Stdlib\Parameters;

class GiftCardTest extends TestCase
{
    use ImageUpload;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_open_amount.php
     * @magentoConfigFixture current_store amgiftcard/display_options/fields am_giftcard_recipient_name,am_giftcard_sender_name,am_giftcard_date_delivery,am_giftcard_message
     */
    public function testAddOpenAmountGiftCardToBuyRequest()
    {
        $image = $this->objectManager->create(Image::class)->load('test_giftcard_image.jpg', 'image_path');

        $buyRequest = new DataObject(
            [
                'am_giftcard_amount_custom' => 20,
                'am_giftcard_sender_name' => 'test sender name',
                'am_giftcard_message' => 'test message',
                'am_giftcard_image' => $image->getImageId(),
                'is_date_delivery' => '0',
                'qty' => 1,
            ]
        );
        $quoteItem = $this->addGiftCardToQuote('am_giftcard_open_amount', $buyRequest);

        $quoteItemBuyRequest = $quoteItem->getOptionByCode('info_buyRequest');
        $this->assertTrue((bool)strpos($quoteItemBuyRequest->getValue(), '"am_giftcard_amount_custom":20'));
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_virtual_with_amounts.php
     * @magentoConfigFixture current_store amgiftcard/display_options/fields am_giftcard_recipient_name,am_giftcard_sender_name,am_giftcard_date_delivery,am_giftcard_message
     */
    public function testAddAmountsGiftCardToBuyRequest()
    {
        $image = $this->objectManager->create(Image::class)->load('test_giftcard_image.jpg', 'image_path');

        $buyRequest = new DataObject(
            [
                'am_giftcard_amount' => 20,
                'am_giftcard_recipient_name' => 'test recipient name',
                'am_giftcard_recipient_email' => 'test@recipient.email',
                'am_giftcard_sender_name' => 'test sender name',
                'am_giftcard_message' => 'test message',
                'am_giftcard_image' => $image->getImageId(),
                'is_date_delivery' => '0',
                'qty' => 1,
            ]
        );
        $quoteItem = $this->addGiftCardToQuote('am_giftcard_with_amounts', $buyRequest);

        $quoteItemBuyRequest = $quoteItem->getOptionByCode('info_buyRequest');
        $this->assertTrue((bool)strpos($quoteItemBuyRequest->getValue(), '"am_giftcard_amount":20'));
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_combined_with_fixed_amount.php
     * @magentoConfigFixture current_store amgiftcard/display_options/fields am_giftcard_recipient_name,am_giftcard_sender_name,am_giftcard_date_delivery,am_giftcard_message
     * @magentoConfigFixture current_store amgiftcard/display_options/allow_user_images 1
     */
    public function testAddFixedAmountGiftCardWithCustomImageToBuyRequest()
    {
        $this->prepareCustomImage(\Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard::IMAGE_INPUT_NAME);

        $buyRequest = new DataObject(
            [
                'am_giftcard_type' => 3,
                'am_giftcard_recipient_name' => 'test recipient name',
                'am_giftcard_recipient_email' => 'test@recipient.email',
                'am_giftcard_sender_name' => 'test sender name',
                'am_giftcard_message' => 'test message',
                'is_date_delivery' => '1',
                'am_giftcard_date_delivery' => '12/12/2022',
                'am_giftcard_date_delivery_timezone' => 'Europe/Rome',
                'qty' => 1,
            ]
        );
        $quoteItem = $this->addGiftCardToQuote('am_giftcard_fixed_amount', $buyRequest);

        $quoteItemBuyRequest = $quoteItem->getOptionByCode('info_buyRequest');
        $this->assertTrue((bool)strpos($quoteItemBuyRequest->getValue(), '"am_giftcard_image":"custom"'));

        $mediaReader = $this->objectManager->create(Filesystem::class)->getDirectoryRead(DirectoryList::MEDIA);
        $filePath = $mediaReader->getAbsolutePath(
            FileUpload::AMGIFTCARD_IMAGE_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR
            . $buyRequest->getData('am_giftcard_custom_image')
        );
        $this->assertFileExists($filePath);
    }

    /**
     * @param string $giftCardSku
     * @param DataObject $buyRequest
     * @return Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addGiftCardToQuote(string $giftCardSku, DataObject $buyRequest)
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get($giftCardSku, false, null, true);
        $quoteItem = $quote->addProduct($product, $buyRequest);

        $quote->collectTotals();
        $this->assertEquals(1, $quote->getItemsQty());

        return $quoteItem;
    }

    public function tearDown(): void
    {
        $mediaWriter = $this->objectManager->create(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaWriter->delete(
            FileUpload::AMGIFTCARD_IMAGE_MEDIA_TMP_PATH
        );
    }
}
