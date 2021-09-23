<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\GiftCard\DataProvider\Product\Form\Modifier;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\DataProvider\Product\Form\Modifier\GiftCard;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard as GiftCardProduct;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Currency;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Model\Locator\RegistryLocator;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCard\Model\GiftCard\DataProvider\Product\Form\Modifier\GiftCard
 */
class GiftCardTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_ID = 1;
    const CONFIG_EMAIL = 'config_email';
    const CONFIG_LIFETIME = 15;

    /**
     * @var GiftCard
     */
    private $modifier;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var ConfigProvider|MockObject
     */
    private $configProvider;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['isScopeGlobal'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attribute->expects($this->any())->method('isScopeGlobal')->willReturn(true);
        $productResource = $this->getMockBuilder(AbstractDb::class)
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productResource->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $this->product = $this->createPartialMock(Product::class, ['getResource']);
        $this->product->expects($this->any())->method('getResource')->willReturn($productResource);
        $this->product->setWebsiteIds([]);

        $currency = $this->createPartialMock(Currency::class, ['getCurrencySymbol']);
        $currency->expects($this->any())->method('getCurrencySymbol')->willReturn('$');
        $store = $this->createPartialMock(Store::class, ['getBaseCurrency']);
        $store->expects($this->any())->method('getBaseCurrency')->willReturn($currency);
        $locator = $objectManager->getObject(
            RegistryLocator::class,
            [
                'store' => $store,
                'product' => $this->product
            ]
        );

        $directoryHelper = $this->createPartialMock(Data::class, ['getBaseCurrencyCode']);
        $directoryHelper->expects($this->any())->method('getBaseCurrencyCode')->willReturn('USD');

        $storeManager = $this->createPartialMock(StoreManager::class, ['getWebsites', 'isSingleStoreMode']);
        $storeManager->expects($this->any())->method('getWebsites')->willReturn([]);
        $storeManager->expects($this->any())->method('isSingleStoreMode')->willReturn(false);

        $this->configProvider = $this->createPartialMock(ConfigProvider::class, ['getEmailTemplate', 'getLifetime']);
        $arrayManager = $this->createPartialMock(ArrayManager::class, []);

        $this->modifier = $objectManager->getObject(
            GiftCard::class,
            [
                'locator' => $locator,
                'directoryHelper' => $directoryHelper,
                'storeManager' => $storeManager,
                'configProvider' => $this->configProvider,
                'arrayManager' => $arrayManager
            ]
        );
    }

    /**
     * @dataProvider modifyDataDataProvider
     */
    public function testModifyData($productData, $expectedProductData)
    {
        $data = [
            self::PRODUCT_ID => [
                'product' => $productData
            ]
        ];
        $expected = [
            self::PRODUCT_ID => [
                'product' => $expectedProductData
            ]
        ];
        $this->product->setTypeId(GiftCardProduct::TYPE_AMGIFTCARD);
        $this->product->setId(self::PRODUCT_ID);

        $this->configProvider->expects($this->any())->method('getEmailTemplate')->willReturn(self::CONFIG_EMAIL);
        $this->configProvider->expects($this->any())->method('getLifetime')->willReturn(self::CONFIG_LIFETIME);

        $result = $this->modifier->modifyData($data);
        $this->assertEquals($expected, $result);
    }

    public function testModifyMeta()
    {
        $meta = [
            'amasty-gift-card-prices' => [
                'children' => [
                    'container_am_giftcard_prices' => [
                        'children' => [
                            'am_giftcard_prices' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'sortOrder' => 0
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'gift-card-information' => [
                'children' => [
                    'container_am_email_template' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => 'Label'
                                ]
                            ]
                        ],
                        'children' => [
                            'am_email_template' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'sortOrder' => 10
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'container_am_giftcard_lifetime' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => 'Label'
                                ]
                            ]
                        ],
                        'children' => [
                            'am_giftcard_lifetime' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'sortOrder' => 20
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->product->setTypeId(GiftCardProduct::TYPE_AMGIFTCARD);

        $result = $this->modifier->modifyMeta($meta);
        $modifiedPricesConfig =
            $result['amasty-gift-card-prices']['children']['container_am_giftcard_prices']
            ['children']['am_giftcard_prices']['arguments']['data']['config'];
        $this->assertEquals('dynamicRows', $modifiedPricesConfig['componentType']);
        $this->assertArrayHasKey(
            'use_config_am_email_template',
            $result['gift-card-information']['children']['container_am_email_template']['children']
        );
        $this->assertArrayHasKey(
            'use_config_am_giftcard_lifetime',
            $result['gift-card-information']['children']['container_am_giftcard_lifetime']['children']
        );
    }

    public function testModifyDataNotGiftCard()
    {
        $data = [
            'wrong_product'
        ];
        $this->product->setTypeId('not_amgiftcard');
        $this->assertEquals($data, $this->modifier->modifyData($data));
    }

    public function testModifyMetaNotGiftCard()
    {
        $meta = [
            'wrong_product'
        ];
        $this->product->setTypeId('not_amgiftcard');
        $this->assertEquals($meta, $this->modifier->modifyMeta($meta));
    }

    /**
     * @return array
     */
    public function modifyDataDataProvider()
    {
        return [
            [//first assertion - saved values
                [
                    Attributes::EMAIL_TEMPLATE => 'test',
                    Attributes::GIFTCARD_LIFETIME => 10
                ],
                [
                    Attributes::EMAIL_TEMPLATE => 'test',
                    Attributes::GIFTCARD_LIFETIME => 10
                ]
            ],
            [//second assertion - no values
                [],
                [
                    Attributes::EMAIL_TEMPLATE => self::CONFIG_EMAIL,
                    Attributes::GIFTCARD_LIFETIME => self::CONFIG_LIFETIME,
                    GiftCard::FIELD_CONFIG_PREFIX . Attributes::EMAIL_TEMPLATE => '1',
                    GiftCard::FIELD_CONFIG_PREFIX . Attributes::GIFTCARD_LIFETIME => '1',
                ]
            ],
            [//third assertion config values
                [
                    Attributes::EMAIL_TEMPLATE => Attributes::ATTRIBUTE_CONFIG_VALUE,
                    Attributes::GIFTCARD_LIFETIME => Attributes::ATTRIBUTE_CONFIG_VALUE
                ],
                [
                    Attributes::EMAIL_TEMPLATE => self::CONFIG_EMAIL,
                    Attributes::GIFTCARD_LIFETIME => self::CONFIG_LIFETIME,
                    GiftCard::FIELD_CONFIG_PREFIX . Attributes::EMAIL_TEMPLATE => '1',
                    GiftCard::FIELD_CONFIG_PREFIX . Attributes::GIFTCARD_LIFETIME => '1',
                ]
            ]
        ];
    }
}
