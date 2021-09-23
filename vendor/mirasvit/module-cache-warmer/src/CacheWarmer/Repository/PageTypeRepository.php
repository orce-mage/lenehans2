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
use Mirasvit\CacheWarmer\Api\Data\PageTypeInterface;
use Mirasvit\CacheWarmer\Api\Data\PageTypeInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Repository\PageTypeRepositoryInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\PageType\CollectionFactory;

class PageTypeRepository implements PageTypeRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PageTypeInterfaceFactory
     */
    private $pageTypeFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * PageTypeRepository constructor.
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param PageTypeInterfaceFactory $pageTypeFactory
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        PageTypeInterfaceFactory $pageTypeFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->pageTypeFactory   = $pageTypeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->pageTypeFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function save(PageTypeInterface $pageType)
    {
        return $this->entityManager->save($pageType);
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
            ->group(PageTypeInterface::PAGE_TYPE)
            ->columns(PageTypeInterface::PAGE_TYPE);

        return $this->getCollection()->getConnection()->fetchCol($select);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }
}
