<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;

/**
 * @api
 */
interface GetByProductIdAndStoreIdInterface
{
    public function execute(int $productId, int $storeId): ?RuleInterface;
}
