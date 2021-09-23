<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Model\ResourceModel\StockstatusSettings as StockstatusSettingsResource;
use Magento\Framework\Model\AbstractModel;

class StockstatusSettings extends AbstractModel implements StockstatusSettingsInterface
{
    public function _construct()
    {
        $this->_init(StockstatusSettingsResource::class);
    }

    public function getOptionId(): ?int
    {
        return $this->hasData(self::OPTION_ID) ? (int)$this->_getData(self::OPTION_ID) : null;
    }

    public function setOptionId(int $optionId): void
    {
        $this->setData(self::OPTION_ID, $optionId);
    }

    public function getStoreId(): ?int
    {
        return $this->hasData(self::STORE_ID) ? (int)$this->_getData(self::STORE_ID) : null;
    }

    public function setStoreId(int $storeId): void
    {
        $this->setData(self::STORE_ID, $storeId);
    }

    public function getImagePath(): ?string
    {
        return $this->_getData(self::IMAGE_PATH);
    }

    public function setImagePath(?string $imagePath): void
    {
        $this->setData(self::IMAGE_PATH, $imagePath);
    }

    public function getTooltipText(): ?string
    {
        return $this->_getData(self::TOOLTIP_TEXT);
    }

    public function setTooltipText(string $tooltipText): void
    {
        $this->setData(self::TOOLTIP_TEXT, $tooltipText);
    }
}
