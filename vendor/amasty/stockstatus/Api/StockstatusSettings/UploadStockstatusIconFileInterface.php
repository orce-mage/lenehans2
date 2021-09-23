<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\StockstatusSettings;

use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 */
interface UploadStockstatusIconFileInterface
{
    /**
     * Takes an array of temporary file information as input
     * Returns the name of the saved file
     *
     * @param array $file
     * @param int $optionId
     * @param int $storeId
     * @return string
     * @throws LocalizedException
     */
    public function execute(array $file, int $optionId, int $storeId): string;
}
