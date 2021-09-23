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



namespace Mirasvit\CacheWarmer\Preference\Framework\Cache\Frontend\Decorator;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Mirasvit\CacheWarmer\Service\CacheCleanService;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;

class TagScopePreference extends TagScope
{
    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        if (!$this->getDeploymentConfig()->getConfigData('db')) {
            return parent::clean($mode, $tags);
        }

        if (!$this->getExtendedConfig()->isForbidCacheFlush()) {
            // if forbid cache flush is disabled AND we try to flush FPC cache
            // we log such action
            if (in_array('FPC', $tags) || $mode == \Zend_Cache::CLEANING_MODE_ALL) {
                $this->getCacheCleanService()->logCacheClean($mode, $tags);
            }
            return parent::clean($mode, $tags);
        }

        // if forbid cache flush is enabled, but we don't have FPC tags, we allow cache flushing
        if (!in_array('FPC', $tags) || $mode == \Zend_Cache::CLEANING_MODE_ALL) {
            return parent::clean($mode, $tags);
        }

        return true;
    }

    /**
     * Prevent error "Cache frontend 'default' is not recognized." (for some stores)
     * @return CacheCleanService
     */
    private function getCacheCleanService()
    {
        return ObjectManager::getInstance()->get(CacheCleanService::class);
    }

    /**
     * Prevent error "Cache frontend 'default' is not recognized." (for some stores)
     * @return ExtendedConfig
     */
    private function getExtendedConfig()
    {
        return ObjectManager::getInstance()->get(ExtendedConfig::class);
    }

    /**
     * @return DeploymentConfig
     */
    private function getDeploymentConfig()
    {
        return ObjectManager::getInstance()->get(DeploymentConfig::class);
    }
}
