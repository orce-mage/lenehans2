<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\Validation\ValueValidator;

use Amasty\ImportCore\Import\Validation\ValueValidator\GreaterThanZero as GreaterThanZeroValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Validation\ValueValidator\GreaterThanZero
 */
class GreaterThanZeroTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GreaterThanZeroValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(GreaterThanZeroValidator::class);
    }

    /**
     * Data provider for field validator test
     *
     * @return array
     */
    public function validatorDataProvider(): array
    {
        return [
            'greater_than_zero' => [['test' => 1], 'test', true],
            'less_than_zero' => [['test' => -1], 'test', false],
            'zero' => [['test' => 0], 'test', false],
            'null' => [['test' => null], 'test', true],
            'empty_string' => [['test' => ''], 'test', true],
            'no_index' => [['test_1' => ''], 'test', true]
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
