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



namespace Mirasvit\CacheWarmer\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;

class TrackJs extends Template
{
    const COOKIE = 'mst-cache-warmer-track';

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ExtendedConfig
     */
    private $extendedConfig;

    /**
     * TrackJs constructor.
     * @param CookieManagerInterface $cookieManager
     * @param ExtendedConfig $extendedConfig
     * @param Context $context
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        ExtendedConfig $extendedConfig,
        Context $context
    ) {
        $this->cookieManager  = $cookieManager;
        $this->urlBuilder     = $context->getUrlBuilder();
        $this->extendedConfig = $extendedConfig;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getMageInit()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        $cookie = (int)$this->cookieManager->getCookie(self::COOKIE);

        return [
            'Mirasvit_CacheWarmer/js/track' => [
                'pageType'    => $request->getFullActionName(),
                'url'         => $this->urlBuilder->getUrl('cache_warmer/track'),
                'cookieName'  => self::COOKIE,
                'cookieValue' => $cookie > 0 ? $cookie : null,
            ],
        ];
    }

    public function toHtml()
    {
        if (!$this->extendedConfig->isStatisticsEnabled()) {
            return '';
        }

        return parent::toHtml();
    }
}
