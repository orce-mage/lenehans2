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



namespace Mirasvit\CacheWarmer\Plugin;

use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;

class InvalidateVarnishPlugin
{
    /**
     * @var ExtendedConfig
     */
    private $extendedConfig;

    /**
     * InvalidateVarnishPlugin constructor.
     * @param ExtendedConfig $extendedConfig
     */
    public function __construct(
        ExtendedConfig $extendedConfig
    ) {
        $this->extendedConfig = $extendedConfig;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     * @param \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver $subject
     * @param callable $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function aroundExecute($subject, callable $proceed, \Magento\Framework\Event\Observer $observer)
    {
        if ($this->extendedConfig->isForbidCacheFlush()) {
            return;
        }
        $proceed($observer);
    }
}
