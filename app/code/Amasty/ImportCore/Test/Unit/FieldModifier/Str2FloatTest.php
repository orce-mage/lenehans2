<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Amasty\ImportCore\Import\DataHandling\FieldModifier\Str2Float;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Str2Float
 */
class Str2FloatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Str2Float
     */
    private $modifier;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->modifier = $objectManager->getObject(Str2Float::class);
    }

    /**
     * @param string $value
     * @param float|string $expectedResult
     * @dataProvider transformDataProvider
     */
    public function testTransform($value, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->modifier->transform($value));
    }

    /**
     * @return array
     */
    public function transformDataProvider()
    {
        return [
            ['0', 0.0],
            ['2.64', 2.64],
            ['-2.64', -2.64],
            ['', ''],
            [null, null],
            ['string', 'string']
        ];
    }
}
