<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Checkout;

use Amasty\Stockstatus\Model\ConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class LayoutRenderer implements LayoutProcessorInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * Add Amasty Stockstatus block to Order Summary
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if ($this->configProvider->isDisplayOnCheckout()) {
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
                ['cart_items']['children'])) {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']
                ['cart_items']['children']['amstockstatus'] = $this->getStockstatusComponentInfo();
            }
        }

        return $jsLayout;
    }

    private function getStockstatusComponentInfo(): array
    {
        return [
            'component' => 'Amasty_Stockstatus/js/checkout/summary/item/details/stockstatus',
            'displayArea' => 'after_details',
            'children' => [],
            'config' => [
                'template' => 'Amasty_Stockstatus/checkout/summary/item/details/stockstatus',
                'isIconOnly' => $this->configProvider->isIconOnly()
            ]
        ];
    }
}
