<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Items\Order;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Framework\Exception\NoSuchEntityException;

class Configurable extends \Magetrend\Email\Block\Email\Block\Sales\Items\Order\DefaultOrder
{
    private $childProduct = null;

    public function getItemImage($item, $imageId = 'sendfriend_small_image')
    {
        /**
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available
         */
        $showParent = $this->_scopeConfig->getValue(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $childProd = $this->getChildProduct($item);
        if (!$childProd
            || $showParent == ThumbnailSource::OPTION_USE_PARENT_IMAGE
            || !($childProd->getThumbnail() && $childProd->getThumbnail() != 'no_selection')
        ) {
            return parent::getItemImage($item, $imageId);
        }

        $product = $this->getChildProduct($item);
        if (!$product) {
            return parent::getItemImage($item, $imageId);
        }

        $imageUrl = $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create()->getImageUrl();
        return $imageUrl;
    }

    public function getChildProduct($item)
    {
        $orderItem = $this->getOrderItem($item);
        $options = $orderItem->getProductOptions();
        if (isset($options['simple_sku']) && !empty($options['simple_sku'])) {
            try {
                return $this->productRepository->get($options['simple_sku']);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }

    public function getOrderItem($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            return $item;
        }

        return $item->getOrderItem();
    }
}