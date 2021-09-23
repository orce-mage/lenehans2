<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface GetByStockstatusSettingInterface
{
    /**
     * Convert Stockstatus settings model to icon model
     *
     * @param StockstatusSettingsInterface $stockstatusSettings
     * @return IconInterface
     * @throws NoSuchEntityException
     */
    public function execute(StockstatusSettingsInterface $stockstatusSettings): IconInterface;
}
