<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\ActionConfigBuilder;
use Amasty\ExportCore\Export\DataHandling\FieldModifier\EavOptionValue2OptionLabel;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Element\Select;
use PHPUnit\Framework\TestCase;

/**
 * @covers EavOptionValue2OptionLabel
 */
class EavOptionValue2OptionLabelTest extends TestCase
{
    public function transformDataProvider(): array
    {
        return [
            'default' => [
                [
                    'option_value' => 'option_label',
                    'option_value2' => 'option_label2',
                    'option_value3' => 'option_label3'
                ],
                [
                    ActionConfigBuilder::EAV_ENTITY_TYPE_CODE => 'catalog_product',
                    'field' => 'test'
                ],
                'frontend_type' => Select::NAME,
                'option_value',
                'option_label'
            ],
            'empty' => [
                [
                    'option_value' => 'option_label',
                    'option_value2' => 'option_label2',
                    'option_value3' => 'option_label3'
                ],
                [
                    ActionConfigBuilder::EAV_ENTITY_TYPE_CODE => 'catalog_product',
                    'field' => 'test'
                ],
                'frontend_type' => Select::NAME,
                'non_existing_option',
                'non_existing_option'
            ],
            'multi_select' => [
                [
                    'option_value' => 'option_label',
                    'option_value2' => 'option_label2',
                    'option_value3' => 'option_label3'
                ],
                [
                    ActionConfigBuilder::EAV_ENTITY_TYPE_CODE => 'catalog_product',
                    'field' => 'test'
                ],
                'frontend_type' => MultiSelect::NAME,
                'option_value,option_value2',
                'option_label,option_label2'
            ],
        ];
    }

    /**
     * @param array  $map
     * @param array  $config
     * @param string $frontendType
     * @param string $value
     * @param string $expectedResult
     *
     * @dataProvider transformDataProvider
     */
    public function testTransform(
        array $map,
        array $config,
        string $frontendType,
        string $value,
        string $expectedResult
    ) {
        $objectManager = new ObjectManager($this);
        $attributeMock = $this->createMock(AbstractAttribute::class);
        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendType);

        $eavConfigMock = $this->createMock(EavConfig::class);
        $eavConfigMock->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);
        $modifier = $objectManager->getObject(
            EavOptionValue2OptionLabel::class,
            [
                'eavConfig' => $eavConfigMock,
                'map' => $map,
                'config' => $config
            ]
        );

        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
