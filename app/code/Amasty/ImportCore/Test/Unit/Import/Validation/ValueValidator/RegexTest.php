<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Validation\ValueValidator;

use Amasty\ImportCore\Import\Validation\ValueValidator\Regex as RegexValidator;

/**
 * @covers \Amasty\ImportCore\Import\Validation\ValueValidator\Regex
 */
class RegexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for validate test
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'simple_match'     => ['test', ['test' => 'test'], 'test', true],
            'simple_mismatch'  => ['tezd', ['test' => 'test'], 'test', false],
            'letter'           => ['^[a-z]$', ['test' => 'test'], 'test', false],
            'word'             => ['^[a-z]+$', ['test' => 'test'], 'test', true],
            'number'           => ['^\d+$', ['test' => '123'], 'test', true],
            'multiline'        => ['def', ['test' => 'abc\ndef\nghi'], 'test', true],
            'case_insensivity' => ['hello', ['test' => 'Hello'], 'test', true],
            'no_index'         => ['test', ['test_1' => ''], 'test', true]
        ];
    }

    /**
     * @param string $regex
     * @param array $row
     * @param string $field
     * @param bool $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testValidate(string $regex, array $row, string $field, bool $expectedResult)
    {
        $validator = new RegexValidator([RegexValidator::REGEX => $regex]);
        $this->assertSame($expectedResult, $validator->validate($row, $field));
    }

    public function testBrokenRegex()
    {
        $this->expectException(\LogicException::class);

        $validator = new RegexValidator([RegexValidator::REGEX => 'Problems(officer?']);
        $validator->validate(['field_name' => 'value'], 'field_name');
    }

    public function testMisconfiguration()
    {
        $this->expectException(\LogicException::class);

        new RegexValidator([]);
    }
}
