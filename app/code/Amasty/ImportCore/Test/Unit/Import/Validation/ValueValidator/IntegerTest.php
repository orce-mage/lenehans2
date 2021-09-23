<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Validation\ValueValidator;

use Amasty\ImportCore\Import\Validation\ValueValidator\Integer as IntegerValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Validation\ValueValidator\Integer
 */
class IntegerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IntegerValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(IntegerValidator::class);
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
            'int'          => [['test' => '42'], 'test', true],
            'float'        => [['test' => '42.42'], 'test', false],
            'null'         => [['test' => null], 'test', true],
            'empty_string' => [['test' => ''], 'test', true],
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
