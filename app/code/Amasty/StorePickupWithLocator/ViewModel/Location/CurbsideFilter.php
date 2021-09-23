<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\ViewModel\Location;

use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * TODO: temporary view model to display curbside filter, remove after refactoring
 */
class CurbsideFilter implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Yesno
     */
    private $yesNoSource;

    public function __construct(
        ConfigProvider $configProvider,
        Yesno $yesNoSource
    ) {
        $this->configProvider = $configProvider;
        $this->yesNoSource = $yesNoSource;
    }

    /**
     * @return bool
     */
    public function isNeedToShowFilter(): bool
    {
        return $this->configProvider->isCurbsideCheckboxEnabled();
    }

    /**
     * @return string
     */
    public function getFilterLabel(): string
    {
        return $this->configProvider->getCurbsideCheckboxLabel();
    }

    /**
     * @return array
     */
    public function getFilterOptions(): array
    {
        return $this->yesNoSource->toOptionArray();
    }
}
