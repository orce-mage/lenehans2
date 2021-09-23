<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Icon\GetIconUrlInterface;
use Amasty\Stockstatus\Api\StockstatusSettings\GetByOptionIdAndStoreIdInterface;

class GetIconUrl implements GetIconUrlInterface
{
    /**
     * @var GetIconUrlByPath
     */
    private $getIconUrlByPath;

    /**
     * @var GetByOptionIdAndStoreIdInterface
     */
    private $getByOptionId;

    public function __construct(
        GetByOptionIdAndStoreIdInterface $getByOptionId,
        GetIconUrlByPath $getIconUrlByPath
    ) {
        $this->getByOptionId = $getByOptionId;
        $this->getIconUrlByPath = $getIconUrlByPath;
    }

    public function execute(int $optionId, int $storeId): ?string
    {
        $stockstatusSettings = $this->getByOptionId->execute($optionId, $storeId);
        $iconPath = $stockstatusSettings->getImagePath();

        return $iconPath ? $this->getIconUrlByPath->execute($iconPath) : null;
    }
}
