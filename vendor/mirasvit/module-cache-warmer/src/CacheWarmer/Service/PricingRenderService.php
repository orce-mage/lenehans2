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



namespace Mirasvit\CacheWarmer\Service;

use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;

class PricingRenderService extends PricingRender
{
    /** @var \Mirasvit\CacheWarmer\Service\PricingRendererPoolService */
    private $rendererPool;

    /**
     * {@inheritdoc}
     */
    public function render($priceCode, SaleableInterface $saleableItem, array $arguments = [])
    {
        $useArguments = array_replace($this->_data, $arguments);

        if (!$this->rendererPool) {
            throw new \RuntimeException('Wrong Price Rendering layout configuration. Factory block is missed');
        }

        // obtain concrete Price Render
        /** @var mixed $priceRender */
        $priceRender = $this->rendererPool->createPriceRender($priceCode, $saleableItem, $useArguments);

        return $priceRender->toHtml();
    }

    /**
     * Internal constructor, that is called from real constructor
     * Please override this one instead of overriding real __construct constructor
     * @return void
     */
    protected function _construct()
    {
        $this->rendererPool = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Mirasvit\CacheWarmer\Service\PricingRendererPoolService::class
        );
    }
}
