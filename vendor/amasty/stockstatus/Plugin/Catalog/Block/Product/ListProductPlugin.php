<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Catalog\Block\Product;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Info as InfoRenderer;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;

class ListProductPlugin
{
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
     * @var InfoRenderer
     */
    private $infoRenderer;

    public function __construct(
        ConfigProvider $configProvider,
        Processor $processor,
        StatusRenderer $statusRenderer,
        InfoRenderer $infoRenderer
    ) {
        $this->processor = $processor;
        $this->statusRenderer = $statusRenderer;
        $this->configProvider = $configProvider;
        $this->infoRenderer = $infoRenderer;
    }

    public function afterGetProductPrice(
        ListProduct $subject,
        string $html,
        Product $product
    ): string {
        if ($this->isEnabledOnCategory()) {
            $this->processor->execute([$product]);
            $status = $this->statusRenderer->render($product, true, true);
            if ($status != '') {
                $status = sprintf(
                    '<div class="amstockstatus-category">%s</div>',
                    $status . $this->infoRenderer->render()
                );
            }

            $html .= $status;
        }

        return $html;
    }

    public function afterToHtml(ListProduct $subject, string $result): string
    {
        if ($this->isEnabledOnCategory()) {
            $result .= '
                <script type="text/javascript">
                    require([
                        "jquery"
                    ], function($) {
                        $(".amstockstatus").each(function(i, item) {
                            var parent = $(item).parents(".item").first();
                            parent.find(".actions .stock").remove();
                        })
                    });
                </script>
            ';
        }

        return $result;
    }

    protected function isEnabledOnCategory(): bool
    {
        return $this->configProvider->isDisplayedOnCategory();
    }
}
