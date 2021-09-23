<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Data\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;

interface RangeProviderInterface
{
    /**
     * @param int $ruleId
     * @return RangeInterface[]
     */
    public function execute(int $ruleId): array;
}
