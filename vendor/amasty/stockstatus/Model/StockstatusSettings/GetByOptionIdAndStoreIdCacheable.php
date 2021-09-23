<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\StockstatusSettings;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\GetByOptionIdAndStoreIdInterface;
use Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings as StockstatusSettingsResource;
use Amasty\Stockstatus\Model\StockstatusSettingsFactory;

class GetByOptionIdAndStoreIdCacheable implements GetByOptionIdAndStoreIdInterface
{
    /**
     * @var StockstatusSettingsInterface[][]
     */
    private $cache;

    /**
     * @var StockstatusSettingsFactory
     */
    private $stockstatusSettingsFactory;

    /**
     * @var StockstatusSettingsResource
     */
    private $stockstatusSettingsResource;

    public function __construct(
        StockstatusSettingsFactory $stockstatusSettingsFactory,
        StockstatusSettingsResource $stockstatusSettingsResource
    ) {
        $this->stockstatusSettingsFactory = $stockstatusSettingsFactory;
        $this->stockstatusSettingsResource = $stockstatusSettingsResource;
    }

    public function execute(int $optionId, int $storeId): StockstatusSettingsInterface
    {
        if (!isset($this->cache[$storeId][$optionId])) {
            $stockstatusSettings = $this->stockstatusSettingsFactory->create();
            $this->stockstatusSettingsResource->loadByOptionIdAndStoreId(
                $stockstatusSettings,
                $optionId,
                $storeId
            );
            $this->cache[$storeId][$optionId] = $stockstatusSettings;
        }

        return $this->cache[$storeId][$optionId];
    }
}
