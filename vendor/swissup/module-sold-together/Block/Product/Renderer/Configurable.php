<?php

namespace Swissup\SoldTogether\Block\Product\Renderer;

use Magento\Framework\View\Element;

class Configurable extends Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param Element\Context                   $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array                             $data
     */
    public function __construct(
        Element\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdocs}
     */
    protected function _prepareLayout()
    {
        if ($this->moduleManager->isOutputEnabled('Magento_Swatches')
            && class_exists(\Magento\Swatches\ViewModel\Product\Renderer\Configurable::class)
        ) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $configurable = $objectManager->get(
                \Magento\Swatches\ViewModel\Product\Renderer\Configurable::class
            );
            $this->addChild(
                'swatches',
                \Magento\Swatches\Block\Product\Renderer\Listing\Configurable::class,
                [
                    'template' => 'Magento_Swatches::product/listing/renderer.phtml',
                    'configurable_view_model' => $configurable
                ]
            );
        }

        $this->addChild(
            'default',
            \Swissup\SoldTogether\Block\Product\Renderer\Listing\Configurable::class,
            [
                'template' => 'product/listing/renderer/configurable.phtml',
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getSwatchesHtml() ?: $this->getConfigurableOptionsHtml();
    }

    /**
     * Render swatches
     *
     * @return string
     */
    private function getSwatchesHtml()
    {
        $product = $this->getProduct();
        $swatches = $this->getChildBlock('swatches');
        if (!$swatches) {
            return '';
        }

        $swatches->setProduct($product);
        $swatchesHtml = $this->getChildHtml('swatches', false);
        // Replace magento swatches renderer with custom renderer.
        $swatchesHtml = str_replace(
            [
                'Magento_Swatches/js/swatch-renderer',
                '"enableControlLabel": false',
                '"onlySwatches": true',
                // replace when minify enabled
                '"enableControlLabel":false',
                '"onlySwatches":true'
            ],
            [
                'Swissup_SoldTogether/js/swatch-renderer',
                '"enableControlLabel": true',
                '"onlySwatches": false',
                // replace when minify enabled
                '"enableControlLabel":true',
                '"onlySwatches":false'
            ],
            $swatchesHtml
        );

        return $swatchesHtml;
    }

    /**
     * Render configurable options
     *
     * @return string
     */
    private function getConfigurableOptionsHtml()
    {
        $product = $this->getProduct();
        $parent = $this->getParentBlock();
        $parent = $parent ? $parent->getParentBlock() : false;
        $this->getChildBlock('default')->addData([
            'product' => $product,
            'block_html_id' => $parent ? $parent->getNameInLayout() : '',
            'image_id' => $parent ? $parent->getData('image_id') : null,
        ]);

        return $this->getChildHtml('default', false);
    }
}
