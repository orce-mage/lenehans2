<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier\Number;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Round;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Round
 */
class RoundTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'default' => [
                ['input_value' => 5],
                120.11111111,
                120.11111
            ],
            'float' => [
                ['input_value' => 4.4],
                123.88888888,
                123.8889
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
     * @param float $value
     * @param float $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, float $value, float $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Round::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
