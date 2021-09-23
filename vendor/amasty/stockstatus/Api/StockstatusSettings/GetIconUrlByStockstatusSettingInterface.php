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
interface GetIconUrlByStockstatusSettingInterface
{
    /**
     * Get Stockstatus icon url (if exists) by Stockstatus Settings model
     *
     * @param StockstatusSettingsInterface $stockstatusSettings
     * @return string|null
     */
    public function execute(StockstatusSettingsInterface $stockstatusSettings): ?string;
}
