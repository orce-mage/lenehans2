<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace MidoriWeb\Inventory\Model\Stockstatus\Renderer\Status;

use Amasty\Stockstatus\Block\CustomStockStatus;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Layout\GeneratorPool;

class DefaultProcessor extends \Amasty\Stockstatus\Model\Stockstatus\Renderer\Status\DefaultProcessor
{
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

    protected $generatorPool;

    public function __construct(
        LayoutInterface $layout,
        ManagerInterface $eventManager,
        GeneratorPool $generatorPool
    ) {
        $this->layout = $layout;
        $this->eventManager = $eventManager;
        $this->generatorPool = $generatorPool;
    }

    public function render(ProductInterface $product, $inProductList = false, $addWrapper = false):string
    {
        $block = $this->getStockStatusBlockV2();
        $block->addData([
            static::IN_PRODUCT_LIST => $inProductList,
            static::ADD_WRAPPER => $addWrapper
        ]);
        $block->setProduct($product);
        $this->eventManager->dispatch(self::EVENT_NAME, ['stockstatus_block' => $block]);

        return $block->toHtml();
    }

    private function getStockStatusBlockV2()
    {
        if ($this->blockTemplate === null) {
            $generatorPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get("\Magento\Framework\View\Layout\GeneratorPool");
            $blockGenerator = $this->generatorPool->getGenerator(\Magento\Framework\View\Layout\Generator\Block::TYPE);
            $this->blockTemplate= $blockGenerator->createBlock(CustomStockStatus::class, 'auto_amasty_stockstatus01');
            //$this->blockTemplate = $this->layout->createBlock(CustomStockStatus::class);
        }

        return $this->blockTemplate;
    }
}
