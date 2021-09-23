<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Renderer;

use Magento\Catalog\Api\Data\ProductInterface;

class Status
{
    /**
     * @var array
     */
    private $processors;

    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    public function render(ProductInterface $product, $inProductList = false, $addWrapper = false): string
    {
        $result = '';
        foreach ($this->processors as $processor) {
            $result .= $processor->render($product, $inProductList, $addWrapper);
        }

        return $result;
    }
}
