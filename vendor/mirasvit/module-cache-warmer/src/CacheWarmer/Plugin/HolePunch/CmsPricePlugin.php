<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Plugin\HolePunch;

use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\Pricing\Render\Layout;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Registry;
use Mirasvit\CacheWarmer\Service\Config\HolePunchConfig;

/**
 * Need for cms blocks excluding
 */
class CmsPricePlugin
{
    /**
     * @var RendererPool
     */
    private $rendererPool;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Layout
     */
    private $priceLayout;
    /**
     * @var ProductsList
     */
    private $productsList;

    /**
     * CmsPricePlugin constructor.
     * @param ProductsList $productsList
     * @param Layout $priceLayout
     * @param Registry $registry
     * @param RendererPool $rendererPool
     */
    public function __construct(
        ProductsList $productsList,
        Layout $priceLayout,
        Registry $registry,
        RendererPool $rendererPool
    ) {
        $this->productsList = $productsList;
        $this->priceLayout  = $priceLayout;
        $this->registry     = $registry;
        $this->rendererPool = $rendererPool;
    }

    /**
     * Return HTML block with tier price
     *
     * @param mixed $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param null $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetProductPriceHtml(
        $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id']              = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container']     = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->productsList->getLayout()->getBlock('product.price.render.default');
        if ($priceRender) {
            if (!$this->registry->registry(HolePunchConfig::FIND_DATA)) {
                $renderer = $this->priceLayout->getBlock('render.product.prices');
                if ($renderer->getData()) {
                    $this->registry->register(HolePunchConfig::FIND_DATA, $renderer->getData(), true);
                }
            }
        } elseif ($this->registry->registry(HolePunchConfig::FROM_CACHE) && !$priceRender) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $priceRender   = $objectManager->create('\Mirasvit\CacheWarmer\Service\PricingRenderService');
        }


        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }

        return $price;
    }
}
