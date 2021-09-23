<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Order\Email\Items;

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

class DefaultOrder
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
    }

    /**
     * @param BundleItemsRenderer|DefaultItemsRenderer $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml($subject, string $result): string
    {
        if ($this->configProvider->isDisplayedOnEmail()) {
            $find = '<p class="sku';

            $product = $this->resolveProduct($subject);

            $this->qtyRegistry->add((int) $product->getId(), (float) $subject->getItem()->getQtyOrdered());
            $this->processor->execute([$product]);
            $status = $this->statusRenderer->render($product, false, true);

            if ($status) {
                $status = '<p>' . $status . '</p>';
                $result = str_replace($find, $status . $find, $result);
            }

        }

        return $result;
    }

    /**
     * @param BundleItemsRenderer|DefaultItemsRenderer $subject
     *
     * @return ProductInterface
     */
    protected function resolveProduct($subject)
    {
        try {
            switch ($subject->getItem()->getProductType()) {
                case BundleType::TYPE_CODE:
                    $product = $this->productRepository->getById($subject->getItem()->getProductId());
                    break;
                case Configurable::TYPE_CODE:
                    $product = $this->productRepository->get($subject->getSku($subject->getItem()));
                    break;
            }
        } catch (NoSuchEntityException $e) {
            null;
        }

        return $product ?? $subject->getItem()->getProduct();
    }
}
