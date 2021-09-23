<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Explode;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Explode
 */
class ExplodeTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'default' => [
                ['input_value' => ','],
                'test,test2,test3',
                ['test', 'test2', 'test3']
            ],
            'empty' => [
                ['input_value' => ' '],
                'test test 2',
                ['test', 'test', '2']
            ],
            'null' => [
                ['input_value' => null],
                'test',
                'test'
            ],
            'without_key' => [
                ['test_key' => 'test_value'],
                'test',
                'test'
            ]
        ];
    }

    /**
     * @param array $config
     * @param string $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, string $value, $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Explode::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
