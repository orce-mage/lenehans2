<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;

class GetTargetStatusId
{
    /**
     * Resolve right range in case
     * range1 (10-100) range2 (20-30) range3 (25-29), requested qty - 26
     * in result - target range is range3
     * Ranges argument must be ranges sorted FROM ASC, TO ASC
     *
     * @param RangeInterface[] $ranges
     * @return int|null
     */
    public function execute(array $ranges): ?int
    {
        $priorityRange = array_shift($ranges);

        if ($priorityRange) {
            $statusId = $priorityRange->getStatusId();
            $qtyFrom = $priorityRange->getFrom();
            $qtyTo = $priorityRange->getTo();

            foreach ($ranges as $range) {
                $toCheck = $qtyTo <=> $range->getTo();
                $fromCheck = $range->getFrom() <=> $qtyFrom;

                if ($toCheck + $fromCheck > 0) {
                    $qtyTo = $range->getTo();
                    $qtyFrom = $range->getFrom();
                    $statusId = $range->getStatusId();
                }
            }
        }

        return $statusId ?? null;
    }
}
