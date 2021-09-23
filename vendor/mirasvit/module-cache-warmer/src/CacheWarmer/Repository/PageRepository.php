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



namespace Mirasvit\CacheWarmer\Repository;

use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Data\UserAgentInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\Page\CollectionFactory;
use Mirasvit\CacheWarmer\Service\WarmerService;

class PageRepository implements PageRepositoryInterface
{
    private $entityManager;

    private $collectionFactory;

    protected $dateFactory;

    private $pageFactory;
    
    private $sourceRepository;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        PageInterfaceFactory $pageFactory,
        SourceRepository $sourceRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->pageFactory       = $pageFactory;
        $this->dateFactory       = $dateFactory;
        $this->sourceRepository  = $sourceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $page = $this->create();
        $page = $this->entityManager->load($page, $id);

        if (!$page->getId()) {
            return false;
        }

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->pageFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getByCacheId($cacheId, $varyDataHash)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter("cache_id", $cacheId)
                    ->addFieldToFilter("vary_data_hash", $varyDataHash);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        // for backward compability
        $collection = $this->getCollection();
        $collection->addFieldToFilter("cache_id", $cacheId)
            ->addFieldToFilter("vary_data", $varyDataHash);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getByURI($uri, $varyDataHash)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter("uri_hash", sha1($uri))
            ->addFieldToFilter("vary_data_hash", $varyDataHash)
            ->setPageSize(1)
            ->setCurPage(1);

        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PageInterface $page)
    {
        $this->entityManager->delete($page);
    }

    /**
     * {@inheritdoc}
     */
    public function save(PageInterface $page)
    {
        if (!$page->getId() || !$page->getCreatedAt()) {
            //using this, because default magento function is not working correctly in some configurations
            $page->setCreatedAt(gmdate("Y-m-d H:i:s"));
        }
        
        if ($page->getUriHash() == "") {
            $page->setUriHash(sha1($page->getUri()));
        }
        
        $page2 = $this->getByURI($page->getUri(), $page->getVaryDataHash());

        if ($page2 && $page->getId() != $page2->getId()) {
            return;
        }
        
        $page->setUpdatedAt(gmdate("Y-m-d H:i:s"));
        
        if (
            $page->getUserAgent() == UserAgentInterface::DESKTOP_USER_AGENT
            && $crawlerSource = $this->sourceRepository->getCrawlerSource()
        ) {
            $page->setSourceId($crawlerSource->getId());
        }
        
        return $this->entityManager->save($page);
    }


    /**
     * {@inheritdoc}
     */
    public function getPageTypes()
    {
        $select = clone $this->getCollection()->getSelect();
        $select->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET)
            ->reset(Select::COLUMNS)
            ->group(PageInterface::PAGE_TYPE)
            ->columns(PageInterface::PAGE_TYPE);

        return $this->getCollection()->getConnection()->fetchCol($select);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @param PageInterface $page
     */
    // Need this method to avoid exception "Asymmetric transaction rollback"
    // while deleting pages in the loop
    public function deletePage(PageInterface $page)
    {
        $resource = $this->getCollection()->getResource();
        $connection = $this->getCollection()->getConnection();

        $connection->query(
            "DELETE FROM " . $resource->getTable(PageInterface::TABLE_NAME)
            . " WHERE " . PageInterface::ID . " = " . $page->getId()
        );
    }
}
