<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Validation\ValueValidator;

use Amasty\ImportCore\Import\Validation\ValueValidator\Number as NumberValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Validation\ValueValidator\Number
 */
class NumberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NumberValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(NumberValidator::class);
    }

    /**
     * Data provider for field validator test
     *
     * @return array
     */
    public function validatorDataProvider(): array
    {
        return [
            'not_int'      => [['test' => 'test_value'], 'test', false],
            'empty_string' => [['test' => ''], 'test', true],
            'space'        => [['test' => ' '], 'test', false],
            'int'          => [['test' => '42'], 'test', true],
            'float'        => [['test' => '42.42'], 'test', true],
            'null'         => [['test' => null], 'test', true],
            'long'         => [['test' => '2.2E-5'], 'test', true],
            'no_index'     => [['test_1' => ''], 'test', true]
        ];
    }
    /**
     * @param array $row
     * @param string $field
     * @param $expectedResult
     * @dataProvider validatorDataProvider
     */
    public function testValidate(array $row, string $field, bool $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->validator->validate($row, $field)
        );
    }
}
