<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class IsHidePrice
{
    const HIDE_PRICE_STATUSES = 'amasty_hide_price/stock_status/stock_status';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Processor
     */
    private $processor;

    public function __construct(
        Processor $processor,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->processor = $processor;
    }

    public function execute(ProductInterface $product): bool
    {
        $this->processor->execute([$product]);

        $appliedStatuses = $this->scopeConfig->getValue(
            static::HIDE_PRICE_STATUSES,
            ScopeInterface::SCOPE_STORE
        );

        return $appliedStatuses && in_array(
            $product->getExtensionAttributes()->getStockstatusInformation()->getStatusId(),
            explode(',', $appliedStatuses)
        );
    }
}
