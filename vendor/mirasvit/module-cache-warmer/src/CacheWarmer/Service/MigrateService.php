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

use Magento\Framework\Stdlib\DateTime;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\MigrateServiceInterface;
use Mirasvit\CacheWarmer\Logger\Logger;
use Mirasvit\CacheWarmer\Model\Config;

class MigrateService implements MigrateServiceInterface
{

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var DateTime\DateTimeFactory
     */
    private $dateFactory;

    /**
     * MigrateService constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param Config $config
     * @param Logger $logger
     * @param DateTime\DateTimeFactory $dateFactory
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        Config $config,
        Logger $logger,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
    ) {
        $this->pageRepository = $pageRepository;

        $this->config = $config;
        $this->logger = $logger;
        $this->dateFactory = $dateFactory;
    }

    public function migrateData()
    {
        $this->logger->forceEnable(true);
        if ($this->config->getDataVersion() < Config::DATA_VERSION) {
            $this->migrateDataV1();
            $this->config->setDataVersion(Config::DATA_VERSION);
        }

        $this->logger->forceEnable(null);
    }

    protected function migrateDataV1()
    {
        $pageCollection = $this->pageRepository->getCollection()
            ->addFieldToFilter(PageInterface::VARY_DATA_HASH, "")
            ->addFieldToFilter(PageInterface::VARY_DATA, ["neq" => "[]"]);
        $size = $pageCollection->getSize();

        if (!$size) {
            return;
        }

        $offset = 0;
        $limit  = 100;


        $select = $pageCollection->getSelect();
        $select->limit($limit, $offset);
        $this->logger->info("Convert warmer URLs to a new format migrateDataV1");
        while ($pageCollection->count()) {
            $this->logger->info($offset."/".$size);
//            $this->logger->info((string)$pageCollection->getSelect());
            /** @var PageInterface $page */
            foreach ($pageCollection as $page) {
                if ($page->getVaryDataHash()) {
                    continue;
                }
                $varyData = $page->getData(PageInterface::VARY_DATA);
                $page->setVaryDataHash($page->getVaryString());
                try {
                    $this->pageRepository->save($page);
                } catch (\LogicException $e) {
                    $this->pageRepository->delete($page); //already exists
                }
            }
            $pageCollection->clear();
            $offset += $limit;
            $select->limit($limit, 0); //fetch only first N records from top
        }
        $this->logger->info("Convert warmer URLs is Done");
    }
}
