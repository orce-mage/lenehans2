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
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Data\JobInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\Job\CollectionFactory;

class JobRepository implements JobRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var JobInterfaceFactory
     */
    private $factory;

    /**
     * JobRepository constructor.
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param JobInterfaceFactory $factory
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        JobInterfaceFactory $factory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $job = $this->create();
        $job = $this->entityManager->load($job, $id);

        if (!$job->getId()) {
            return false;
        }

        return $job;
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
    public function delete(JobInterface $job)
    {
        $this->entityManager->delete($job);
    }

    /**
     * {@inheritdoc}
     */
    public function save(JobInterface $job)
    {
        if (!$job->getStatus()) {
            $job->setStatus(JobInterface::STATUS_SCHEDULED);
        }

        return $this->entityManager->save($job);
    }
}
