<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Uppercase
 */
class UppercaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'simple_test' => ["test", "TEST"],
            'mixed'       => ["hElLo", "HELLO"],
            'unicode'     => ["ТьмАФФки", "ТЬМАФФКИ"],
        ];
    }

    /**
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(string $value, string $expectedResult)
    {
        $modifier = new \Amasty\ImportCore\Import\DataHandling\FieldModifier\Uppercase();
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
