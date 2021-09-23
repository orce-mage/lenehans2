<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Test\Unit\Model\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Model\Range;
use Amasty\Stockstatus\Model\Range\GetTargetStatusId;
use Amasty\Stockstatus\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Stockstatus\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Amasty\Stockstatus\Model\Range\GetTargetStatusId.
 *
 * Class GetTargetStatusIdTest
 *
 * @see GetTargetStatusId
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetTargetStatusIdTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var GetTargetStatusId
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(GetTargetStatusId::class);
    }

    /**
     * @covers GetTargetStatusId::execute
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $rangesData, ?int $expectedStatusId): void
    {
        $ranges = [];
        foreach ($rangesData as $rangeData) {
            $ranges[] = $this->getObjectManager()->getObject(Range::class, [
                'data' => $rangeData
            ]);
        }

        $this->assertEquals($expectedStatusId, $this->model->execute($ranges));
    }

    public function executeDataProvider(): array
    {
        return [
            [
                [
                    [RangeInterface::FROM => 1, RangeInterface::TO => 3, RangeInterface::STATUS_ID => 1]
                ],
                1
            ],
            [
                [
                    [RangeInterface::FROM => 1, RangeInterface::TO => 3, RangeInterface::STATUS_ID => 1],
                    [RangeInterface::FROM => 2, RangeInterface::TO => 3, RangeInterface::STATUS_ID => 2]
                ],
                2
            ],
            [
                [
                    [RangeInterface::FROM => 1, RangeInterface::TO => 30, RangeInterface::STATUS_ID => 1],
                    [RangeInterface::FROM => 2, RangeInterface::TO => 33, RangeInterface::STATUS_ID => 2]
                ],
                1
            ],
            [
                [
                    [RangeInterface::FROM => 1, RangeInterface::TO => 30, RangeInterface::STATUS_ID => 1],
                    [RangeInterface::FROM => 2, RangeInterface::TO => 20, RangeInterface::STATUS_ID => 2],
                    [RangeInterface::FROM => 15, RangeInterface::TO => 20, RangeInterface::STATUS_ID => 3],
                    [RangeInterface::FROM => 17, RangeInterface::TO => 21, RangeInterface::STATUS_ID => 4],
                ],
                3
            ]
        ];
    }
}
