<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Helper;

use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * @deprecated Use \Amasty\Stockstatus\Model\Stockstatus\Processor for retrieve custom stock status data.
 * Custom stock status data appear in extension attributes of product entity.
 */
class Data
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var StatusRenderer
     */
    private $statusRenderer;

    public function __construct(
        Processor $processor,
        StatusRenderer $statusRenderer,
        Registry $registry,
        ModuleManager $moduleManager,
        LayoutInterface $layout
    ) {
        $this->registry = $registry;
        $this->layout = $layout;
        $this->moduleManager = $moduleManager;
        $this->processor = $processor;
        $this->statusRenderer = $statusRenderer;
    }

    public function getStockAlert(Product $product)
    {
        $html = '';
        if ($this->moduleManager->isEnabled('Amasty_Xnotif')) {
            $configurableProduct = $this->registry->registry('current_product');
            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $product);

            $alertBlock = $this->layout->getBlock(
                'productalert.stock'
            );
            if ($alertBlock) {
                $alertBlock->setData('parent_product_id', $configurableProduct->getId());
                $alertBlock->setOriginalProduct($product);
                $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
                $html = $alertBlock->toHtml();
            }

            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $configurableProduct);
        }

        return $html;
    }

    public function getPriceAlert(Product $product)
    {
        $html = '';
        if ($this->moduleManager->isEnabled('Amasty_Xnotif')) {
            $configurableProduct = $this->registry->registry('current_product');
            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $product);

            $alertBlock = $this->layout->getBlock(
                'productalert.price'
            );
            if ($alertBlock) {
                $alertBlock->setData('parent_product_id', $configurableProduct->getId());
                $alertBlock->setOriginalProduct($product);
                $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
                $html = $alertBlock->toHtml();
            }

            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $configurableProduct);
        }

        return $html;
    }

    /**
     * @deprecated
     *
     * @param ProductInterface $product
     * @param QuoteItem|null|mixed $item
     *
     * @return mixed
     */
    public function getProductStockStatus(ProductInterface $product, $item = null)
    {
        if ($product->getTypeId() == 'configurable' && $item) {
            $product = $item->getOptionByCode('simple_product')->getProduct();
        }

        $this->processor->execute([$product]);

        return $this->statusRenderer->render($product, false, true);
    }
}
