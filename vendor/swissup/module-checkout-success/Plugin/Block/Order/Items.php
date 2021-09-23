<?php

namespace Swissup\CheckoutSuccess\Plugin\Block\Order;

class Items
{
    /**
     * Insert product image
     *
     * @param  \Magento\Sales\Block\Items\AbstractItems $items
     * @param  string                                   $html
     * @param  \Magento\Sales\Model\Order\Item          $item
     * @return string
     */
    public function afterGetItemHtml(
        \Magento\Sales\Block\Items\AbstractItems $items,
        $html,
        \Magento\Sales\Model\Order\Item $item
    ) {
        $renderer = $items->getChildBlock('imageRenderer');
        if (!$renderer) {
            return $html;
        }

        $needle = $renderer->getNeedle()
            ? $renderer->getNeedle()
            : '<strong class="product name product-item-name">';

        return str_replace($needle, $renderer->getImageHtml($item) . $needle, $html);
    }
}
