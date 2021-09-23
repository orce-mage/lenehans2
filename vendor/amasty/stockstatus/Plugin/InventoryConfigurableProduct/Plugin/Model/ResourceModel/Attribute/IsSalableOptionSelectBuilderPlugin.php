<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Source\Outofstock;
use Closure;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute\IsSalableOptionSelectBuilder;

class IsSalableOptionSelectBuilderPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function aroundAfterGetSelect(
        IsSalableOptionSelectBuilder $subject,
        Closure $proceed,
        OptionSelectBuilderInterface $origSubject,
        Select $select
    ): Select {
        if ($this->configProvider->getOutofstockVisibility() === Outofstock::MAGENTO_LOGIC) {
            $select = $proceed($origSubject, $select);
        }

        return $select;
    }
}
