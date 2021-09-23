<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Model\Stockstatus\Renderer\Status;

use Amasty\Stockstatus\Block\CustomStockStatus;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\LayoutInterface;

class DefaultProcessor
{
    const IN_PRODUCT_LIST = 'in_product_list';
    const ADD_WRAPPER = 'add_wrapper';
    const EVENT_NAME = 'amasty_stockstatus_render_before';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var null|CustomStockStatus
     */
    private $blockTemplate = null;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        LayoutInterface $layout,
        ManagerInterface $eventManager
    ) {
        $this->layout = $layout;
        $this->eventManager = $eventManager;
    }

    public function render(ProductInterface $product, $inProductList = false, $addWrapper = false): string
    {
        $block = $this->getStockStatusBlock();
        $block->addData([
            static::IN_PRODUCT_LIST => $inProductList,
            static::ADD_WRAPPER => $addWrapper
        ]);
        $block->setProduct($product);
        $this->eventManager->dispatch(self::EVENT_NAME, ['stockstatus_block' => $block]);

        return $block->toHtml();
    }

    private function getStockStatusBlock(): CustomStockStatus
    {
        if ($this->blockTemplate === null) {
            $this->blockTemplate = $this->layout->createBlock(CustomStockStatus::class);
        }

        return $this->blockTemplate;
    }
}
