<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier\Number;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Modulo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Modulo
 */
class ModuloTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'default' => [
                ['input_value' => 123],
                390,
                21
            ],
            'float' => [
                ['input_value' => 12.6],
                123.88,
                3
            ],
            'null' => [
                ['input_value' => null],
                123,
                123
            ],
            'without_key' => [
                ['test_key' => 'test_value'],
                123,
                123
            ]
        ];
    }

    /**
     * @param array $config
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, $value, $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Modulo::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
