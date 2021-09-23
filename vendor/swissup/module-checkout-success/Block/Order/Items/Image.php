<?php

namespace Swissup\CheckoutSuccess\Block\Order\Items;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\Framework\View\Element\Context;

class Image extends AbstractBlock
{
    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var \Magento\GroupedProduct\Model\ResourceModel\Product\Link
     */
    protected $productLinks;

    /**
     * @param ItemResolver $itemResolver
     * @param ImageBuilder $imageBuilder
     * @param Context      $context
     * @param array        $data
     */
    public function __construct(
        ImageBuilder $imageBuilder,
        Link $productLink,
        Context $context,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->productLinks = $productLink;
        parent::__construct($context, $data);
    }

    /**
     * Get product image for ordered item
     *
     * @param  \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getImageHtml(\Magento\Sales\Model\Order\Item $item)
    {
        $product = $item->getProduct();
        $imageId = $this->getProductImageId();

        // get parent grouped product image in case its child is empty
        $groupedId = $this->productLinks->getParentIdsByChild(
            $product->getId(),
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);

        if ($groupedId && strstr($product->getImage(), '/') == false) {  // compatibility with magento 2.4.1
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($groupedId);
            $imageId = $this->getProductImageId();
        }

        return $this->imageBuilder
            ->setProduct($product) // compatibility with Magento 2.2.6
            ->setImageId($imageId) // compatibility with Magento 2.2.6
            ->create()             // compatibility with Magento 2.2.6
            ->toHtml();

    }
}
