<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Api\Icon\GetByOptionIdInterface;
use Amasty\Stockstatus\Api\StockstatusSettingsRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByOptionId implements GetByOptionIdInterface
{
    /**
     * @var IconInterface[]
     */
    private $icons = [];

    /**
     * @var GetByStockstatusSetting
     */
    private $getByStockstatusSetting;

    /**
     * @var StockstatusSettingsRepositoryInterface
     */
    private $stockstatusSettingsRepository;

    public function __construct(
        StockstatusSettingsRepositoryInterface $stockstatusSettingsRepository,
        GetByStockstatusSetting $getByStockstatusSetting
    ) {
        $this->stockstatusSettingsRepository = $stockstatusSettingsRepository;
        $this->getByStockstatusSetting = $getByStockstatusSetting;
    }

    /**
     * @param int $optionId
     * @param int $storeId
     * @return IconInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $optionId, int $storeId): IconInterface
    {
        if (!isset($this->icons[$storeId][$optionId])) {
            $stockstatusSetting = $this->stockstatusSettingsRepository->getByOptionIdAndStoreId($optionId, $storeId);
            $this->icons[$storeId][$optionId] = $this->getByStockstatusSetting->execute($stockstatusSetting);
        }

        return $this->icons[$storeId][$optionId];
    }
}
