<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\StockstatusSettings;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;

/**
 * @api
 */
interface GetByOptionIdAndStoreIdInterface
{
    /**
     * Load Stockstatus Setting with inheritance data from default store model
     *
     * @param int $optionId
     * @param int $storeId
     * @return StockstatusSettingsInterface
     */
    public function execute(int $optionId, int $storeId): StockstatusSettingsInterface;
}
