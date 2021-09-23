<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Block\Adminhtml\Sales\Items\Column\Name;

use Amasty\GiftCard\Block\Adminhtml\Sales\Items\Column\Name\GiftCard;
use Amasty\GiftCard\Model\Image\Image;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCard\Block\Adminhtml\Sales\Items\Column\Name\GiftCard
 */
class GiftCardTest extends \PHPUnit\Framework\TestCase
{
    const TEST_OPTIONS = [
        'am_giftcard_amount' => '25',
        'am_giftcard_type' => '1',
        'am_giftcard_image' => '10',
        'am_giftcard_sender_name' => 'Test Sender',
        'am_giftcard_recipient_name' => 'Test Recipient',
        'am_giftcard_recipient_email' => 'Test Recipient Email',
        'am_giftcard_message' => 'Test Message',
        'am_giftcard_lifetime' => '15',
        'am_giftcard_date_delivery' => 'now',
        'am_giftcard_date_delivery_timezone' => 'Test Timezone',
        'am_giftcard_created_codes' => [
            'TEST_1'
        ]
    ];

    const TEST_RESULT = [
        [
            'label' => 'Card Value',
            'value' => '$25'
        ],
        [
            'label' => 'Card Type',
            'value' => 'Virtual'
        ],
        [
            'label' => 'Gift Card Image',
            'value' => '<img src="' . self::TEST_IMG_URL . '"  width="270px;" title="' . self::TEST_IMG_TITLE . '"/>',
            'custom_view' => true
        ],
        [
            'label' => 'Gift Card Sender',
            'value' => 'Test Sender'
        ],
        [
            'label' => 'Gift Card Recipient',
            'value' => "Test Recipient &lt;Test Recipient Email&gt;"
        ],
        [
            'label' => 'Gift Card Message',
            'value' => 'Test Message'
        ],
        [
            'label' => 'Gift Card Lifetime',
            'value' => '15 days'
        ],
        [
            'label' => 'Date of Certificate Delivery',
            'value' => 'Test Date Delivery'
        ],
        [
            'label' => 'Delivery Timezone',
            'value' => 'Test Timezone'
        ],
        [
            'label' => 'Gift Card Accounts',
            'value' => 'TEST_1<br />N/A',
            'custom_view' => true
        ]
    ];

    const TEST_IMG_PATH = 'testpath.jpg';
    const TEST_IMG_URL = 'test/image/url';
    const TEST_IMG_TITLE = 'Test Title';

    /**
     * @var GiftCard
     */
    private $block;

    /**
     * @var Item|MockObject
     */
    private $item;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var PriceCurrency|MockObject
     */
    private $priceCurrency;

    /**
     * @var Repository|MockObject
     */
    private $imageRepository;

    /**
     * @var FileUpload|MockObject
     */
    private $fileUpload;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml', 'escapeUrl']);
        $this->priceCurrency = $this->createPartialMock(PriceCurrency::class, ['format']);
        $this->imageRepository = $this->createPartialMock(Repository::class, ['getById']);
        $this->fileUpload = $this->createPartialMock(FileUpload::class, ['getImageUrl']);
        $localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatDateTime'])
            ->getMockForAbstractClass();
        $localeDate->expects($this->any())->method('formatDateTime')->willReturn('Test Date Delivery');
        $this->block = $objectManager->getObject(
            GiftCard::class,
            [
                '_escaper' => $this->escaper,
                'priceCurrency' => $this->priceCurrency,
                'imageRepository' => $this->imageRepository,
                'fileUpload' => $this->fileUpload,
                '_localeDate' => $localeDate
            ]
        );
        $this->item = $this->createPartialMock(Item::class, ['getProductOptionByCode']);
        $this->item->setQtyOrdered(2);
        $this->block->setItem($this->item);
    }

    public function testGetOrderOptions()
    {
        $this->prepareCustomOption('am_giftcard_amount', self::TEST_OPTIONS['am_giftcard_amount'], 0, 0);
        $this->initPriceCurrency(self::TEST_OPTIONS['am_giftcard_amount']);

        $this->prepareCustomOption('am_giftcard_type', self::TEST_OPTIONS['am_giftcard_type'], 1, 1);
        $this->prepareCustomOption('am_giftcard_image', self::TEST_OPTIONS['am_giftcard_image'], 2, 2);
        $this->initImage(self::TEST_OPTIONS['am_giftcard_image'], 4);
        $this->prepareCustomOption('am_giftcard_sender_name', self::TEST_OPTIONS['am_giftcard_sender_name'], 3, 5);
        $this->prepareCustomOption(
            'am_giftcard_recipient_name',
            self::TEST_OPTIONS['am_giftcard_recipient_name'],
            5,
            6
        );
        $this->prepareCustomOption(
            'am_giftcard_recipient_email',
            self::TEST_OPTIONS['am_giftcard_recipient_email'],
            6,
            7
        );
        $this->prepareCustomOption('am_giftcard_message', self::TEST_OPTIONS['am_giftcard_message'], 7, 8);
        $this->prepareCustomOption('am_giftcard_lifetime', self::TEST_OPTIONS['am_giftcard_lifetime'], 8, 9);
        $this->prepareCustomOption('am_giftcard_date_delivery', self::TEST_OPTIONS['am_giftcard_date_delivery'], 9, 10);
        $this->prepareCustomOption(
            'am_giftcard_date_delivery_timezone',
            self::TEST_OPTIONS['am_giftcard_date_delivery_timezone'],
            10,
            11
        );
        $this->prepareCustomOption('am_giftcard_created_codes', self::TEST_OPTIONS['am_giftcard_created_codes'], 11);

        $this->assertEquals(self::TEST_RESULT, $this->block->getOrderOptions());
    }

    /**
     * @param string $code
     * @param string $result
     * @param int $itemIndex
     * @param int|null $escaperIndex
     */
    protected function prepareCustomOption($code, $result, $itemIndex, $escaperIndex = null)
    {
        $this->item->expects($this->at($itemIndex))
            ->method('getProductOptionByCode')
            ->with($code)
            ->willReturn($result);

        if ($escaperIndex === null) {
            return;
        }
        $this->escaper->expects($this->at($escaperIndex))
            ->method('escapeHtml')
            ->with($result)
            ->willReturn($result);
    }

    /**
     * @param string $value
     *
     * @throws \ReflectionException
     */
    protected function initPriceCurrency($value)
    {
        $currency = $this->createPartialMock(Currency::class, []);
        $store = $this->createPartialMock(Store::class, ['getBaseCurrency']);
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $order = $this->createPartialMock(Order::class, ['getStore']);
        $order->expects($this->any())->method('getStore')->willReturn($store);
        $this->block->setOrder($order);
        $this->item->setOrder($order);

        $this->priceCurrency->expects($this->once())->method('format')->with(
            $value,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $store,
            $currency
        )->willReturn('$' . $value);
    }

    /**
     * @param int $imageId
     * @param int $escaperIndex
     *
     * @throws \ReflectionException
     */
    protected function initImage($imageId, $escaperIndex)
    {
        $image = $this->createPartialMock(Image::class, []);
        $image->setImageId($imageId);
        $image->setImagePath(self::TEST_IMG_PATH);
        $image->setIsUserUpload(true);
        $this->imageRepository->expects($this->once())->method('getById')->with($imageId)->willReturn($image);

        $this->fileUpload->expects($this->once())->method('getImageUrl')
            ->with(self::TEST_IMG_PATH, true)
            ->willReturn(self::TEST_IMG_URL);
        $this->escaper->expects($this->once())->method('escapeUrl')
            ->with(self::TEST_IMG_URL)
            ->willReturn(self::TEST_IMG_URL);
        $this->escaper->expects($this->at($escaperIndex))
            ->method('escapeHtml')
            ->willReturn(self::TEST_IMG_TITLE);
    }
}
