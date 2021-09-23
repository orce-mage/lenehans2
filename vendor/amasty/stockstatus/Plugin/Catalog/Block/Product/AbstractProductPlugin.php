<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Catalog\Block\Product;

use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Info as InfoRenderer;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Magento\Catalog\Block\Product\AbstractProduct;

class AbstractProductPlugin
{
    /**
     * @var array
     */
    protected $matchedNames = [
        'product.info.configurable',
        'product.info.simple',
        'product.info.bundle',
        'product.info.virtual',
        'product.info.downloadable',
        'product.info.grouped.stock'
    ];

    /**
     * @var StatusRenderer
     */
    private $statusRenderer;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var InfoRenderer
     */
    private $infoRenderer;

    public function __construct(
        Processor $processor,
        StatusRenderer $statusRenderer,
        InfoRenderer $infoRenderer
    ) {
        $this->statusRenderer = $statusRenderer;
        $this->processor = $processor;
        $this->infoRenderer = $infoRenderer;
    }

    public function afterToHtml(
        AbstractProduct $subject,
        string $result
    ): string {
        $name = $subject->getNameInLayout();

        if (in_array($name, $this->matchedNames)
            || strpos($name, 'product.info.type_schedule_block') !== false
        ) {
            $this->processor->execute([$subject->getProduct()]);
            $status = $this->statusRenderer->render($subject->getProduct(), false, true);
            if ($status != '') {
                $result = $status . $this->infoRenderer->render();
            }
        }

        return  $result;
    }
}
