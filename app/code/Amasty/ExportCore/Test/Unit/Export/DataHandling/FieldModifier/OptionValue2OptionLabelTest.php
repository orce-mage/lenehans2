<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\ActionConfigBuilder;
use Amasty\ExportCore\Export\DataHandling\FieldModifier\OptionValue2OptionLabel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers EavOptionValue2OptionLabel
 */
class OptionValue2OptionLabelTest extends TestCase
{
    /**
     * @var OptionValue2OptionLabel
     */
    private $modifier;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->modifier = $objectManager->getObject(OptionValue2OptionLabel::class);
    }

    /**
     * Data provider for transform
     * @return array
     */
    public function transformDataProvider(): array
    {
        return [
            'default' => [
                [
                    'options' => [
                        ['value' => 'option_value', 'label' => 'option_label'],
                        ['value' => 'option_value2', 'label' => 'option_label2'],
                        ['value' => 'option_value3', 'label' => 'option_label3'],
                    ],
                    ActionConfigBuilder::IS_MULTISELECT => false
                ],
                'option_value',
                'option_label'
            ],
            'empty' => [
                [
                    'options' => [
                        ['value' => 'option_value', 'label' => 'option_label'],
                        ['value' => 'option_value2', 'label' => 'option_label2'],
                        ['value' => 'option_value3', 'label' => 'option_label3'],
                    ],
                    ActionConfigBuilder::IS_MULTISELECT => false
                ],
                'non_existing_option',
                ''
            ],
            'multi_select' => [
                [
                    'options' => [
                        ['value' => 'option_value', 'label' => 'option_label'],
                        ['value' => 'option_value2', 'label' => 'option_label2'],
                        ['value' => 'option_value3', 'label' => 'option_label3'],
                    ],
                    ActionConfigBuilder::IS_MULTISELECT => true
                ],
                'option_value,option_value2',
                'option_label,option_label2'
            ]
        ];
    }

    /**
     * @param array  $config
     * @param string $value
     * @param string $expectedResult
     *
     * @dataProvider transformDataProvider
     */
    public function testTransform(array $config, string $value, string $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(OptionValue2OptionLabel::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
