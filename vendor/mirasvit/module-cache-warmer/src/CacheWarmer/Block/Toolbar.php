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

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Service\Config\DebugConfig;

class Toolbar extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Mirasvit_CacheWarmer::toolbar.phtml';

    /**
     * @var \Mirasvit\CacheWarmer\Model\Config
     */
    private $config;

    /**
     * @var DebugConfig
     */
    private $debugConfig;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    public function __construct(
        Config $config,
        DebugConfig $debugConfig,
        DeploymentConfig $deploymentConfig,
        Context $context
    ) {
        $this->config           = $config;
        $this->debugConfig      = $debugConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->context          = $context;
        $this->urlBuilder       = $context->getUrlBuilder();

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        //we disable it only if it's disable completely
        //if its disabled by IP, we enable it to make sure that JS script call will be cached and served
        if (!$this->debugConfig->isInfoBlockEnabled()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return bool
     */
    public function isVarnishEnabled()
    {
        if ($this->config->getCacheType() != PageCacheConfig::BUILT_IN) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCacheType()
    {
        switch ($this->config->getCacheType()) {
            case 1:
                $cacheType = 'Built-in';

                if($external = $this->deploymentConfig->get('cache/frontend/page_cache/backend')) {
                    $type = strpos($external, 'Redis') !== false ? 'Redis' : $external;

                    $cacheType .= " ($type)";
                }

                return $cacheType;
            case 'LITEMAGE':
                return 'LiteMage';
            default:
                return 'Varnish';
        }
    }

    /**
     * @return bool|string
     */
    public function getIgnorePattern()
    {
        $uri = $this->getURI();
        foreach ($this->config->getIgnoredUriExpressions() as $expression) {
            if (preg_match($expression, $uri)) {
                return $expression;
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getIgnoredUserAgent()
    {
        $userAgent = $this->getUserAgent();
        foreach ($this->config->getIgnoredUserAgents() as $pattern) {
            if(preg_match($pattern, $userAgent)) {
                return $pattern;
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getIgnoredPageType()
    {
        $pageType         = $this->getPageType();
        $ignoredPageTypes = $this->config->getIgnoredPageTypes();

        return in_array($pageType, $ignoredPageTypes) ? $pageType : false;
    }

    /**
     * @return string
     */
    public function getCacheableTestUrl()
    {
        return $this->urlBuilder->getUrl('cache_warmer/test/cacheable');
    }

    /**
     * @return string
     */
    public function getNonCacheableTestUrl()
    {
        return $this->urlBuilder->getUrl('cache_warmer/test/nonCacheable');
    }
}
