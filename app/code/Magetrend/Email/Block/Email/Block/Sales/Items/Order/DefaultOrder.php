<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Items\Order;

use Magento\Framework\Exception\NoSuchEntityException;

class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * Final parent block
     *
     * @var \Magetrend\Email\Block\Email\Block
     */
    private $mainNode = null;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    public $imageBuilder;

    public $productRepository;

    public $moduleHelper;

    /**
     * DefaultOrder constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magetrend\Email\Helper\Data $moduleHelper,

     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magetrend\Email\Helper\Data $moduleHelper,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->productRepository = $productRepository;
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $design = $this->getTheme();
        $template = $this->getTemplate();
        $template = str_replace('/default/', '/'.$design.'/', $template);
        $this->setTemplate($template);
        return $this;
    }

    /**
     * Returns variable manager model
     *
     * @return \ Magetrend\Email\Model\Varmanager|null
     */
    public function getVarModel()
    {
        return $this->getMainNode()->getVarModel();
    }

    /**
     * Returns final parent block
     *
     * @return \Magetrend\Email\Block\Email\Block
     */
    public function getMainNode()
    {
        if ($this->mainNode == null) {
            $this->mainNode = $this->getParentBlock()->getParentBlock()->getParentBlock();
        }
        return $this->mainNode;
    }

    /**
     * Is text direction trl
     * @return bool
     */
    public function isRTL()
    {
        return $this->getMainNode()->isRTL();
    }

    /**
     * Show item image or not
     *
     * @return bool
     */
    public function showImage()
    {
        return true;
    }

    /**
     * Returns item image html
     *
     * @param $item
     * @return string
     */
    public function getItemImage($item, $imageId = 'sendfriend_small_image')
    {
        try {
            $product = $this->productRepository->getById($item->getProductId());
        } catch (NoSuchEntityException $e) {
            return $this->getProductImagePlaceholder();
        }

        $imageUrl = $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create()->getImageUrl();
        return $imageUrl;
    }

    public function getProductImagePlaceholder()
    {
        return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/thumbnail.jpg');
    }

    public function getHelper()
    {
        return $this->moduleHelper;
    }

    public function isShipmentItem()
    {
        if ($this->getItem() instanceof \Magento\Sales\Model\Order\Shipment\Item) {
            return true;
        }
        return false;
    }

    /**
     * Get the html for item price
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return string
     */
    public function getPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }

    public function getQty($item)
    {
        if ($item->getQty() == 0 && $item->hasData('qty_ordered')) {
            return $item->getQtyOrdered()*1;
        }

        return $item->getQty()*1;
    }

    public function showFPT()
    {
        return $this->getWeeeTax() > 0;
    }

    public function getWeeeTax($storeId = 0)
    {
        if (!$this->moduleHelper->showFPT($storeId)) {
            return 0;
        }

        return $this->getItem()->getData('weee_tax_applied_row_amount');
    }

    public function getFormattedWeeeTax()
    {
        return __('[FPT: %1]', $this->moduleHelper->formatPriceByStoreId($this->getWeeeTax(), $this->getItem()->getStoreId()));
    }

    public function hideSku()
    {
        return false;
    }

    public function getProductUrl($item)
    {
        try {
            $product = $this->productRepository->getById($item->getProductId());
        } catch (NoSuchEntityException $e) {
            return '';
        }

        return $product->getProductUrl();
    }
}
