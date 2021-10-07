<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace MidoriWeb\Custom\Plugin\Order\Email\Items;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Product\QtyRegistry;
use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Magento\Bundle\Block\Sales\Order\Items\Renderer as BundleItemsRenderer;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder as DefaultItemsRenderer;

class DefaultOrder extends \Amasty\Stockstatus\Plugin\Order\Email\Items\DefaultOrder
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var StatusRenderer
     */
    private $statusRenderer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QtyRegistry
     */
    private $qtyRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigProvider $configProvider,
        Processor $processor,
        StatusRenderer $statusRenderer,
        QtyRegistry $qtyRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->processor = $processor;
        $this->statusRenderer = $statusRenderer;
        $this->configProvider = $configProvider;
        $this->qtyRegistry = $qtyRegistry;

        parent::__construct($productRepository, $configProvider, $processor, $statusRenderer, $qtyRegistry);
    }

    /**
     * @param BundleItemsRenderer|DefaultItemsRenderer $subject
     *
     * @return ProductInterface
     */
    protected function resolveProduct($subject)
    {
        $item = $subject->getItem();
        $productType = $this->getItemProductType($item);
        try {
            switch ($productType) {
                case BundleType::TYPE_CODE:
                    $product = $this->productRepository->getById($item->getProductId());
                    break;
                case Configurable::TYPE_CODE:
                    $product = $this->productRepository->get($subject->getSku($item));
                    break;
            }
        } catch (NoSuchEntityException $e) {
            null;
        }

        return $product ?? $this->getProduct($item);
    }

    private function getItemProductType($item)
    {
        if ($item->getOrderItem()) {
            $type = $item->getOrderItem()->getProductType();
        } elseif ($item instanceof \Magento\Quote\Model\Quote\Address\Item) {
            $type = $item->getQuoteItem()->getProductType();
        } else {
            $type = $item->getProductType();
        }

        return $type;
    }

    private function getProduct($item)
    {
        if ($item->getOrderItem()) {
            $product = $item->getOrderItem()->getProduct();
        } elseif ($item instanceof \Magento\Quote\Model\Quote\Address\Item) {
            $product = $item->getQuoteItem()->getProduct();
        } else {
            $product = $item->getProduct();
        }

        return $product;
    }
}
