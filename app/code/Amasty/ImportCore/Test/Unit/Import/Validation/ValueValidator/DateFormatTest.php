<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\Validation\ValueValidator;

use Amasty\ImportCore\Import\Validation\ValueValidator\DateFormat as DateFormatValidator;

/**
 * @covers \Amasty\ImportCore\Import\Validation\ValueValidator\DateFormat
 */
class DateFormatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for field validator test
     *
     * @return array
     */
    public function validatorDataProvider(): array
    {
        return [
            'correct_format' => ['Y-m-d H:i:s', ['test' => '2020-08-16 20:58:38'], 'test', true],
            'wrong_format' => ['Y-m-d H:i:s', ['test' => '2020-08-16'], 'test', false],
            'no_index' => ['Y-m-d H:i:s', ['test_1' => '2020-08-16'], 'test', true],
            'null' => ['Y-m-d H:i:s', ['test' => null], 'test', true],
            'empty_string' => ['Y-m-d H:i:s', ['test' => ''], 'test', true],
        ];
    }

    public function validateWrongValuesDataProvider(): array
    {
        return [
            'string' => [['test' => 'test'], \Exception::class],
            'int' => [['test' => 123], \TypeError::class],
            'float' => [['test' => 23.34], \TypeError::class]
        ];
    }

    /**
     * @param string $format
     * @param array $row
     * @param string $field
     * @param bool $expectedResult
     * @dataProvider validatorDataProvider
     */
    public function testValidate(string $format, array $row, string $field, bool $expectedResult)
    {
        $validator = new DateFormatValidator([DateFormatValidator::FORMAT => $format]);

        $this->assertSame(
            $expectedResult,
            $validator->validate($row, $field)
        );
    }

    /**
     * @param array $row
     * @param string $exception
     * @dataProvider validateWrongValuesDataProvider
     */
    public function testValidateWrongValues(array $row, string $exception)
    {
        $validator = new DateFormatValidator([DateFormatValidator::FORMAT => 'Y']);
        $this->expectException($exception);

        $validator->validate($row, 'test');
    }

    public function testMisconfiguration()
    {
        $this->expectException(\LogicException::class);

        new DateFormatValidator([]);
    }
}
