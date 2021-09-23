<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Quote\Items;

use Magento\Framework\Exception\NoSuchEntityException;

class DefaultItem extends \Magetrend\Email\Block\Email\Block\Sales\Items\Order\DefaultOrder
{
    protected function _beforeToHtml()
    {
        return $this;
    }

    /**
     * Returns item image html
     *
     * @param $item
     * @return string
     */
    public function getItemImage($item, $imageId = 'sendfriend_small_image')
    {
        $imageId = !empty($this->getImageId())?$this->getImageId():'sendfriend_small_image';
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
}
