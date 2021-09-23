<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Validation\ValueValidator;

use Amasty\ImportCore\Import\Validation\ValueValidator\NotEmpty as NotEmptyValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Validation\ValueValidator\NotEmpty
 */
class NotEmptyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NotEmptyValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(
            NotEmptyValidator::class,
            ['config' => []]
        );
    }

    /**
     * Data provider for field validator test
     *
     * @return array
     */
    public function validatorDataProvider(): array
    {
        return [
            'not_empty' => [['field' => 'test_value'], 'field', [], true],
            'empty' => [['field' => ''], 'field', [], false],
            'null' => [['field' => null], 'field', [], false],
            'space' => [['field' => ' '], 'field', [], false],
            'no_index' => [['field_1' => ''], 'field_1', [], false],
            'zero_allowed' => [
                ['field' => '0'],
                'field',
                ['isZeroValueAllowed' => true],
                true
            ],
            'zero_not_allowed' => [
                ['field' => '0'],
                'field',
                ['isZeroValueAllowed' => false],
                false
            ]
        ];
    }

    /**
     * @param array $row
     * @param string $field
     * @param array $config
     * @param bool $expectedResult
     * @dataProvider validatorDataProvider
     */
    public function testValidate(
        array $row,
        string $field,
        array $config,
        bool $expectedResult
    ) {
        $reflection = new \ReflectionClass(NotEmptyValidator::class);

        $configProp = $reflection->getProperty('config');
        $configProp->setAccessible(true);
        $configProp->setValue($this->validator, $config);

        $this->assertSame(
            $expectedResult,
            $this->validator->validate($row, $field)
        );
    }
}
