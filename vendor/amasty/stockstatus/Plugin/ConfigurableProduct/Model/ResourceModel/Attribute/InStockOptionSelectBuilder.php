<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\ConfigurableProduct\Model\ResourceModel\Attribute;

use Amasty\Stockstatus\Model\ConfigProvider;
use Closure;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute\InStockOptionSelectBuilder as NativeBuilder;
use Amasty\Stockstatus\Model\Source\Outofstock;

class InStockOptionSelectBuilder
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
        NativeBuilder $nativeSubject,
        Closure $proceed,
        OptionSelectBuilderInterface $subject,
        Select $select
    ): Select {
        if ($this->configProvider->getOutofstockVisibility() === Outofstock::MAGENTO_LOGIC) {
            $select = $proceed($subject, $select);
        }

        return $select;
    }
}
