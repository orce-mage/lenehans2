<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\StockstatusSettings;

use Amasty\Stockstatus\Api\StockstatusSettings\RemoveStockstatusSettingsIconFileInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Amasty\Stockstatus\Model\Icon\RemoveIconFileByPath;

class RemoveStockstatusSettingsIconFile implements RemoveStockstatusSettingsIconFileInterface
{
    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $stockstatusSettingsRepository;

    /**
     * @var RemoveIconFileByPath
     */
    private $removeIconFileByPath;

    public function __construct(
        StockstatusSettingsRepositoryInterface $stockstatusSettingsRepository,
        RemoveIconFileByPath $removeIconFileByPath
    ) {
        $this->stockstatusSettingsRepository = $stockstatusSettingsRepository;
        $this->removeIconFileByPath = $removeIconFileByPath;
    }

    public function execute(int $optionId, int $storeId): void
    {
        $stockStatusSetting = $this->stockstatusSettingsRepository->getByOptionIdAndStoreId($optionId, $storeId);
        $this->removeIconFileByPath->execute($stockStatusSetting->getImagePath());
    }
}
