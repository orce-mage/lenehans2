<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Test\Unit\Model\Export\Adapter;

use Amasty\Feed\Model\Export\Adapter\Csv;
use Amasty\Feed\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CsvTest
 *
 * @see Csv
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CsvTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const DATE = [
            'format' => 'date'
    ];
    const PRICE = [
            'format' => 'price'
    ];
    const INTEGER = [
            'format' => 'integer'
    ];

    /**
     * @covers Csv::_formatValue
     *
     * @dataProvider formatValueDataProvider
     *
     * @throws \ReflectionException
     */
    public function testFormatValue($field, $value, $expectedResult)
    {
        /** @var Csv|MockObject $model */
        $model = $this->createPartialMock(Csv::class, ['getCurrencyRate']);
        $model->expects($this->any())->method('getCurrencyRate')->willReturn(2);

        $this->setProperty($model, '_formatDate', "Y", Csv::class);
        $this->setProperty($model, '_formatPriceDecimals', 2, Csv::class);
        $this->setProperty($model, '_formatPriceDecimalPoint', ",", Csv::class);
        $this->setProperty($model, '_formatPriceThousandsSeparator', " ", Csv::class);
        $this->setProperty($model, '_formatPriceCurrencyShow', true, Csv::class);
        $this->setProperty($model, '_formatPriceCurrency', "$", Csv::class);

        $result = $this->invokeMethod($model, '_formatValue', [$field, $value]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for _formatValue test
     * @return array
     */
    public function formatValueDataProvider()
    {
        return [
            [self::PRICE, 50, '100,00 $'],
            [self::DATE, '10 September 2000', 2000],
            [self::INTEGER, 1, 1],
            ['wrongFormat', 1, 1],
        ];
    }
}
