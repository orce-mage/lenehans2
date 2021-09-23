<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Plugin\Catalog\Controller\Adminhtml\Product\Initialization;

use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\GiftCardPrice;
use Amasty\GiftCard\Model\GiftCard\GiftCardPriceRepository;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\InitializationHelperPlugin;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see InitializationHelperPlugin
 */
class InitializationHelperPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InitializationHelperPlugin
     */
    private $initializationPlugin;

    /**
     * @var Helper|MockObject
     */
    private $subject;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var GiftCardPriceRepository|MockObject
     */
    private $priceRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->subject = $this->createPartialMock(Helper::class, []);
        $this->product = $this->createPartialMock(Product::class, ['getExtensionAttributes', 'setExtensionAttributes']);

        $attribute = $this->createPartialMock(Attribute::class, []);
        $attribute->setAttributeId(10);
        $attributeRepository = $this->createPartialMock(AttributeRepository::class, ['get']);
        $attributeRepository->expects($this->any())->method('get')
            ->with(Product::ENTITY, Attributes::GIFTCARD_PRICES)
            ->willReturn($attribute);
        $this->priceRepository = $this->createPartialMock(GiftCardPriceRepository::class, ['getEmptyPriceModel']);

        $this->initializationPlugin = $objectManager->getObject(
            InitializationHelperPlugin::class,
            [
                'attributeRepository' => $attributeRepository,
                'giftCardPriceRepository' => $this->priceRepository
            ]
        );
    }

    /**
     * @covers \Amasty\GiftCard\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\InitializationHelperPlugin::beforeInitializeFromData
     *
     * @dataProvider beforeInitializeFromDataDataProvider
     */
    public function testBeforeInitializeFromData($typeId, $productData, $expectedProductData)
    {
        $this->product->setTypeId($typeId);

        $this->assertEquals(
            [$this->product, $expectedProductData],
            $this->initializationPlugin->beforeInitializeFromData(
                $this->subject,
                $this->product,
                $productData
            )
        );
    }

    /**
     * @covers \Amasty\GiftCard\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\InitializationHelperPlugin::afterInitialize
     *
     * @dataProvider afterInitializeDataProvider
     */
    public function testAfterInitialize($typeId, $amountsData, $amountCreateCount, $callCount)
    {
        $this->product->setTypeId($typeId);
        $this->product->setData(Attributes::GIFTCARD_PRICES, $amountsData);
        $amount = $this->createPartialMock(GiftCardPrice::class, []);

        $this->priceRepository->expects($this->exactly($amountCreateCount))->method('getEmptyPriceModel')
            ->willReturn($amount);

        $extension = $this->createPartialMock(ProductExtension::class, ['setAmGiftcardPrices']);
        $extension->expects($this->exactly($callCount))->method('setAmGiftcardPrices');
        $this->product->expects($this->exactly($callCount))->method('getExtensionAttributes')->willReturn($extension);
        $this->product->expects($this->exactly($callCount))->method('setExtensionAttributes')
            ->with($extension);

        $this->initializationPlugin->afterInitialize($this->subject, $this->product);
    }

    /**
     * @return array
     */
    public function beforeInitializeFromDataDataProvider()
    {
        return [
            [//gift card without amount
                GiftCard::TYPE_AMGIFTCARD,
                ['initialData'],
                ['initialData', Attributes::GIFTCARD_PRICES => []]
            ],
            [//gift card with amount
                GiftCard::TYPE_AMGIFTCARD,
                ['initialData', Attributes::GIFTCARD_PRICES => ['amount data']],
                ['initialData', Attributes::GIFTCARD_PRICES => ['amount data']]
            ],
            [//not gift card
                'wrong_product',
                ['initialData'],
                ['initialData']
            ]
        ];
    }

    /**
     * @return array
     */
    public function afterInitializeDataProvider()
    {
        return [
            [//gift card without amount
                GiftCard::TYPE_AMGIFTCARD,
                [],
                0,
                1
            ],
            [//gift card with amount
                GiftCard::TYPE_AMGIFTCARD,
                [['value' => '1,000', 'website_id' => 0]],
                1,
                1
            ],
            [//gift card with amount, no data
                GiftCard::TYPE_AMGIFTCARD,
                [['value' => '']],
                0,
                1
            ],
            [//not gift card
                'wrong_product',
                [],
                0,
                0
            ]
        ];
    }
}
