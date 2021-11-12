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
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterInterfaceFactory;
use Mirasvit\Finder\Model\ResourceModel\Filter\Collection;
use Mirasvit\Finder\Model\ResourceModel\Filter\CollectionFactory;

class FilterRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    private $filterManager;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        FilterInterfaceFactory $factory,
        FilterManager $filterManager
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
        $this->filterManager     = $filterManager;
    }

    /**
     * @return FilterInterface[]|Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create(): FilterInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?FilterInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(FilterInterface $model): FilterInterface
    {
        if ($model->getLinkType() === FilterInterface::LINK_TYPE_CUSTOM && !$model->getAttributeCode()) {
            $model->setAttributeCode(str_replace('-', '_', $this->filterManager->translitUrl($model->getName())));
        }

        if (!$model->getUrlKey()) {
            $model->setUrlKey($model->getAttributeCode());
        }

        return $this->entityManager->save($model);
    }

    public function delete(FilterInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
