<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\GroupedProduct\Block\Product\View\Type;

use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Closure;
use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped;

class GroupedPlugin
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var StatusRenderer
     */
    private $statusRenderer;

    public function __construct(
        Processor $processor,
        StatusRenderer $statusRenderer
    ) {
        $this->processor = $processor;
        $this->statusRenderer = $statusRenderer;
    }

    public function aroundGetProductPrice(
        Grouped $subject,
        Closure $proceed,
        Product $product
    ): string {
        $result = $proceed($product);

        $this->processor->execute([$product]);
        $status = $this->statusRenderer->render($product, false, true);

        if ($status) {
            $status = '<p>' . $status . '</p>';
            $result = $status . $result;
        }

        return $result;
    }
}
