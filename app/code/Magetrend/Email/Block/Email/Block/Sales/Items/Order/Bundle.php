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

class Bundle extends \Magetrend\Email\Block\Email\Block\Sales\Items\Order\DefaultOrder
{
    public function getItemOptions()
    {
        $bundleOptions = [];
        $item = $this->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
            $order = $item->getOrder();
        } else {
            $orderItem =  $item->getOrderItem();
            $options = $orderItem->getProductOptions();
            $order = $orderItem->getOrder();
        }

        if ($options && isset($options['bundle_options'])) {
            foreach ($options['bundle_options'] as $option) {
                foreach ($option['value'] as $subOption) {
                    $bundleOptions[] = [
                        'bundle_option' => true,
                        'label' => $subOption['title'],
                        'value' => $subOption['qty'].' x '.$order->formatPriceTxt($subOption['price'])
                    ];
                }
            }
        }

        $result = array_merge(parent::getItemOptions(), $bundleOptions);
        return $result;
    }

    public function hideSku()
    {
        return true;
    }
}