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
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

class UpgradeData1014 implements UpgradeDataInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpgradeData1014 constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        LoggerInterface $logger
    ) {
        $this->pageRepository = $pageRepository;
        $this->logger = $logger;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $pageCollection = $this->pageRepository->getCollection();
        $size = $pageCollection->getSize();

        if (!$size) {
            return;
        }

        $connection = $setup->getConnection();
        //remove possible duplicates
        $this->logger->info("Removing possible duplicates...");

        $connection = $setup->getConnection();

        $indexName = "mst_cache_warmer_uri_vary_index";
        $connection->query("ALTER TABLE `{$setup->getTable(PageInterface::TABLE_NAME)}` ADD INDEX `$indexName` (`uri` (600), `vary_data` (100));");

        $connection->query("DELETE t1 FROM `{$setup->getTable(PageInterface::TABLE_NAME)}` t1
            INNER JOIN
            `{$setup->getTable(PageInterface::TABLE_NAME)}` t2
            WHERE t1.page_id > t2.page_id AND t1.uri = t2.uri AND t1.vary_data = t2.vary_data;");

        $connection->query("ALTER TABLE `{$setup->getTable(PageInterface::TABLE_NAME)}` DROP INDEX `$indexName`;");
    }
}
