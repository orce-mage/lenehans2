<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

/**
 * @api
 */
interface RemoveIconEntityInterface
{
    /**
     * Remove icon model and file from Stockstatus settings by option id and store id
     *
     * @param int $optionId
     * @param int $storeId
     */
    public function execute(int $optionId, int $storeId): void;
}
