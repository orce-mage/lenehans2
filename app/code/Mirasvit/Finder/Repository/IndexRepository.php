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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Filter\FilterManager;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Api\Data\IndexInterfaceFactory;
use Mirasvit\Finder\Model\ResourceModel\Index\Collection;
use Mirasvit\Finder\Model\ResourceModel\Index\CollectionFactory;

class IndexRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    private $filterManager;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        IndexInterfaceFactory $factory,
        FilterManager $filterManager
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
        $this->filterManager     = $filterManager;
    }

    /**
     * @return IndexInterface[]|Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create(): IndexInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?IndexInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(IndexInterface $model): IndexInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(IndexInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
