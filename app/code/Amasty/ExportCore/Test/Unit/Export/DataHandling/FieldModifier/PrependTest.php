<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Prepend;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Prepend
 */
class PrependTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'default' => [
                ['input_value' => 'test'],
                'text',
                'testtext'
            ],
            'empty' => [
                ['input_value' => ''],
                'text',
                'text'
            ],
            'null' => [
                ['input_value' => null],
                'text',
                'text'
            ],
            'number' => [
                ['input_value' => 123],
                'text',
                '123text'
            ],
            'without_key' => [
                ['test_key' => 'test_value'],
                'text',
                'text'
            ]
        ];
    }

    /**
     * @param array $config
     * @param string $value
     * @param string $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, string $value, string $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Prepend::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
