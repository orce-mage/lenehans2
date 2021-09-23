<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Trim
 */
class TrimTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $value
     * @param $expectedResult
     * @testWith ["test", "test"]
     *           [" test      ", "test"]
     *           ["   ", ""]
     */
    public function testTransform(string $value, string $expectedResult)
    {
        $modifier = new \Amasty\ImportCore\Import\DataHandling\FieldModifier\Trim();
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
