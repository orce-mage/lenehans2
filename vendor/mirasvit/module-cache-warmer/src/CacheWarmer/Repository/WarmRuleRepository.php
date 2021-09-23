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
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\WarmRule\CollectionFactory;

class WarmRuleRepository implements WarmRuleRepositoryInterface
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
     * @var WarmRuleInterfaceFactory
     */
    private $factory;

    /**
     * WarmRuleRepository constructor.
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param WarmRuleInterfaceFactory $factory
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        WarmRuleInterfaceFactory $factory
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
        return $this->collectionFactory->create()->addFieldToFilter('is_active', true);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        if (!$model->getId()) {
            return false;
        }

        return $model;
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
    public function delete(WarmRuleInterface $model)
    {
        $this->entityManager->delete($model);
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarmRuleInterface $model)
    {
        return $this->entityManager->save($model);
    }
}
