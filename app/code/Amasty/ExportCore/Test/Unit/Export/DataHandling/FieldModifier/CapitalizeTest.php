<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Capitalize;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Capitalize
 */
class CapitalizeTest extends TestCase
{
    /**
     * @param string $value
     * @param string $expectedResult
     * @testWith ["test", "Test"]
     *           ["", ""]
     *           ["123", "123"]
     */
    public function testTransform(string $value, string $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Capitalize::class);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
