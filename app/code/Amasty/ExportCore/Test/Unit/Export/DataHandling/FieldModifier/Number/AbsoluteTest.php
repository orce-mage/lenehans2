<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier\Number;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Absolute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Absolute
 */
class AbsoluteTest extends TestCase
{
    /**
     * @param float $value
     * @param float $expectedResult
     * @testWith [-123, 123]
     *           [-0.52, 0.52]
     *           [-0, 0]
     */
    public function testTransform(float $value, float $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Absolute::class);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
