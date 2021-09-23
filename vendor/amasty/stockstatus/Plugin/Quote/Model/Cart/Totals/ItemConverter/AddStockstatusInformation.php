<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Quote\Model\Cart\Totals\ItemConverter;

use Amasty\Stockstatus\Api\Data\StockstatusInformationInterface;
use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class AddStockstatusInformation
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Processor $processor,
        ConfigProvider $configProvider
    ) {
        $this->processor = $processor;
        $this->configProvider = $configProvider;
    }

    public function afterModelToDataObject(
        ItemConverter $subject,
        TotalsItemInterface $totalsItem,
        QuoteItem $quoteItem
    ): TotalsItemInterface {
        if ($this->configProvider->isDisplayOnCheckout()) {
            $this->populateExtensionAttributes($totalsItem, $quoteItem);
        }

        return $totalsItem;
    }

    private function populateExtensionAttributes(TotalsItemInterface $totalsItem, QuoteItem $quoteItem): void
    {
        if (!$totalsItem->getExtensionAttributes()->getStockstatusInformation()) {
            $product = $quoteItem->getProduct();
            if ($product->getTypeId() == 'configurable') {
                $product = $quoteItem->getOptionByCode('simple_product')->getProduct();
            }

            $this->processor->execute([$product]);
            /** @var StockstatusInformationInterface $productStatusInformation**/
            $productStatusInformation = $product->getExtensionAttributes()->getStockstatusInformation();

            if (null !== $productStatusInformation->getStatusId()) {
                $stockStatusInfo = clone $productStatusInformation;
                $totalsItem->getExtensionAttributes()->setStockstatusInformation($stockStatusInfo);
            }
        }
    }
}
