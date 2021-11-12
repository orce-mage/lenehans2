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
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\FinderInterfaceFactory;
use Mirasvit\Finder\Model\ResourceModel\Finder\Collection;
use Mirasvit\Finder\Model\ResourceModel\Finder\CollectionFactory;

class FinderRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        FinderInterfaceFactory $factory
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
    }

    /**
     * @return FinderInterface[]|Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create(): FinderInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?FinderInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(FinderInterface $model): FinderInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(FinderInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
