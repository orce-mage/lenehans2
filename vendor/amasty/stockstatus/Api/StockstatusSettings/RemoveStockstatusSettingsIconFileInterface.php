<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\StockstatusSettings;

/**
 * @api
 */
interface RemoveStockstatusSettingsIconFileInterface
{
    /**
     * Remove Stockstatus Icon by store id and Stockstatus attribute option id
     *
     * @param int $optionId
     * @param int $storeId
     */
    public function execute(int $optionId, int $storeId): void;
}
