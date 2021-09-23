<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Cart;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Stockstatus\Cart\AddStockstatusToCartHtml;
use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Info as InfoRenderer;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Closure;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class AbstractCart
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var StatusRenderer
     */
    private $statusRenderer;

    /**
     * @var InfoRenderer
     */
    private $infoRenderer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AddStockstatusToCartHtml
     */
    private $addStockstatusToCartHtml;

    public function __construct(
        Processor $processor,
        StatusRenderer $statusRenderer,
        InfoRenderer $infoRenderer,
        ConfigProvider $configProvider,
        AddStockstatusToCartHtml $addStockstatusToCartHtml
    ) {
        $this->processor = $processor;
        $this->statusRenderer = $statusRenderer;
        $this->infoRenderer = $infoRenderer;
        $this->configProvider = $configProvider;
        $this->addStockstatusToCartHtml = $addStockstatusToCartHtml;
    }

    public function aroundGetItemHtml(
        \Magento\Checkout\Block\Cart\AbstractCart $subject,
        Closure $proceed,
        QuoteItem $item
    ) {
        $result = $proceed($item);

        if ($this->configProvider->isDisplayedOnCart() && $item->getProduct()->getData('sku')) {
            $product = $item->getProduct();
            if ($product->getTypeId() == 'configurable') {
                $product = $item->getOptionByCode('simple_product')->getProduct();
            }

            $this->processor->execute([$product]);
            $status = $this->statusRenderer->render($product, false, true);

            if ($status) {
                $status = '<div class="amstockstatus-cart">' . $status . $this->infoRenderer->render() . '</div>';
                $result = $this->addStockstatusToCartHtml->execute($status, $result);
            }
        }

        return $result;
    }
}
