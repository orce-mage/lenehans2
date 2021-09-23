<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier\Number;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Floor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Floor
 */
class FloorTest extends TestCase
{
    /**
     * @param float $value
     * @param float $expectedResult
     * @testWith [4.2, 4]
     *           [1.99999, 1]
     *           [-1.25, -2]
     */
    public function testTransform(float $value, float $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Floor::class);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
