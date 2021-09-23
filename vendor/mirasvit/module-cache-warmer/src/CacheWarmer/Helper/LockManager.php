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



namespace Mirasvit\CacheWarmer\Helper;


use Magento\Config\Model\ResourceModel\Config as ConfigWriter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Lock\LockManagerInterface;
use Mirasvit\Core\Service\CompatibilityService;

class LockManager
{
    const PATH_PREFIX = "mst_lock/";

    private $scopeConfig;

    private $configWriter;

    private $configCollectionFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigWriter $configWriter,
        ConfigCollectionFactory $factory
    ) {
        $this->scopeConfig             = $scopeConfig;
        $this->configWriter            = $configWriter;
        $this->configCollectionFactory = $factory;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function lock($name)
    {
        $lockPath = self::PATH_PREFIX . $name;

        if (!$this->isLocked($name)) {
            $this->configWriter->saveConfig($lockPath, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function unlock($name)
    {
        if ($this->isLocked($name)) {
            return false;
        }

        $lockPath = self::PATH_PREFIX . $name;
        $this->configWriter->saveConfig($lockPath, 0, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

        return true;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isLocked($name)
    {
        $lockPath = self::PATH_PREFIX . $name;

        // using collection to avoid read lock status from cache
        $collection = $this->configCollectionFactory->create();
        $config = $collection->addScopeFilter('default', 0, $lockPath)->getFirstItem();

        return (bool)$config->getData('value');
    }
}
