<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\StockstatusSettings;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\GetIconUrlByStockstatusSettingInterface;
use Amasty\Stockstatus\Model\Icon\GetIconUrlByPath;

class GetIconUrlByStockstatusSetting implements GetIconUrlByStockstatusSettingInterface
{
    /**
     * @var GetIconUrlByPath
     */
    private $getIconUrlByPath;

    public function __construct(
        GetIconUrlByPath $getIconUrlByPath
    ) {
        $this->getIconUrlByPath = $getIconUrlByPath;
    }

    public function execute(StockstatusSettingsInterface $stockstatusSettings): ?string
    {
        $imagePath = $stockstatusSettings->getImagePath();

        return $imagePath ? $this->getIconUrlByPath->execute($imagePath) : null;
    }
}
