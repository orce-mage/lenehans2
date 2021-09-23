<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Plugin\Sales\Order;


class Item
{
    protected $_productAttributesIndexerProcessor;

    public function __construct(
        \Amasty\Orderexport\Model\Indexer\Attribute\Processor $productAttributesIndexerProcessor
    ){
        $this->_productAttributesIndexerProcessor = $productAttributesIndexerProcessor;
    }

    public function afterAfterSave(
        \Magento\Sales\Model\Order\Item $item,
        $result
    ){
        $this->_productAttributesIndexerProcessor->reindexRow($item->getId());
        return $result;
    }
}