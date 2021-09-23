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

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\CacheWarmer\Api\Data\LogInterface;
use Mirasvit\CacheWarmer\Api\Data\LogInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Repository\LogRepositoryInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\Log\CollectionFactory;

class LogRepository implements LogRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LogInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * LogRepository constructor.
     * @param EntityManager $entityManager
     * @param LogInterfaceFactory $factory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        EntityManager $entityManager,
        LogInterfaceFactory $factory,
        CollectionFactory $collectionFactory
    ) {
        $this->entityManager     = $entityManager;
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function save(LogInterface $log)
    {
        return $this->entityManager->save($log);
    }

    /**
     * @return LogInterface[]|\Mirasvit\CacheWarmer\Model\ResourceModel\Log\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(LogInterface $log)
    {
        $this->entityManager->delete($log);
    }
}
