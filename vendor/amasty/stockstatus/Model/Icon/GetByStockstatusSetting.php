<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\Icon\GetByStockstatusSettingInterface;
use Amasty\Stockstatus\Api\Icon\GetNewInterface;

class GetByStockstatusSetting implements GetByStockstatusSettingInterface
{
    /**
     * @var GetNewInterface
     */
    private $getNew;

    public function __construct(
        GetNewInterface $getNew
    ) {
        $this->getNew = $getNew;
    }

    public function execute(StockstatusSettingsInterface $stockstatusSettings): IconInterface
    {
        $icon = $this->getNew->execute();
        $icon->setOptionId($stockstatusSettings->getOptionId());
        $icon->setStoreId($stockstatusSettings->getStoreId());

        if ($stockstatusSettings->getImagePath()) {
            $icon->setPath($stockstatusSettings->getImagePath());
        }

        return $icon;
    }
}
