<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model\ConfigHtmlConverter;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ConfigHtmlConverter\VariablesRendererInterface;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Framework\Escaper;

class VariablesRenderer implements VariablesRendererInterface
{
    const CURBSIDE_LABEL_VARIABLE_KEY = 'curbside_available_label';

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Escaper $escaper,
        ConfigProvider $configProvider
    ) {
        $this->escaper = $escaper;
        $this->configProvider = $configProvider;
    }

    /**
     * @param LocationInterface $location
     * @return void
     */
    public function renderVariable(LocationInterface $location, string $variable): string
    {
        $variableHtml = '';

        if ($variable === self::CURBSIDE_LABEL_VARIABLE_KEY
            && $this->configProvider->isCurbsideLabelsEnabled()
            && $location->getCurbsideEnabled()
        ) {
            $variableHtml = '<span class="ampickup-curbside-label">'
                . $this->escaper->escapeHtml($this->configProvider->getCurbsideLabelText())
                . '</span>';
        }

        return $variableHtml;
    }
}
