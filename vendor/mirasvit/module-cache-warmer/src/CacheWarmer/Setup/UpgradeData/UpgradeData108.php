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



namespace Mirasvit\CacheWarmer\Setup\UpgradeData;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Psr\Log\LoggerInterface;

class UpgradeData108 implements UpgradeDataInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpgradeData108 constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        LoggerInterface $logger,
        Serializer $serializer
    ) {
        $this->pageRepository = $pageRepository;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Json_Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $offset = 0;
        $limit  = 1000;

        $pageCollection = $this->pageRepository->getCollection();
        $size = $pageCollection->getSize();
        $select = $pageCollection->getSelect();
        $select->limit($limit, $offset);
        if ($size) {
            $this->logger->info("Convert warmer URLs to a new format 1:");
        }
        while ($pageCollection->count()) {
            $this->logger->info($offset."/".$size);
            /** @var PageInterface $page */
            foreach ($pageCollection as $page) {
                $varyData = $page->getData(PageInterface::VARY_DATA);
                $varyData = $this->serializer->unserialize($varyData);
                if (is_array($varyData)) {
                    $page->setVaryData($varyData);
                    $this->pageRepository->save($page);
                }
            }
            $pageCollection->clear();
            $offset += $limit;
            $select->limit($limit, $offset);
        }
        if ($size) {
            $this->logger->info("Done");
        }
    }
}
