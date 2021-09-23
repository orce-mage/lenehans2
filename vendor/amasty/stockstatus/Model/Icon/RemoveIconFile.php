<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Icon\RemoveIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\RemoveStockstatusSettingsIconFileInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class RemoveIconFile implements RemoveIconFileInterface
{
    /**
     * @var RemoveStockstatusSettingsIconFileInterface
     */
    private $removeStockstatusSettingsIconFile;

    public function __construct(
        RemoveStockstatusSettingsIconFileInterface $removeStockstatusSettingsIconFile
    ) {
        $this->removeStockstatusSettingsIconFile = $removeStockstatusSettingsIconFile;
    }

    public function execute(int $optionId, int $storeId): void
    {
        try {
            $this->removeStockstatusSettingsIconFile->execute($optionId, $storeId);
        } catch (NoSuchEntityException $e) {
            null; // no action required if no entity with same option_id
        }
    }
}
