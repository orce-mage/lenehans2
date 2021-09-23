<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Helper\Catalog\Product;

use Amasty\GiftCard\Helper\Catalog\Product\Configuration;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\OptionSource\GiftCardOption;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCard\Helper\Catalog\Product\Configuration
 */
class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    const AMOUNT_FORMATED_VALUE = '$25';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Item|MockObject
     */
    private $item;

    /**
     * @var PriceCurrency|MockObject
     */
    private $priceCurrency;

    /**
     * @var ConfigProvider|MockObject
     */
    private $configProvider;

    /**
     * @var GiftCardOption|MockObject
     */
    private $giftCardOption;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->item = $this->createPartialMock(Item::class, ['getProduct']);
        $this->priceCurrency = $this->createPartialMock(PriceCurrency::class, ['convertAndFormat']);
        $this->configProvider = $this->createPartialMock(
            ConfigProvider::class,
            ['isEnabled', 'isShowOptionsInCartAndCheckout']
        );
        $this->giftCardOption = $this->createPartialMock(GiftCardOption::class, []);
        $productConfig = $this->createPartialMock(
            \Magento\Catalog\Helper\Product\Configuration::class,
            ['getCustomOptions']
        );
        $productConfig->expects($this->any())->method('getCustomOptions')->with($this->item)->willReturn([]);
        $escaper = $this->createPartialMock(\Magento\Framework\Escaper::class, ['escapeHtml']);
        $escaper->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->configuration = $objectManager->getObject(
            Configuration::class,
            [
                'configProvider' => $this->configProvider,
                'giftCardOption' => $this->giftCardOption,
                'priceCurrency' => $this->priceCurrency,
                'productConfig' => $productConfig,
                'escaper' => $escaper
            ]
        );
    }

    /**
     * @dataProvider getGiftCardOptionsDataProvider
     */
    public function testGetOptions($isEnabled, $customOptionsData, $expected)
    {
        $this->configProvider->expects($this->once())->method('isEnabled')->willReturn($isEnabled);
        $this->configProvider->expects($this->any())->method('isShowOptionsInCartAndCheckout')->willReturn($isEnabled);
        $customOptions = [];

        foreach ($customOptionsData as $optionData) {
            $optionMock = $this->createPartialMock(Option::class, []);
            $optionMock->setData($optionData);

            if ($optionData['code'] === 'am_giftcard_amount') {
                $this->priceCurrency->expects($this->once())
                    ->method('convertAndFormat')
                    ->with($optionData['value'])
                    ->willReturn(self::AMOUNT_FORMATED_VALUE);
            }
            $customOptions[] = $optionMock;
        }
        $product = $this->createPartialMock(Product::class, []);
        $product->setCustomOptions($customOptions);

        $this->item->expects($this->any())->method('getProduct')->willReturn($product);
        $result = $this->configuration->getOptions($this->item);
        $this->assertEquals(
            $result,
            $expected
        );
    }

    /**
     * @return array
     */
    public function getGiftCardOptionsDataProvider()
    {
        return [
            [ //first assertion - module disabled
                false,
                [],
                []
            ],
            [ //second assertion - valid gift card options
                true,
                [
                    [
                        'code' => 'am_giftcard_sender_name',
                        'value' => 'Test1'
                    ],
                    [
                        'code' => 'am_giftcard_recipient_name',
                        'value' => 'Test2'
                    ],
                    [
                        'code' => 'am_giftcard_date_delivery',
                        'value' => 'Test3'
                    ]
                ],
                [
                    [
                        'label' => 'Sender Name',
                        'value' => 'Test1'
                    ],
                    [
                        'label' => 'Recipient Name',
                        'value' => 'Test2'
                    ],
                    [
                        'label' => 'Delivery Date',
                        'value' => 'Test3'
                    ]
                ]
            ],
            [ //third assertion - one invalid gift card option
                true,
                [
                    [
                    'code' => 'am_giftcard_sender_name',
                    'value' => 'Test1'
                    ],
                    [
                    'code' => 'am_giftcard_recipient_name',
                    'value' => 'Test2'
                    ],
                    [
                    'code' => 'buy_request',
                    'value' => 'Invalid'
                    ]
                ],
                [
                    [
                    'label' => 'Sender Name',
                    'value' => 'Test1'
                    ],
                    [
                    'label' => 'Recipient Name',
                    'value' => 'Test2'
                    ]
                ]
            ],
            [ //fourth assertion - valid option without value
              true,
              [
                  [
                      'code' => 'am_giftcard_sender_name',
                      'value' => 'Test1'
                  ],
                  [
                      'code' => 'am_giftcard_recipient_name',
                      'value' => ''
                  ]
              ],
              [
                  [
                      'label' => 'Sender Name',
                      'value' => 'Test1'
                  ]
              ]
            ],
            [ //fifth assertion - amount option
              true,
              [
                  [
                      'code' => 'am_giftcard_amount',
                      'value' => 25.50
                  ]
              ],
              [
                  [
                      'label' => 'Card Value',
                      'value' => self::AMOUNT_FORMATED_VALUE
                  ]
              ]
            ]
        ];
    }
}
