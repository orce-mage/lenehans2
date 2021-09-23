<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Items\Order;

class Downloadable extends \Magetrend\Email\Block\Email\Block\Sales\Items\Order\DefaultOrder
{
    public $downloadableRenderer;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magetrend\Email\Helper\Data $moduleHelper,
        \Magento\Downloadable\Block\Sales\Order\Email\Items\Order\Downloadable $downloadableRenderer,
        array $data = []
    ) {
        $this->downloadableRenderer = $downloadableRenderer;
        parent::__construct($context, $imageBuilder, $productRepository, $moduleHelper, $data);
    }

    public function getLinks()
    {
        return $this->downloadableRenderer->setItem($this->getItem())->getLinks();
    }

    public function getLinksTitle()
    {
        return $this->downloadableRenderer->setItem($this->getItem())->getLinksTitle();
    }

}